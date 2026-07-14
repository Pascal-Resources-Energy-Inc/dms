<?php

namespace App\Providers;

use App\DealerStockRequest;
use App\OrderDetail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.header', function ($view) {
            $pendingOrdersCount = 0;
            $pendingStockRequestsCount = 0;

            if (auth()->check()) {
                $user = auth()->user();
                $adId = optional($user->ad)->id;
                $pendingOrdersQuery = OrderDetail::whereRaw("LOWER(TRIM(status)) = 'pending'");

                if ($user->role === 'Area Distributor') {
                    $pendingOrdersQuery->where('ad_id', $adId);
                } elseif ($user->role !== 'Admin') {
                    $pendingOrdersQuery->whereRaw('1 = 0');
                }

                $pendingOrdersCount = $pendingOrdersQuery->count();

                if ($user->role === 'Area Distributor' && $adId) {
                    foreach (['admin_crms', 'admin_crms2'] as $connection) {
                        $pendingOrdersCount += $this->remotePendingOrdersCount($connection, $adId);
                    }
                }

                if ($user->role === 'Admin') {
                    $pendingStockRequestsCount = DealerStockRequest::where('status', 'Pending')->count();
                }
            }

            $view->with([
                'pendingOrdersCount' => $pendingOrdersCount,
                'pendingStockRequestsCount' => $pendingStockRequestsCount,
            ]);
        });
    }

    private function remotePendingOrdersCount($connection, $adId)
    {
        try {
            $schema = DB::connection($connection)->getSchemaBuilder();

            if (
                !$schema->hasTable('order_details') ||
                !$schema->hasColumn('order_details', 'ad_id') ||
                !$schema->hasColumn('order_details', 'status')
            ) {
                return 0;
            }

            return DB::connection($connection)
                ->table('order_details')
                ->where('ad_id', $adId)
                ->whereRaw("LOWER(TRIM(status)) = 'pending'")
                ->when($schema->hasColumn('order_details', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->count();
        } catch (\Exception $exception) {
            return 0;
        }
    }
}
