<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LayoutDesignController extends Controller {
    private const DEPARTMENTS = [
        'Provincial Distributor (PD)',
        'Area Distributor (AD)',
        'Mega Dealer',
        'Dealer',
    ];

    private const INTENDED_USES = [
        'Internal Use',
        'External Use',
    ];

    private const MEDIUM_TYPES = [
        'Digital Media',
        'Print Media',
    ];

    private const COLLATERAL_TYPES = [
        'Business Card Layout',
        'Announcement Layout',
        'Poster Layout',
        'Tarpaulin Layout',
        'Promotional Item Layout',
        'Video',
        'Store Signage',
        'Other (Put details in project description)',
    ];

    private const STORE_SIGNAGE_OPTIONS = [
        'Layout file of store signage only',
        'Refurbished Store Signage (P5,000)',
    ];

    private const REFURBISHED_STORE_SIGNAGE_OPTION = 'Refurbished Store Signage (P5,000)';
    private const REFURBISHED_STORE_SIGNAGE_PREFILL = 'refurbished-store-signage';

    private const PEG_SAMPLE_LINK_MIME_TYPE = 'text/uri-list;type=peg-sample';

    public function index(Request $request) {
        $filters = $request->only(['search', 'status']);
        $requests = [];
        $pagination = [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 0,
        ];
        $summary = [
            'pending' => 0,
            'approved' => 0,
            'processed' => 0,
            'declined' => 0,
            'cancelled' => 0,
        ];
        $serviceError = null;

        try {
            $client = new Client(['timeout' => 30]);
            $response = $client->get(
                config('services.ldr.endpoint'),
                [
                    'headers' => $this->apiHeaders(),
                    'query' => array_filter([
                        'requestor_email' => auth()->user()->email,
                        'search' => $filters['search'] ?? null,
                        'status' => $filters['status'] ?? null,
                        'page' => max(1, (int) $request->input('page', 1)),
                        'per_page' => 15,
                    ], function ($value) {
                        return $value !== null && $value !== '';
                    }),
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);
            $requests = $payload['data'] ?? [];
            $pagination = array_merge($pagination, $payload['meta'] ?? []);
            $statusCounts = $payload['meta']['status_counts'] ?? [];

            if (!empty($statusCounts)) {
                $summary = array_merge($summary, array_intersect_key($statusCounts, $summary));
            } else {
                $summary = $this->fetchRequestSummary($client, auth()->user()->email, $summary);
            }
        } catch (RequestException $exception) {
            report($exception);
            $serviceError = 'Service is temporarily unavailable. Please try again later.';
        }

        return view('ldr.index', compact('requests', 'pagination', 'summary', 'filters', 'serviceError'));
    }

    private function fetchRequestSummary(Client $client, $requestorEmail, array $summary) {
        $page = 1;

        do {
            $response = $client->get(config('services.ldr.endpoint'), [
                'headers' => $this->apiHeaders(),
                'query' => [
                    'requestor_email' => $requestorEmail,
                    'page' => $page,
                    'per_page' => 50,
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            foreach ($payload['data'] ?? [] as $item) {
                $status = strtolower(trim((string) ($item['status'] ?? 'pending')));

                if ($status === 'pending') {
                    $summary['pending']++;
                } elseif ($status === 'approved') {
                    $summary['approved']++;
                } elseif ($status === 'processed') {
                    $summary['processed']++;
                } elseif ($status === 'declined') {
                    $summary['declined']++;
                } elseif ($status === 'cancelled') {
                    $summary['cancelled']++;
                }
            }

            $lastPage = max(1, (int) ($payload['meta']['last_page'] ?? 1));
            $page++;
        } while ($page <= $lastPage);

        return $summary;
    }

    public function create() {
        $user = auth()->user();
        $nameParts = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY);
        $fallbackLastName = count($nameParts) > 1 ? array_pop($nameParts) : '';
        $fallbackFirstName = implode(' ', $nameParts);
        $partnerTypes = [
            'Provincial Distributor' => 'Provincial Distributor (PD)',
            'Area Distributor' => 'Area Distributor (AD)',
            'Mega Dealer' => 'Mega Dealer',
            'Dealer' => 'Dealer',
        ];

        return view('ldr.create', [
            'departments' => self::DEPARTMENTS,
            'intendedUses' => self::INTENDED_USES,
            'mediumTypes' => self::MEDIUM_TYPES,
            'collateralTypes' => self::COLLATERAL_TYPES,
            'storeSignageOptions' => self::STORE_SIGNAGE_OPTIONS,
            'requestorEmail' => $user->email,
            'requestorFirstName' => trim((string) ($user->first_name ?: $fallbackFirstName)),
            'requestorLastName' => trim((string) ($user->last_name ?: $fallbackLastName)),
            'requestorPartnerType' => $partnerTypes[$user->role] ?? '',
        ]);
    }

    public function show($id) {
        try {
            $response = (new Client(['timeout' => 30]))->get(
                rtrim(config('services.ldr.endpoint'), '/') . '/' . urlencode($id),
                [
                    'headers' => $this->apiHeaders(),
                    'query' => ['requestor_email' => auth()->user()->email],
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);
            $layoutRequest = $payload['data'] ?? null;

            if (!$layoutRequest) {
                return redirect()->route('layout.index')
                    ->withErrors(['submission' => 'The layout design request could not be loaded.']);
            }

            $layoutRequest = $this->normalizeLayoutRequest($layoutRequest);

            return view('ldr.show', compact('layoutRequest'));
        } catch (RequestException $exception) {
            report($exception);

            $message = $exception->hasResponse() && $exception->getResponse()->getStatusCode() === 404
                ? 'The layout design request was not found or does not belong to your account.'
                : 'The layout design request could not be loaded. Please try again later.';

            return redirect()->route('layout.index')->withErrors(['submission' => $message]);
        }
    }

    public function edit($id) {
        try {
            $response = (new Client(['timeout' => 30]))->get(
                rtrim(config('services.ldr.endpoint'), '/') . '/' . urlencode($id),
                [
                    'headers' => $this->apiHeaders(),
                    'query' => ['requestor_email' => auth()->user()->email],
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);
            $layoutRequest = $payload['data'] ?? null;

            if (!$layoutRequest) {
                return redirect()->route('layout.index')
                    ->withErrors(['submission' => 'The layout design request could not be loaded.']);
            }

            if (($layoutRequest['status'] ?? null) !== 'Pending') {
                return redirect()->route('layout.index')
                    ->withErrors(['submission' => 'Only pending requests can be edited.']);
            }

            $layoutRequest = $this->normalizeLayoutRequest($layoutRequest);
            $storeSignageOption = $this->extractStoreSignageOption(
                $layoutRequest['marketing_collateral_type']
            );

            return view('ldr.edit', [
                'departments' => self::DEPARTMENTS,
                'intendedUses' => self::INTENDED_USES,
                'mediumTypes' => self::MEDIUM_TYPES,
                'collateralTypes' => self::COLLATERAL_TYPES,
                'storeSignageOptions' => self::STORE_SIGNAGE_OPTIONS,
                'storeSignageOption' => $storeSignageOption,
                'requestorEmail' => auth()->user()->email,
                'layoutRequest' => $layoutRequest,
            ]);
        } catch (RequestException $exception) {
            report($exception);

            $message = $exception->hasResponse() && $exception->getResponse()->getStatusCode() === 404
                ? 'The layout design request was not found or does not belong to your account.'
                : 'The layout design request could not be loaded. Please try again later.';

            return redirect()->route('layout.index')->withErrors(['submission' => $message]);
        }
    }

    public function store(Request $request) {
        $selectedCollateralTypes = array_values(array_unique(
            (array) $request->input('marketing_collateral_type', [])
        ));
        $storeSignageSelected = in_array(
            'Store Signage',
            $selectedCollateralTypes,
            true
        );
        $storeSignageRules = $storeSignageSelected
            ? ['required', Rule::in(self::STORE_SIGNAGE_OPTIONS)]
            : ['nullable', Rule::in(self::STORE_SIGNAGE_OPTIONS)];
        $storeSignageDetailRules = $storeSignageSelected
            ? 'required|string'
            : 'nullable|string';

        $validated = $request->validate([
            'requestor_first_name' => 'required|string|max:255',
            'requestor_last_name' => 'required|string|max:255',
            'department' => 'required|in:' . implode(',', self::DEPARTMENTS),
            'requestor_email' => 'required|email|max:255',
            'intended_use' => 'required|array|min:1',
            'intended_use.*' => 'in:' . implode(',', self::INTENDED_USES),
            'medium_type' => 'required|in:' . implode(',', self::MEDIUM_TYPES),
            'marketing_collateral_type' => 'required|array|min:1',
            'marketing_collateral_type.*' => 'in:' . implode(',', self::COLLATERAL_TYPES),
            'store_signage_option' => $storeSignageRules,
            'business_name' => $storeSignageDetailRules . '|max:255',
            'ad_territory' => $storeSignageDetailRules . '|max:255',
            'business_address' => $storeSignageDetailRules . '|max:255',
            'contact_number' => $storeSignageDetailRules . '|max:11|regex:/^[0-9]+$/',
            'purpose_of_layout_request' => 'required|string',
            'project_description' => 'required|string',
            'peg_samples' => 'nullable|array|max:6',
            'peg_samples.*' => 'required|mimes:jpg,jpeg,png,webp,mp4,pdf,doc,docx,ppt,pptx|max:30720',
            'peg_sample_link' => ['nullable', 'url', 'max:1000', 'regex:/^https?:\/\//i'],
            'date_needed' => 'nullable|date_format:Y-m-d',
        ], [
            'store_signage_option.required' => 'Select a Store Signage option.',
            'business_name.required' => 'Enter the business name for the Store Signage.',
            'ad_territory.required' => 'Enter the AD territory for the Store Signage.',
            'business_address.required' => 'Enter the business address for the Store Signage.',
            'contact_number.required' => 'Enter the contact number for the Store Signage.',
            'contact_number.max' => 'The contact number must not exceed 11 digits.',
            'contact_number.regex' => 'The contact number may contain numbers only.',
            'peg_samples.max' => 'You may upload up to 6 peg samples.',
            'peg_samples.*.mimes' => 'Each peg sample must be a JPG, JPEG, PNG, WebP, MP4, PDF, Word, or PowerPoint file.',
            'peg_samples.*.max' => 'Each peg sample must not exceed 30 MB.',
            'peg_sample_link.url' => 'Enter a valid peg sample link.',
            'peg_sample_link.max' => 'The peg sample link must not exceed 1000 characters.',
        ]);

        $validated['requestor_email'] = auth()->user()->email;

        $multipart = $this->multipartFields($validated, ['peg_samples']);

        foreach ($request->file('peg_samples', []) as $file) {
            $this->appendMultipartFile($multipart, 'peg_samples[]', $file);
        }

        try {
            $response = (new Client(['timeout' => 30]))->post(
                config('services.ldr.endpoint'),
                [
                    'headers' => $this->apiHeaders(),
                    'multipart' => $multipart,
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);

            if (($validated['store_signage_option'] ?? null) === self::REFURBISHED_STORE_SIGNAGE_OPTION) {
                return redirect()
                    ->route('layout.index')
                    ->with('ldr_success', $payload['message'] ?? 'Layout design request submitted.')
                    ->with('ldr_open_adpo_url', route('ad-purchase-orders.create', [
                        'prefill_product' => self::REFURBISHED_STORE_SIGNAGE_PREFILL,
                    ]));
            }

            return redirect()
                ->route('layout.index')
                ->with('ldr_success', $payload['message'] ?? 'Layout design request submitted.');
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $exception->getResponse()->getStatusCode() === 422) {
                $payload = json_decode((string) $exception->getResponse()->getBody(), true);
                $errors = $payload['errors'] ?? ['submission' => $payload['message'] ?? 'Please check the form and try again.'];

                return redirect()->route('layout.create')->withErrors($errors)->withInput();
            }

            report($exception);

            return redirect()
                ->route('layout.create')
                ->withErrors(['submission' => 'The request service is temporarily unavailable. Please try again later.'])
                ->withInput();
        }
    }

    public function update(Request $request, $id) {
        $selectedCollateralTypes = array_values(array_unique(
            (array) $request->input('marketing_collateral_type', [])
        ));
        $storeSignageSelected = in_array(
            'Store Signage',
            $selectedCollateralTypes,
            true
        );
        $storeSignageRules = $storeSignageSelected
            ? ['required', Rule::in(self::STORE_SIGNAGE_OPTIONS)]
            : ['nullable', Rule::in(self::STORE_SIGNAGE_OPTIONS)];
        $storeSignageDetailRules = $storeSignageSelected
            ? 'required|string'
            : 'nullable|string';

        $validated = $request->validate([
            'requestor_first_name' => 'required|string|max:255',
            'requestor_last_name' => 'required|string|max:255',
            'department' => 'required|in:' . implode(',', self::DEPARTMENTS),
            'requestor_email' => 'required|email|max:255',
            'intended_use' => 'required|array|min:1',
            'intended_use.*' => 'in:' . implode(',', self::INTENDED_USES),
            'medium_type' => 'required|in:' . implode(',', self::MEDIUM_TYPES),
            'marketing_collateral_type' => 'required|array|min:1',
            'marketing_collateral_type.*' => 'in:' . implode(',', self::COLLATERAL_TYPES),
            'store_signage_option' => $storeSignageRules,
            'business_name' => $storeSignageDetailRules . '|max:255',
            'ad_territory' => $storeSignageDetailRules . '|max:255',
            'business_address' => $storeSignageDetailRules . '|max:255',
            'contact_number' => $storeSignageDetailRules . '|max:11|regex:/^[0-9]+$/',
            'purpose_of_layout_request' => 'required|string',
            'project_description' => 'required|string',
            'peg_sample_replacements' => 'nullable|array',
            'peg_sample_replacements.*' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,pdf,doc,docx,ppt,pptx|max:30720',
            'remove_peg_samples' => 'nullable|array',
            'remove_peg_samples.*' => 'integer',
            'remove_legacy_peg_sample' => 'nullable|boolean',
            'peg_sample_link' => ['nullable', 'url', 'max:1000', 'regex:/^https?:\/\//i'],
            'peg_sample' => 'nullable|mimes:jpg,jpeg,png,webp,mp4,pdf,doc,docx,ppt,pptx|max:30720',
            'date_needed' => 'nullable|date_format:Y-m-d',
        ], [
            'store_signage_option.required' => 'Select a Store Signage option.',
            'business_name.required' => 'Enter the business name for the Store Signage.',
            'ad_territory.required' => 'Enter the AD territory for the Store Signage.',
            'business_address.required' => 'Enter the business address for the Store Signage.',
            'contact_number.required' => 'Enter the contact number for the Store Signage.',
            'contact_number.max' => 'The contact number must not exceed 11 digits.',
            'contact_number.regex' => 'The contact number may contain numbers only.',
            'peg_sample_replacements.*.mimes' => 'Each replacement must be a JPG, JPEG, PNG, WebP, MP4, PDF, Word, or PowerPoint file.',
            'peg_sample_replacements.*.max' => 'Each replacement must not exceed 30 MB.',
            'peg_sample.mimes' => 'The peg sample must be a JPG, JPEG, PNG, WebP, MP4, PDF, Word, or PowerPoint file.',
            'peg_sample.max' => 'The peg sample must not exceed 30 MB.',
            'peg_sample_link.url' => 'Enter a valid peg sample link.',
            'peg_sample_link.max' => 'The peg sample link must not exceed 1000 characters.',
        ]);

        $validated['requestor_email'] = auth()->user()->email;

        $multipart = $this->multipartFields(
            $validated,
            ['peg_sample_replacements', 'peg_sample']
        );
        $multipart[] = ['name' => '_method', 'contents' => 'PATCH'];

        foreach ($request->file('peg_sample_replacements', []) as $attachmentId => $file) {
            $this->appendMultipartFile(
                $multipart,
                'peg_sample_replacements[' . $attachmentId . ']',
                $file
            );
        }

        if ($request->hasFile('peg_sample')) {
            $this->appendMultipartFile($multipart, 'peg_sample', $request->file('peg_sample'));
        }

        try {
            $response = (new Client(['timeout' => 30]))->post(
                rtrim(config('services.ldr.endpoint'), '/') . '/' . urlencode($id),
                [
                    'headers' => $this->apiHeaders(),
                    'multipart' => $multipart,
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);

            return redirect()->route('layout.index')
                ->with('ldr_success', $payload['message'] ?? 'Layout design request updated.')
                ->with('ldr_reference_no', $payload['data']['reference_no'] ?? null);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $status = $exception->getResponse()->getStatusCode();
                $payload = json_decode((string) $exception->getResponse()->getBody(), true);

                if ($status === 422) {
                    $errors = $payload['errors'] ?? ['submission' => $payload['message'] ?? 'Please check the form and try again.'];

                    return redirect()->route('layout.edit', $id)->withErrors($errors)->withInput();
                }

                if ($status === 404 || $status === 409) {
                    return redirect()->route('layout.index')->withErrors([
                        'submission' => $payload['message'] ?? 'This request can no longer be edited.',
                    ]);
                }
            }

            report($exception);

            return redirect()->route('layout.edit', $id)
                ->withErrors(['submission' => 'The request service is temporarily unavailable. Please try again later.'])
                ->withInput();
        }
    }

    public function cancel($id) {
        try {
            $response = (new Client(['timeout' => 30]))->patch(
                rtrim(config('services.ldr.endpoint'), '/') . '/' . urlencode($id) . '/cancel',
                [
                    'headers' => $this->apiHeaders(),
                    'json' => ['requestor_email' => auth()->user()->email],
                ]
            );

            $payload = json_decode((string) $response->getBody(), true);

            return redirect()->route('layout.index')
                ->with('ldr_success', $payload['message'] ?? 'Layout design request cancelled.')
                ->with('ldr_reference_no', $payload['data']['reference_no'] ?? null);
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $status = $exception->getResponse()->getStatusCode();
                $payload = json_decode((string) $exception->getResponse()->getBody(), true);

                if ($status === 404) {
                    return redirect()->route('layout.index')->withErrors([
                        'submission' => 'The layout design request was not found or does not belong to your account.',
                    ]);
                }

                if ($status === 409 || $status === 422) {
                    return redirect()->route('layout.show', $id)->withErrors([
                        'submission' => $payload['message'] ?? 'This request can no longer be cancelled.',
                    ]);
                }
            }

            report($exception);

            return redirect()->route('layout.show', $id)->withErrors([
                'submission' => 'The request service is temporarily unavailable. Please try again later.',
            ]);
        }
    }

    private function normalizeLayoutRequest(array $layoutRequest) {
        foreach (['intended_use', 'marketing_collateral_type'] as $field) {
            if (!is_array($layoutRequest[$field] ?? null)) {
                $decoded = json_decode($layoutRequest[$field] ?? '[]', true);
                $layoutRequest[$field] = is_array($decoded) ? $decoded : [];
            }
        }

        foreach (['peg_samples', 'output_attachments'] as $field) {
            $layoutRequest[$field] = array_map(function ($attachment) {
                $path = $attachment['output_attachment_path'] ?? null;
                $isLink = ($attachment['mime_type'] ?? null) === self::PEG_SAMPLE_LINK_MIME_TYPE;
                $attachment['url'] = $this->externalAssetUrl($path);
                $attachment['is_link'] = $isLink;
                $attachment['display_name'] = $isLink
                    ? $path
                    : ($attachment['original_name'] ?? basename((string) $path));

                return $attachment;
            }, $layoutRequest[$field] ?? []);
        }

        $layoutRequest['peg_sample_url'] = $this->externalAssetUrl(
            $layoutRequest['peg_sample_path'] ?? null
        );

        return $layoutRequest;
    }

    private function extractStoreSignageOption(array &$collateralTypes) {
        foreach (self::STORE_SIGNAGE_OPTIONS as $option) {
            $index = array_search($option, $collateralTypes, true);

            if ($index !== false) {
                unset($collateralTypes[$index]);
                $collateralTypes = array_values($collateralTypes);

                return $option;
            }
        }

        return null;
    }

    private function multipartFields(array $payload, array $skip = []) {
        $multipart = [];

        foreach ($payload as $name => $value) {
            if (in_array($name, $skip, true)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    $multipart[] = [
                        'name' => $name . '[]',
                        'contents' => (string) $item,
                    ];
                }

                continue;
            }

            $multipart[] = [
                'name' => $name,
                'contents' => (string) $value,
            ];
        }

        return $multipart;
    }

    private function appendMultipartFile(array &$multipart, $name, $file) {
        $multipart[] = [
            'name' => $name,
            'contents' => fopen($file->getRealPath(), 'r'),
            'filename' => $file->getClientOriginalName(),
        ];
    }

    private function apiHeaders() {
        $headers = ['Accept' => 'application/json'];
        $apiKey = config('services.ldr.api_key');

        if ($apiKey) {
            $headers['X-DMS-Key'] = $apiKey;
        }

        return $headers;
    }

    private function externalAssetUrl($path) {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $endpoint = parse_url(config('services.ldr.endpoint'));

        if (empty($endpoint['scheme']) || empty($endpoint['host'])) {
            return null;
        }

        $origin = $endpoint['scheme'] . '://' . $endpoint['host'];

        if (!empty($endpoint['port'])) {
            $origin .= ':' . $endpoint['port'];
        }

        return $origin . '/' . ltrim($path, '/');
    }
}
