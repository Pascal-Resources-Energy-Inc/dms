<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class BmcController extends Controller {
    private const ITEMS = [
        'Booth' => 3400,
        'Negosyo Partner Roll Up Banner' => 2000,
        'End User Roll Up Banner' => 2000,
    ];

    private function borrower() {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['Area Distributor', 'Provincial Distributor'], true), 403);
        return $user;
    }

    protected function apiClient() {
        return new Client(['timeout' => 30]);
    }

    private function apiResponse($method, $path = '', array $options = []) {
        $url = parse_url((string) config('services.ldr.endpoint'));
        if (empty($url['scheme']) || empty($url['host']) || !config('services.ldr.api_key')) {
            throw new \RuntimeException('BMC request service is not configured.');
        }
        $endpoint = $url['scheme'] . '://' . $url['host']
            . (isset($url['port']) ? ':' . $url['port'] : '') . '/api/external/bmc' . $path;
        $options['headers'] = ['Accept' => 'application/json', 'X-DMS-Key' => config('services.ldr.api_key')];
        return $this->apiClient()->request($method, $endpoint, $options);
    }

    private function apiRequest($method, $path = '', array $options = []) {
        return json_decode((string) $this->apiResponse($method, $path, $options)->getBody(), true);
    }

    public function index(Request $request) {
        $user = $this->borrower();
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['For Approval', 'Approved', 'Partially Released', 'Released', 'Closed', 'Declined', 'Cancelled'])],
            'page' => 'nullable|integer|min:1',
        ]);
        $requests = [];
        $meta = ['current_page' => 1, 'last_page' => 1, 'total' => 0];
        $serviceError = null;
        $summary = null;
        try {
            $payload = $this->apiRequest('GET', '', ['query' => array_filter(array_merge($filters, ['email' => $user->email]), function ($value) { return $value !== null && $value !== ''; })]);
            $requests = $payload['data'] ?? [];
            $meta = array_merge($meta, $payload['meta'] ?? []);
        } catch (\Exception $exception) {
            report($exception);
            $serviceError = 'Requests could not be loaded. Please try again later.';
        }
        if (!$serviceError) {
            try {
                $counts = array_fill_keys(['For Approval', 'Approved', 'Partially Released', 'Released', 'Closed', 'Declined', 'Cancelled'], 0);
                $page = 1;
                do {
                    $result = $this->apiRequest('GET', '', ['query' => ['email' => $user->email, 'per_page' => 50, 'page' => $page]]);
                    foreach ($result['data'] ?? [] as $item) {
                        $status = $item['status'] ?? '';
                        if (array_key_exists($status, $counts)) $counts[$status]++;
                    }
                    $lastPage = max(1, (int) ($result['meta']['last_page'] ?? 1));
                    $page++;
                } while ($page <= $lastPage);
                $summary = $counts;
            } catch (\Exception $exception) {
                report($exception);
            }
        }
        return view('bmc.index', compact('requests', 'meta', 'filters', 'serviceError', 'summary'));
    }

    private function findBorrowing($id) {
        $user = $this->borrower();
        $payload = $this->apiRequest('GET', '/' . urlencode($id), ['query' => ['email' => $user->email]]);
        if (empty($payload['data']) || !is_array($payload['data'])) {
            throw new \RuntimeException('Invalid BMC response.');
        }
        return $payload['data'];
    }

    private function actionError(\Exception $exception, Request $request) {
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();
            $status = $response->getStatusCode();
            if ($status === 404 || $status === 403) abort(404);
            if (in_array($status, [409, 422], true)) {
                $payload = json_decode((string) $response->getBody(), true) ?: [];
                return back()->withErrors($payload['errors'] ?? ['submission' => $payload['message'] ?? 'Please check your request.'])
                    ->withInput($request->except('signature'));
            }
        }
        report($exception);
        return back()->withErrors(['submission' => 'The action could not be confirmed. Check the current status before trying again.'])
            ->withInput($request->except('signature'));
    }

    public function cancel(Request $request, $id) {
        $user = $this->borrower();
        try {
            $this->apiRequest('PATCH', '/' . urlencode($id) . '/cancel', ['json' => ['email' => $user->email]]);
            Alert::success('Borrowing request cancelled')->persistent('Dismiss');
            return redirect()->route('bmc.show', $id);
        } catch (\Exception $exception) {
            return $this->actionError($exception, $request);
        }
    }

    public function payment(Request $request, $id) {
        $user = $this->borrower();
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['COD', 'GCash', 'Bank Transfer'])],
            'release_token' => 'required|string|size:64',
            'proof_of_transfer' => 'required_unless:payment_method,COD|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $file = $request->file('proof_of_transfer');
        try {
            $multipart = [
                ['name' => 'email', 'contents' => $user->email],
                ['name' => 'payment_method', 'contents' => $data['payment_method']],
                ['name' => 'release_token', 'contents' => $data['release_token']],
            ];
            if ($file) {
                $multipart[] = ['name' => 'proof_of_transfer', 'contents' => file_get_contents($file->getRealPath()), 'filename' => $file->getClientOriginalName()];
            }
            $this->apiRequest('POST', '/' . urlencode($id) . '/payment', ['multipart' => $multipart]);
            Alert::success('Payment deposit details submitted.')->persistent('Dismiss');
            return redirect()->route('bmc.show', $id);
        } catch (\Exception $exception) {
            if ($exception instanceof RequestException && $exception->hasResponse() && $exception->getResponse()->getStatusCode() === 409) {
                return redirect()->route('bmc.show', $id)->withErrors(['payment' => 'The prepared release may have changed or is no longer available. Review the current items and deposit before submitting again.'])
                    ->withInput($request->only('payment_method'));
            }
            return $this->actionError($exception, $request);
        }
    }

    private function proxyPaymentProof($path, $description = 'payment proof') {
        $user = $this->borrower();
        try {
            $response = $this->apiResponse('GET', $path, ['query' => ['email' => $user->email]]);
            $type = strtolower(trim(explode(';', $response->getHeaderLine('Content-Type'))[0]));
            if (!in_array($type, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
                throw new \RuntimeException('Unexpected proof content type.');
            }
            return response((string) $response->getBody(), 200, [
                'Content-Type' => $type,
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && in_array($exception->getResponse()->getStatusCode(), [403, 404], true)) abort(404);
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        abort(502, 'The ' . $description . ' could not be loaded. Please try again.');
    }

    public function paymentProof($id) {
        return $this->proxyPaymentProof('/' . urlencode($id) . '/payment-proof');
    }

    public function refundProof($id) {
        return $this->proxyPaymentProof('/' . urlencode($id) . '/refund-proof', 'refund proof');
    }

    public function accountabilityPaymentProof($id) {
        return $this->proxyPaymentProof('/accountabilities/' . urlencode($id) . '/payment-proof');
    }

    public function accountability($id, $preview = false) {
        $user = $this->borrower();
        try {
            $payload = $this->apiRequest('GET', '/accountabilities/' . urlencode($id), ['query' => ['email' => $user->email]]);
            if (empty($payload['data']) || !is_array($payload['data'])) throw new \RuntimeException('Invalid accountability response.');
            $accountability = $payload['data'];
            return view('bmc.accountability', compact('accountability', 'preview'));
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && in_array($exception->getResponse()->getStatusCode(), [403, 404], true)) abort(404);
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        return redirect()->route('bmc.index')->withErrors(['submission' => 'The accountability could not be loaded. Please try again.']);
    }

    public function accountabilityPreview($id) {
        return $this->accountability($id, true);
    }

    public function signatureImage($id) {
        $user = $this->borrower();
        try {
            $payload = $this->apiRequest('GET', '/accountabilities/' . urlencode($id), ['query' => ['email' => $user->email]]);
            $path = $payload['data']['borrower_signature'] ?? '';
            if (!is_string($path) || !preg_match('~^signatures/bmc_accountability_[0-9A-Za-z_.-]+\.png$~', $path)) abort(404);
            $origin = parse_url((string) config('services.ldr.endpoint'));
            if (empty($origin['scheme']) || empty($origin['host']) || !in_array($origin['scheme'], ['http', 'https'], true)) {
                throw new \RuntimeException('BMC request service is not configured.');
            }
            $url = $origin['scheme'] . '://' . $origin['host'] . (isset($origin['port']) ? ':' . $origin['port'] : '') . '/' . $path;
            $image = $this->apiClient()->request('GET', $url, ['allow_redirects' => false]);
            if (strtolower(trim(explode(';', $image->getHeaderLine('Content-Type'))[0])) !== 'image/png') {
                throw new \RuntimeException('Unexpected signature content type.');
            }
            return response((string) $image->getBody(), 200, [
                'Content-Type' => 'image/png', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && in_array($exception->getResponse()->getStatusCode(), [403, 404], true)) abort(404);
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        abort(502, 'The signature could not be loaded.');
    }

    public function signature(Request $request, $id) {
        $user = $this->borrower();
        $validator = \Validator::make($request->all(), [
            'signature' => 'required|string|max:7000000',
            'borrower_remarks' => 'nullable|array',
            'borrower_remarks.*' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('signature'));
        }
        $data = $validator->validated();
        $data['email'] = $user->email;
        try {
            $this->apiRequest('POST', '/accountabilities/' . urlencode($id) . '/signature', ['json' => $data]);
            Alert::success('Accountability signed successfully')->persistent('Dismiss');
            return $request->input('signature_from_request') ? back() : redirect()->route('bmc.accountability', $id);
        } catch (\Exception $exception) {
            return $this->actionError($exception, $request);
        }
    }

    public function extension(Request $request, $id) {
        $user = $this->borrower();
        $data = $request->validate(['requested_return_date' => 'required|date_format:Y-m-d', 'reason' => 'required|string|max:2000']);
        $data['email'] = $user->email;
        try {
            $this->apiRequest('POST', '/accountabilities/' . urlencode($id) . '/extensions', ['json' => $data]);
            Alert::success('Extension request submitted')->persistent('Dismiss');
            return redirect()->route('bmc.accountability', $id);
        } catch (\Exception $exception) {
            return $this->actionError($exception, $request);
        }
    }

    public function returnItems(Request $request, $id) {
        $user = $this->borrower();
        $data = $request->validate([
            'return_remarks' => 'required|string|max:2000',
            'return_mode' => ['required', Rule::in(['Drop off', 'Courier', 'Officer Name', 'Field location'])],
            'return_mode_details' => ['required', 'string', 'max:1900', 'not_regex:/[\r\n]/'],
            'attachment' => 'required|array|min:1|max:3',
            'attachment.*' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'return_serial_ids' => 'nullable|array',
            'return_serial_ids.*' => 'array',
            'return_serial_ids.*.*' => 'integer',
            'return_serial_numbers' => 'nullable|array',
            'return_serial_numbers.*' => 'array',
            'return_serial_numbers.*.*' => 'string|max:255',
            'bmc_request_id' => 'nullable|integer|min:1',
        ]);
        if (empty($data['return_serial_ids']) && empty($data['return_serial_numbers'])) {
            throw ValidationException::withMessages(['return_serial_ids' => 'Select at least one serial number to return.']);
        }
        $multipart = [];
        foreach (['return_remarks', 'return_mode', 'return_mode_details'] as $field) {
            $multipart[] = ['name' => $field, 'contents' => $data[$field]];
        }
        $multipart[] = ['name' => 'email', 'contents' => $user->email];
        foreach (['return_serial_ids', 'return_serial_numbers'] as $field) {
            foreach ($data[$field] ?? [] as $itemId => $serials) {
                foreach ($serials as $serial) $multipart[] = ['name' => $field . '[' . $itemId . '][]', 'contents' => (string) $serial];
            }
        }
        foreach ($request->file('attachment', []) as $photo) {
            $multipart[] = ['name' => 'attachment[]', 'contents' => fopen($photo->getRealPath(), 'r'), 'filename' => $photo->getClientOriginalName()];
        }
        try {
            $this->apiRequest('POST', '/accountabilities/' . urlencode($id) . '/returns', ['multipart' => $multipart]);
            Alert::success('Returned items are awaiting for confirmation')->persistent('Dismiss');
            return !empty($data['bmc_request_id'])
                ? redirect()->route('bmc.show', $data['bmc_request_id'])
                : redirect()->route('bmc.index');
        } catch (\Exception $exception) {
            return $this->actionError($exception, $request);
        }
    }

    public function show($id) {
        $this->borrower();
        try {
            $borrowing = $this->findBorrowing($id);
            $depositRates = self::ITEMS;
            return view('bmc.show', compact('borrowing', 'depositRates'));
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 404) abort(404);
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        return redirect()->route('bmc.index')->withErrors(['submission' => 'The request could not be loaded. Please try again later.']);
    }

    public function edit($id) {
        $this->borrower();
        try {
            $borrowing = $this->findBorrowing($id);
            if (($borrowing['status'] ?? '') !== 'For Approval') {
                return redirect()->route('bmc.show', $id)->withErrors(['submission' => 'Only requests awaiting approval can be edited.']);
            }
            $user = $this->borrower();
            return view('bmc.create', ['user' => $user, 'firstName' => $borrowing['borrower_first_name'], 'lastName' => $borrowing['borrower_last_name'], 'items' => self::ITEMS, 'borrowing' => $borrowing]);
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 404) abort(404);
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        return redirect()->route('bmc.index')->withErrors(['submission' => 'The request could not be loaded. Please try again later.']);
    }

    public function update(Request $request, $id) {
        $user = $this->borrower();
        $data = $request->validate([
            'borrower_first_name' => 'required|string|max:255',
            'borrower_last_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|regex:/^09[0-9]{9}$/',
            'date_needed' => 'required|date_format:Y-m-d',
            'event_name' => 'required|string|max:255',
            'request_details' => 'required|string',
            'number_of_days_item_needed' => 'required|integer|min:1|max:40',
            'borrowed_items' => 'required|array|min:1',
            'borrowed_items.*.item_name' => ['required', 'string', 'distinct', Rule::in(array_keys(self::ITEMS))],
            'borrowed_items.*.requested_quantity' => 'required|integer|min:0|max:99',
        ]);
        if (!collect($data['borrowed_items'])->contains(function ($item) { return (int) $item['requested_quantity'] > 0; })) {
            throw ValidationException::withMessages(['borrowed_items' => 'Select at least one item.']);
        }
        $data['email'] = $user->email;
        try {
            $this->apiRequest('PATCH', '/' . urlencode($id), ['json' => $data]);
            Alert::success('Borrowing request updated successfully')->persistent('Dismiss');
            return redirect()->route('bmc.show', $id);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $status = $exception->getResponse()->getStatusCode();
                if ($status === 404) abort(404);
                if ($status === 409) return redirect()->route('bmc.show', $id)->withErrors(['submission' => 'Only requests awaiting approval can be edited.']);
                if ($status === 422) {
                    $payload = json_decode((string) $exception->getResponse()->getBody(), true);
                    return back()->withErrors($payload['errors'] ?? ['submission' => 'Please check the form.'])->withInput();
                }
            }
            report($exception);
        } catch (\RuntimeException $exception) {
            report($exception);
        }
        return back()->withErrors(['submission' => 'The update could not be confirmed. Please check the request before trying again.'])->withInput();
    }

    public function create() {
        $user = $this->borrower();
        $parts = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY);
        $last = count($parts) > 1 ? array_pop($parts) : '';
        return view('bmc.create', [
            'user' => $user,
            'firstName' => $user->first_name ?: implode(' ', $parts),
            'lastName' => $user->last_name ?: $last,
            'items' => self::ITEMS,
        ]);
    }

    public function store(Request $request) {
        $user = $this->borrower();
        $data = $request->validate([
            'borrower_first_name' => 'required|string|max:255',
            'borrower_last_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|regex:/^09[0-9]{9}$/',
            'date_needed' => 'required|date_format:Y-m-d|after_or_equal:today',
            'event_name' => 'required|string|max:255',
            'request_details' => 'required|string',
            'number_of_days_item_needed' => 'required|integer|min:1|max:40',
            'borrowed_items' => 'required|array|min:1',
            'borrowed_items.*.item_name' => ['required', 'string', 'distinct', Rule::in(array_keys(self::ITEMS))],
            'borrowed_items.*.requested_quantity' => 'required|integer|min:0|max:99',
        ]);

        if (!collect($data['borrowed_items'])->contains(function ($item) { return (int) $item['requested_quantity'] > 0; })) {
            throw ValidationException::withMessages(['borrowed_items' => 'Select at least one item.']);
        }

        $data['borrower_profile'] = $user->role;
        $data['email'] = $user->email;
        $url = parse_url((string) config('services.ldr.endpoint'));
        if (empty($url['scheme']) || empty($url['host']) || !config('services.ldr.api_key')) {
            return back()->withErrors(['submission' => 'The request service is not configured. Please contact support.'])->withInput();
        }
        $endpoint = $url['scheme'] . '://' . $url['host']
            . (isset($url['port']) ? ':' . $url['port'] : '') . '/api/external/bmc';

        try {
            $response = (new Client(['timeout' => 30]))->post($endpoint, [
                'headers' => ['Accept' => 'application/json', 'X-DMS-Key' => config('services.ldr.api_key')],
                'json' => $data,
            ]);
            $payload = json_decode((string) $response->getBody(), true);
            $message = 'Your request was submitted.';
            if (!empty($payload['reference_no'])) {
                $message .= ' Reference number: ' . $payload['reference_no'];
            }
            Alert::success('Borrowing request submitted successfully')->persistent('Dismiss');
            return !empty($payload['id']) ? redirect()->route('bmc.show', $payload['id']) : redirect()->route('bmc.index');
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 422) {
                $payload = json_decode((string) $exception->getResponse()->getBody(), true);
                return back()->withErrors($payload['errors'] ?? ['submission' => 'Please check the form and try again.'])->withInput();
            }
            report($exception);
            return back()->withErrors(['submission' => 'The submission could not be confirmed. Please check with the marketing team before submitting again.'])->withInput();
        }
    }
}
