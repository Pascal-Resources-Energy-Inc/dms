<?php

namespace App\Http\Controllers;

use App\Dealer;
use App\MegaDealer;
use App\Client;
use App\Stove;
use App\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->back()->with('error', 'Please enter a search term.');
        }

        $client = Client::where('name', 'LIKE', '%' . $query . '%')->first();
        $dealer = Dealer::where('name', 'LIKE', '%' . $query . '%')->first();
        $megaDealer = MegaDealer::where('name', 'LIKE', '%' . $query . '%')->first();

        if ($client) {
            return $this->viewProfile($client->id, 'client');
        } elseif ($dealer) {
            return $this->viewProfile($dealer->id, 'dealer');
        } elseif ($megaDealer) {
            return $this->viewProfile($megaDealer->id, 'mega_dealer');
        } else {
            return redirect()->back()->with('error', 'No user found with the name "' . $query . '".');
        }
    }

    public function viewProfile($id, $type)
    {
        if ($type === 'client') {
            $profile = Client::findOrFail($id);
            $transactions = TransactionDetail::where('client_id', $profile->id)->get();
        } elseif ($type === 'dealer') {
            $profile = Dealer::findOrFail($id);
            $transactions = TransactionDetail::where('dealer_id', $profile->user_id)->get();
        } elseif ($type === 'mega_dealer') {
            $profile = MegaDealer::findOrFail($id);
            $transactions = TransactionDetail::where('dealer_id', $profile->user_id)->get();
        } else {
            abort(404);
        }

        return view('view_profile', [
            'profile' => $profile,
            'transactions' => $transactions,
        ]);
    }

    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'LIKE', '%' . $query . '%')
                        ->select('id', 'name')
                        ->limit(5)
                        ->get()
                        ->map(function($client) {
                            return [
                                'id' => $client->id,
                                'name' => $client->name,
                                'type' => 'client'
                            ];
                        });

        $dealers = Dealer::where('name', 'LIKE', '%' . $query . '%')
                        ->select('id', 'name')
                        ->limit(5)
                        ->get()
                        ->map(function($dealer) {
                            return [
                                'id' => $dealer->id,
                                'name' => $dealer->name,
                                'type' => 'dealer'
                            ];
                        });

        $megaDealers = MegaDealer::where('name', 'LIKE', '%' . $query . '%')
                        ->select('id', 'name')
                        ->limit(5)
                        ->get()
                        ->map(function($dealer) {
                            return [
                                'id' => $dealer->id,
                                'name' => $dealer->name,
                                'type' => 'mega_dealer'
                            ];
                        });

        $suggestions = $clients->concat($dealers)->concat($megaDealers)->take(10);

        return response()->json($suggestions);
    }

    public function scanLoyaltyCard(Request $request)
    {
        $rawCode = trim((string) $request->input('code', ''));
        $code = $this->normalizeQrCode($rawCode);

        if ($code === '') {
            return response()->json([
                'success' => false,
                'message' => 'No QR code was detected.',
            ], 422);
        }

        $client = $this->findClientByLoyaltyCode($code);

        if (!$client) {
            return response()->json([
                'success' => true,
                'included' => false,
                'message' => 'No client in the project matched this loyalty card.',
                'code' => $code,
            ]);
        }

        if ($client->status !== 'Active' || !$client->serial_number) {
            return response()->json([
                'success' => true,
                'included' => false,
                'message' => 'Client found, but the loyalty card is not active in the project.',
                'client' => [
                    'name' => $client->name,
                    'status' => $client->status,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'included' => true,
            'message' => 'Client loyalty card verified.',
            'redirect_url' => url('view-client/' . $client->id),
            'order_url' => route('guest-order', ['client_id' => $client->id]),
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'reference' => $client->client_reference,
                'phone' => $client->number,
                'status' => $client->status,
                'serial_id' => $client->serial_number,
            ],
        ]);
    }

    private function normalizeQrCode($rawCode)
    {
        $code = trim((string) $rawCode);

        $json = json_decode($code, true);
        if (is_array($json)) {
            foreach (['client_reference', 'reference', 'loyalty_card', 'loyalty_code', 'serial_number', 'serial', 'code', 'id'] as $key) {
                if (!empty($json[$key])) {
                    return trim((string) $json[$key]);
                }
            }
        }

        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $parts = parse_url($code);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                foreach (['client_reference', 'reference', 'loyalty_card', 'loyalty_code', 'serial_number', 'serial', 'code', 'id'] as $key) {
                    if (!empty($query[$key])) {
                        return trim((string) $query[$key]);
                    }
                }
            }

            if (!empty($parts['path'])) {
                $path = trim($parts['path'], '/');
                $segments = array_values(array_filter(explode('/', $path)));
                if (!empty($segments)) {
                    return trim((string) end($segments));
                }
            }
        }

        return $code;
    }

    private function findClientByLoyaltyCode($code)
    {
        $code = trim((string) $code);

        $clientQuery = Client::with('serial')
            ->where(function ($query) use ($code) {
                $query->where('client_reference', $code)
                    ->orWhere('id', $code)
                    ->orWhere('number', $code)
                    ->orWhere('name', $code);
            });

        $client = $clientQuery->first();

        if ($client) {
            return $client;
        }

        if (!Schema::hasTable('stoves')) {
            return null;
        }

        $stoveColumns = collect(Schema::getColumnListing('stoves'));
        $searchColumns = $stoveColumns
            ->filter(function ($column) {
                return in_array($column, ['id', 'serial_number', 'serial_no', 'serial', 'stove_serial', 'qr_code', 'loyalty_card', 'loyalty_code']);
            })
            ->values();

        if ($searchColumns->isEmpty()) {
            return null;
        }

        $stove = Stove::where(function ($query) use ($searchColumns, $code) {
            foreach ($searchColumns as $index => $column) {
                $index === 0
                    ? $query->where($column, $code)
                    : $query->orWhere($column, $code);
            }
        })->first();

        if (!$stove) {
            return null;
        }

        return Client::with('serial')
            ->where('serial_number', $stove->id)
            ->orWhere('id', $stove->client_id ?? null)
            ->first();
    }

    public function markNotificationRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        
        if ($notificationId) {
            $readNotifications = session('read_notifications', []);
            
            if (!in_array($notificationId, $readNotifications)) {
                $readNotifications[] = $notificationId;
                session(['read_notifications' => $readNotifications]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Notification marked as read'
                ]);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Notification already read'
            ]);
        }
        
        return response()->json([
            'success' => false, 
            'message' => 'Invalid notification ID'
        ]);
    }

    // Mark all notifications as read
    public function markAllNotificationsRead(Request $request)
    {
        try {
            $recentClients = Client::whereDate('created_at', '>=', now()->subDays(3))
                ->orderBy('created_at', 'desc')
                ->get();
            $recentTransactions = TransactionDetail::with(['customer', 'dealer', 'product'])
                ->whereDate('created_at', '>=', now()->subDays(3))
                ->orderBy('created_at', 'desc')
                ->get();
            
            $allNotificationIds = [];
            
            foreach ($recentClients as $client) {
                $allNotificationIds[] = 'client_' . $client->id;
            }
            
            foreach ($recentTransactions as $transaction) {
                $allNotificationIds[] = 'transaction_' . $transaction->id;
            }
            
            session(['read_notifications' => $allNotificationIds]);
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error marking notifications as read: ' . $e->getMessage()
            ]);
        }
    }

    public function getNotificationCount(Request $request)
    {
        $recentClients = Client::whereDate('created_at', '>=', now()->subDays(3))
            ->orderBy('created_at', 'desc')
            ->get();
        $recentTransactions = TransactionDetail::with(['customer', 'dealer', 'product'])
            ->whereDate('created_at', '>=', now()->subDays(3))
            ->orderBy('created_at', 'desc')
            ->get();
        
        $readNotifications = session('read_notifications', []);
        
        $unreadClients = $recentClients->reject(function($client) use ($readNotifications) {
            return in_array('client_' . $client->id, $readNotifications);
        });
        
        $unreadTransactions = $recentTransactions->reject(function($transaction) use ($readNotifications) {
            return in_array('transaction_' . $transaction->id, $readNotifications);
        });
        
        $totalUnread = $unreadClients->count() + $unreadTransactions->count();
        
        return response()->json([
            'success' => true,
            'count' => $totalUnread
        ]);
    }
}
