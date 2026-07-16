<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\DealerController;

Route::get('/', 'HomeController@index')->name('home');
Route::get('/ad-dashboard', 'HomeController@adDashboard')->name('ad-dashboard');
Route::get('/guest-order', 'OrderController@guestOrder')->name('guest-order');
Route::post('/guest-order', 'OrderController@storeGuest')->name('guest-order.store');

Auth::routes();

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/otp', 'Auth\ForgotPasswordController@showOtpForm')->name('password.otp');
Route::post('password/verify-otp', 'Auth\ForgotPasswordController@verifyOtp')->name('password.verify-otp');
Route::get('password/reset/form', 'Auth\ForgotPasswordController@showResetForm')->name('password.reset.form');
Route::post('password/update', 'Auth\ForgotPasswordController@reset')->name('password.update');


Route::group(['middleware' => 'auth'], function () {

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/transactions','TransactionController@index')->name('transactions');
Route::get('/ad-transactions','TransactionController@adTransactions')->name('ad-transactions');
Route::get('/customer-ads','TransactionController@customerAds')->name('customer-ads');
Route::delete('/transactions/{id}', 'TransactionController@destroy')->name('transactions.destroy');
Route::post('/transactions/bulk-delete', 'TransactionController@bulkDelete')->name('transactions.bulkDelete');

Route::get('/get-province-details', 'HomeController@getProvinceDetails')->name('province.details');

// Products
Route::get('/about', 'HomeController@about')->name('about');
Route::get('/items', 'ItemController@index')->name('items');
Route::post('/items', 'ItemController@store')->name('items.store');
Route::put('/items/{item}', 'ItemController@update')->name('items.update');
Route::delete('/items/{item}', 'ItemController@destroy')->name('items.destroy');
Route::get('/areas', 'AreaController@index')->name('areas');
Route::post('/areas', 'AreaController@store')->name('areas.store');
Route::put('/areas/{area}', 'AreaController@update')->name('areas.update');
Route::delete('/areas/{area}', 'AreaController@destroy')->name('areas.destroy');
Route::get('/products', 'ProductController@index')->name('products');
Route::get('/products/create', 'ProductController@create')->name('products.create');
Route::post('/products', 'ProductController@store')->name('products.store');
Route::put('/products/{id}', 'ProductController@update');
Route::delete('/products/{product}', 'ProductController@destroy')->name('products.destroy');
Route::post('/products/bulk-update', 'ProductController@storeBulk')
    ->name('products.storeBulk');

// Inventory Transfer
Route::get('/inventory-transfers', 'InventoryTransferController@index')->name('inventory-transfers.index');
Route::post('/inventory-transfers', 'InventoryTransferController@store')->name('inventory-transfers.store');
Route::delete('/inventory-transfers/{id}', 'InventoryTransferController@destroy')->name('inventory-transfers.destroy');

Route::get('/storelocation', 'HomeController@storelocation')->name('storelocation');
Route::get('/api/locations-map', 'HomeController@getLocationsForMap')->name('locations.map');
Route::get('/api/location-details/{id}/{type}', 'HomeController@getLocationDetails')->name('location.details');
Route::get('/home/monthly-data', 'HomeController@getMonthlyDataAjax')->name('home.monthly-data');
Route::get('/home/chart-data', 'HomeController@getChartDataAjax')->name('home.chart-data');
Route::get('/home/live-overview', 'HomeController@liveOverview')->name('home.live-overview');
// Route::get('/ad/philippine-map', 'HomeController@philippineDealerMap');

Route::post('/store-transaction','TransactionController@store')->name('new-transaction');
Route::post('/store-transaction-admin','TransactionController@storeAdmin')->name('new-transaction');
Route::post('/store-transaction-ad','TransactionController@storeAd')->name('ad.new-transaction');
Route::get('user-profile','UserController@view');
Route::get('get-user/{id}','CustomerController@getUser');

Route::get('/search', 'SearchController@search')->name('search');
Route::get('/search/suggestions', 'SearchController@searchSuggestions')->name('search.suggestions');
Route::post('/loyalty-card/scan', 'SearchController@scanLoyaltyCard')->name('loyalty-card.scan');
Route::get('/profile/{id}/{type}', 'SearchController@viewProfile')->name('profile.view');
Route::post('/notification/save', 'NotificationController@saveNotification')->name('notification.save');
Route::post('/notification/mark-read', 'NotificationController@markAsRead')->name('notification.markRead');
Route::post('/notifications/mark-all-read', 'NotificationController@markAllAsRead')->name('notifications.markAllAsRead');
Route::get('/notifications/unread-count', 'NotificationController@getUnreadCount')->name('notifications.unreadCount');

// Route::get('/users','EditUserController@index')->name('users');
Route::put('edit-users/{id}', 'EditUserController@update')->name('edit-users');
Route::post('/new-admin','EditUserController@store')->name('new-admin');
Route::post('admin-privillege/{id}', 'EditUserController@updatePrivilege')->name('admin.privilege.update');
// Route::get('/users-data', 'EditUserController@datatable')->name('users.data');

// Area Distributor
Route::get('/ads','AreaDistributorController@index')->name('ads');
Route::get('view-ad/{id}', 'AreaDistributorController@view')->name('ad.view');
Route::post('/new-ad','AreaDistributorController@store')->name('new-ad');
Route::put('edit-ads/{id}', 'AreaDistributorController@update')->name('edit-ads');
Route::post('/geocode-location', 'AreaDistributorController@geocodeLocation')->name('geocode.location');
Route::post('ad/{id}/areas/update', 'AreaDistributorController@updateAreas')->name('update.areas');


// Provincial Distributor
Route::get('/pds','ProvincialDistributorController@index')->name('pds');

// Dealers Area Distributor
Route::get('/dealer-ads','AreaDistributorController@myDealer')->name('dealer-ads');
Route::get('/md-ads','AreaDistributorController@myMegaDealer')->name('md-ads');
Route::get('/charges', 'OtherChargeController@index')->name('charges');
Route::post('/charges', 'OtherChargeController@store')->name('charges.store');
Route::put('/charges/{charge}', 'OtherChargeController@update')->name('charges.update');
Route::delete('/charges/{charge}', 'OtherChargeController@destroy')->name('charges.destroy');

// Orders
Route::get('/orders','OrderController@index')->name('orders');
Route::get('/orders/export','OrderController@export')->name('orders.export');
Route::get('/orders/purchase-order','OrderController@purchaseOrder')->name('orders.purchase-order');
Route::post('/orders','OrderController@store')->name('orders.store');
Route::put('/orders/{id}', 'OrderController@update')->name('orders.update');

// Area Distributor Purchase Orders
Route::get('/ad-purchase-orders', 'AdPurchaseOrderController@index')->name('ad-purchase-orders.index');
Route::get('/ad-purchase-orders/export', 'AdPurchaseOrderController@export')->name('ad-purchase-orders.export');
Route::get('/ad-purchase-orders/create', 'AdPurchaseOrderController@create')->name('ad-purchase-orders.create');
Route::post('/ad-purchase-orders', 'AdPurchaseOrderController@store')->name('ad-purchase-orders.store');
Route::post('/ad-purchase-orders/products/{item}/favorite', 'AdPurchaseOrderController@toggleFavoriteProduct')->name('ad-purchase-orders.products.favorite');
Route::get('/warehouse/ad-purchase-orders/region-v', 'AdPurchaseOrderController@regionVWarehouseIndex')->name('warehouse-ad-purchase-orders.region-v');
Route::get('/warehouse/ad-purchase-orders/region-v/export', 'AdPurchaseOrderController@exportRegionVWarehouse')->name('warehouse-ad-purchase-orders.region-v.export');
Route::get('/ad-purchase-orders/{id}', 'AdPurchaseOrderController@show')->name('ad-purchase-orders.show');
Route::patch('/ad-purchase-orders/{id}/status', 'AdPurchaseOrderController@updateStatus')->name('ad-purchase-orders.updateStatus');
Route::delete('/ad-purchase-orders/{id}', 'AdPurchaseOrderController@destroy')->name('ad-purchase-orders.destroy');

Route::get('/dealers','DealerController@index')->name('dealers');
Route::get('/mds','AreaDistributorController@megaDealers')->name('mds');
Route::post('/new-dealer','DealerController@newDealer');
Route::post('/check-dealer-duplicate', 'DealerController@checkDuplicate')->name('check.dealer.duplicate');
Route::get('admin-crm-dealer/{source}/{id}', 'DealerController@viewAdminCrmDealer')->name('admin.crm.dealer.view');
Route::get('view-dealer/{id}', 'DealerController@view')->name('dealer.view');
Route::post('/change-avatar-dealer/{id}', 'DealerController@changeAvatar')->name('dealer.view');
Route::post('valid-id-dealer/{id}', 'DealerController@uploadValidId')->name('dealer.view');
Route::post('/submit-contract-dealer/{id}','DealerController@contractSign')->name('sign');
Route::get('/dashboard-dealer','DealerController@show')->name('Dealer');
Route::post('/dealer/update/{id}', 'DealerController@update')->name('dealer.update');
Route::get('/get-zipcode1', [DealerController::class, 'getZipCode1']);

Route::get('/customers','CustomerController@index')->name('customers');
Route::get('/customer','CustomerController@view')->name('customer');
Route::get('/dashboard-customer','CustomerController@show')->name('customer');
Route::get('/new-customer','CustomerController@newCustomer')->name('newcustomer');
Route::get('admin-crm-customer/{source}/{id}', 'CustomerController@viewAdminCrmCustomer')->name('admin.crm.customer.view');
Route::get('view-client/{id}', 'CustomerController@view')->name('client.view');
Route::post('new-customer','CustomerController@saveCustomer')->name('saveCustomer');
Route::post('/customer/update/{id}', 'CustomerController@update')->name('customer.update');
Route::post('/change-avatar/{id}','CustomerController@changeAvatar')->name('changeAvatar');
Route::post('/valid-id/{id}','CustomerController@uploadValidId')->name('uploadValidId');
Route::post('/submit-contract/{id}','CustomerController@contractSign')->name('sign');

Route::get('/signature/{id}','CustomerController@sign');
Route::get('/signature-dealer/{id}','DealerController@sign');

Route::get('/rewards', 'RewardController@index')->name('rewards');
Route::post('/rewards', 'RewardController@store')->name('rewards.store');
Route::put('/rewards/{reward}', 'RewardController@update')->name('rewards.update');
Route::delete('/rewards/{reward}', 'RewardController@destroy')->name('rewards.destroy');

Route::post('/rewards/{id}/update-status', 'RewardController@updateStatus')->name('rewards.updateStatus');

Route::get('/vouchers', 'VoucherController@index')->name('vouchers');
Route::get('/my-vouchers', 'VoucherController@myVouchers')->name('vouchers.mine');
Route::get('/vouchers/export', 'VoucherController@export')->name('vouchers.export');
Route::get('/vouchers/distributor-areas', 'VoucherController@distributorAreas')->name('vouchers.distributor-areas');
Route::get('/vouchers/{voucher}/ad-orders', 'VoucherController@adOrders')->name('vouchers.ad-orders');
Route::get('/vouchers/{voucher}/ad-orders/export', 'VoucherController@exportAdOrders')->name('vouchers.ad-orders.export');
Route::get('/vouchers/import-template', 'VoucherController@exportImportTemplate')->name('vouchers.import-template');
Route::post('/vouchers', 'VoucherController@store')->name('vouchers.store');
Route::post('/vouchers/upload', 'VoucherController@upload')->name('vouchers.upload');
Route::put('/vouchers/{voucher}', 'VoucherController@update')->name('vouchers.update');
Route::delete('/vouchers/{voucher}', 'VoucherController@destroy')->name('vouchers.destroy');
Route::get('/vouchers/available-for-territory', 'VoucherController@availableForTerritory')->name('vouchers.available-for-territory');
Route::post('/vouchers/check', 'VoucherController@check')->name('vouchers.check');

// Raffles
Route::get('/raffles', 'RaffleController@index')->name('raffles');
Route::post('/raffles', 'RaffleController@store')->name('raffles.store');
Route::put('/raffles/{raffle}', 'RaffleController@update')->name('raffles.update');
Route::delete('/raffles/{raffle}', 'RaffleController@destroy')->name('raffles.destroy');
Route::post('/raffles/{raffle}/entries', 'RaffleController@storeEntry')->name('raffles.entries.store');
Route::delete('/raffles/{raffle}/entries/{entry}', 'RaffleController@destroyEntry')->name('raffles.entries.destroy');
Route::post('/raffles/{raffle}/draw', 'RaffleController@draw')->name('raffles.draw');

// Users
Route::get('/users/{id}/show', 'UserController@show');

Route::post('/users/update', 'UserController@update')->name('users.update');
Route::post('/users/access-update', 'UserController@updateAccess')->name('users.access.update');

Route::get('/users/data', 'UserController@datatable')->name('users.data');

Route::get('/users','UserController@index')->name('users');
Route::post('/new-user','UserController@store')->name('new-admin');
Route::post('/users/mobile-otp/send', 'UserController@sendMobileOtp')->name('users.mobile-otp.send');
Route::post('/users/mobile-otp/verify', 'UserController@verifyMobileOtp')->name('users.mobile-otp.verify');
Route::post('/generate-partner-code', 'UserController@generatePartnerCode')->name('generate.partner.code');
Route::post('/check-mothers-name', 'UserController@checkMothersName')->name('check.mothers.name');
Route::post('/check-user-duplicate', 'UserController@checkDuplicate')->name('check.user.duplicate');
Route::post('/get-zipcode', [UserController::class, 'getZipCode'])->name('get.zipcode');

Route::get('/stock-requests', 'DealerStockRequestController@adminIndex')->name('admin.stock.requests');
Route::post('/stock-requests', 'DealerStockRequestController@store')->name('dealer.stock.requests.store');
Route::post('/stock-requests/{id}/approve', 'DealerStockRequestController@approve')->name('admin.stock.requests.approve');
Route::post('/stock-requests/{id}/reject', 'DealerStockRequestController@reject')->name('admin.stock.requests.reject');

// Reports
Route::get('/reports/daily-sales', 'ReportController@dailySalesReport')->name('dsr');
Route::get('/reports/daily-sales/export', 'ReportController@exportDailySales')->name('reports.daily.export');
Route::get('/reports/distributor-other-charges', 'ReportController@distributorOtherChargesReport')->name('reports.distributor-other-charges');
Route::get('/reports/aging', 'ReportController@agingReport')->name('aging');
Route::get('/reports/aging-report-dealer', 'ReportController@dealerAgingReport')->name('aging-report-dealer');
Route::get('/reports/dpo-report', 'ReportController@dpoReport')->name('dpo');
Route::get('/reports/isl-report', 'ReportController@inventoryStockLevelReport')->name('isl');
Route::get('/reports/isl-report/export', 'ReportController@exportInventoryStockLevel')->name('isl.export');
Route::get('/reports/monthly-sales', 'ReportController@monthlySalesReport')->name('monthly-sales');
Route::get('/reports/monthly-sales/export', 'ReportController@exportMonthlySales')->name('monthly-sales.export');
Route::get('/reports/voucher-history', 'ReportController@voucherHistoryReport')->name('voucher-history');
Route::get('/reports/voucher-history/export', 'ReportController@exportVoucherHistory')->name('voucher-history.export');
Route::get('/reports/signup-incentives', 'ReportController@signupIncentivesReport')->name('signup-incentives');
Route::get('/reports/signup-incentives/clients', 'ReportController@signupIncentiveClients')->name('signup-incentives.clients');
Route::get('/reports/signup-incentives/export', 'ReportController@exportSignupIncentives')->name('signup-incentives.export');
Route::get('/reports/repeat-purchase-incentives', 'ReportController@repeatPurchaseIncentivesReport')->name('repeat-purchase-incentives');
Route::get('/reports/repeat-purchase-incentives/transactions', 'ReportController@repeatPurchaseTransactions')->name('repeat-purchase-incentives.transactions');
Route::get('/reports/repeat-purchase-incentives/export', 'ReportController@exportRepeatPurchaseIncentives')->name('repeat-purchase-incentives.export');


});

Route::get('/test-db', function () {
    try {
        DB::connection('admin_crms2')->getPdo();

        return 'Connected!';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
