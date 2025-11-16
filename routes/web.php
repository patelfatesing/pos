<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ItemController;
use App\Livewire\Shoppingcart;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\CommissionUserController;
use App\Http\Controllers\PartyUserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CashInHandController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\VendorListController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Livewire\CashBreakdown;
use App\Http\Controllers\CashController;
use App\Http\Controllers\ShiftClosingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Livewire\ShiftClosingForm;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DemandOrderController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ShiftManageController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\CreditHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Report2Controller;
use App\Http\Controllers\PurchaseLedgerController;
use App\Http\Controllers\Accounting\GroupController;
use App\Http\Controllers\Accounting\LedgerController;
use App\Http\Controllers\Accounting\VoucherController;
use App\Http\Controllers\RolePermissionController;

// Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('users/change-password', [UserController::class, 'changePassword']);

Route::get('/logs', function () {
    $file = storage_path('logs/laravel-' . now()->format('Y-m-d') . '.log');

    if (!File::exists($file)) {
        abort(404, 'Log file not found.');
    }

    return Response::make(nl2br(e(File::get($file))), 200, [
        'Content-Type' => 'text/html',
    ]);
});


Route::get('/shift-closing', ShiftClosingForm::class);
Route::get('/cash-tender', [CashController::class, 'index']);
Route::post('/calculate-change', [CashController::class, 'calculateChange']);


Route::get('/cash-breakdown', CashBreakdown::class);

Route::get('lang/{locale}', function ($locale) {

    if (in_array($locale, ['en', 'hi', 'sq'])) {
        Session::put('locale', $locale);
        App::setLocale($locale);
    }

    return redirect()->back();
});


Route::middleware(['role:admin'])->get('/admin-dashboard', function () {
    return 'Admin Dashboard';
});

Route::middleware(['permission:editor_permission'])->get('/editor-dashboard', function () {
    return 'Editor Dashboard';
});

Route::get('/', function () {
    return redirect('/login');
})->name('root');

Route::post('/shift-close/store', [ShiftClosingController::class, 'store'])->name('shift-close.store');
Route::post('/shift-close/withdraw', [ShiftClosingController::class, 'withdraw'])->name('shift-close.withdraw');
Route::get('/shift-summary/{shiftId}', [ShiftClosingController::class, 'getShiftSummary']);

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get('/dashboard/store/{store}', [DashboardController::class, 'showStore'])->name('dashboard.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/users/list', [UserController::class, 'index'])->name('users.list');
    Route::post('/users/get-data', [UserController::class, 'getData'])->name('users.getData');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/delete', [UserController::class, 'destroy'])->name('users.delete');
    Route::post('/users/status-change', [UserController::class, 'statusChange'])->name('users.status-change');
    Route::get('/open-drawer', [UserController::class, 'openDrawer']);

    Route::post('/cash-in-hand', [CashInHandController::class, 'store'])->name('cash-in-hand')->middleware('auth');

    Route::get('/roles/list', [RolesController::class, 'index'])->name('roles.list');
    Route::post('/roles/get-data', [RolesController::class, 'getData'])->name('roles.getData');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/edit/{id}', [RolesController::class, 'edit'])->name('roles.edit');
    Route::get('/roles/view/{role}', [RolesController::class, 'show'])->name('roles.show');
    Route::post('/roles/update', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
    Route::post('{role}/permissions', [RolesController::class, 'updatePermission'])
        ->name('roles.permissions.update');

    Route::get('/store/list', [BranchController::class, 'index'])->name('branch.list');
    Route::post('/store/get-data', [BranchController::class, 'getData'])->name('user.getData');
    Route::get('/store/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/store/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/store/edit/{id}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/store/update', [BranchController::class, 'update'])->name('branch.update');
    Route::post('/store/delete', [BranchController::class, 'destroy'])->name('branch.destroy');
    Route::post('/store/status-change', [BranchController::class, 'statusChange'])->name('store.status-change');
    Route::post('/store/update-status', [BranchController::class, 'updateStatus'])->name('branch.update.status');
    Route::get('/get-available-notes', [BranchController::class, 'getAvailableNotes']);

    Route::get('/products/list', [ProductController::class, 'index'])->name('products.list');
    Route::post('/products/get-data', [ProductController::class, 'getData'])->name('products.getData');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products/add', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/edit/{id}', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('/products/update', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/update-price', [ProductController::class, 'updatePrice'])->name('products.updatePrice');
    Route::post('/products/delete', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/products/check-barcode', [ProductController::class, 'check'])->name('products.checkbarcode');
    Route::get('/products/pic', [ProductController::class, 'pic'])->name('products.pic');
    Route::post('/products/upload-pic', [ProductController::class, 'uploadPhoto'])->name('products.uploadpic');
    // Route::post('/products/upload-pic', [ProductController::class, 'uploadPhotp'])->name('products.upload');
    Route::get('/products/availability/{id}', [ProductController::class, 'getAvailability']);
    Route::get('/products/get-availability-branch/{id}', [ProductController::class, 'getAvailabilityBranch']);
    Route::get('/products/download-sample', [ProductController::class, 'sampleFileDownload'])->name('products.download-sample');

    Route::get('/stock/list', [StockController::class, 'index'])->name('stock.list');
    Route::post('/stock/get-data', [StockController::class, 'getData'])->name('stock.getData');
    Route::get('/stock/add', [StockController::class, 'add'])->name('stock.add');
    Route::post('/stock/store', [StockController::class, 'store'])->name('stock.store');
    Route::post('/stock/stock-request-from-store', [StockController::class, 'stockRequestFromStore'])->name('stock.stock-request-from-store');

    Route::get('/stock/request-list', [StockController::class, 'show'])->name('stock.requestList');
    Route::post('/stock/get-request-data', [StockController::class, 'getRequestData'])->name('stock.getRequestData');
    Route::get('/stock/view/{id}', [StockController::class, 'view'])->name('stock.view');
    Route::get('/stock/view-request/{id}', [StockController::class, 'viewRequest'])->name('stock.viewRequest');
    Route::get('/stock/stock-request-view/{id}', [StockController::class, 'stockRequestView'])->name('stock.stock-request-view');
    Route::post('/stock-requests/{id}/approve', [StockController::class, 'approve'])
        ->name('stock-requests.approve');
    Route::post('/stock-requests/{id}/reject', [StockController::class, 'reject'])
        ->name('stock-requests.reject');
    Route::get('/stock-requests/popup-details/{id}', [StockController::class, 'stockShow'])->name('stock.popupDetails');

    Route::get('/stock/edit/{id}', [StockController::class, 'edit'])->name('stock.edit');
    Route::get('/stock/add-warehouse', [StockController::class, 'addWarehouse'])->name('addWarehouse');
    Route::post('/stock/store-warehouse', [StockController::class, 'storeWarehouse'])->name('stock.warehouse');
    Route::get('/stock/send-request-list', [StockController::class, 'showSendRequest'])->name('stock.requestSendList');
    Route::post('/stock/get-send-request-data', [StockController::class, 'getSendRequestData'])->name('stock.getSendRequestData');
    Route::post('/stock/get-stock-request-details-approved', [StockController::class, 'getStockRequestDetailsApproved'])->name('stock.get-stock-request-details-approved');
    Route::post('/stock/get-stock-request-details', [StockController::class, 'getStockRequestDetails'])->name('stock.get-stock-request-details');

    // Route::get('/stock/send-store-request-list', [StockController::class, 'showStoreSendRequest'])->name('stock.requestStoreSendList');
    // Route::post('/stock/get-send-store-request-data', [StockController::class, 'getStoreSendRequestData'])->name('stock.getSendStoreRequestData');


    Route::get('/products/subcategory/{category_id}', [ProductController::class, 'getSubcategories'])->name('get.subcategories');
    Route::get('/products/getpacksize/{category_id}', [ProductController::class, 'getPackSize'])->name('get.getpacksize');
    Route::get('/products/get-products/{category_id}', [ProductController::class, 'getProducts'])->name('get.products');

    Route::get('/barcode/{productCode}', [ProductController::class, 'generateBarcode'])->name('barcode.generate');
    Route::post('/products/barcode/check', [ProductController::class, 'barcodeCheck'])->name('products.check');
    Route::get('/products/barcode-print/{id}', [ProductController::class, 'barcodePrint'])->name('products.barcode-print');


    Route::get('/inventories/list', [InventoryController::class, 'index'])->name('inventories.list');
    Route::post('/inventories/get-data', [InventoryController::class, 'getData'])->name('inventories.getData');
    Route::get('/inventories/create', [InventoryController::class, 'create'])->name('inventories.create');
    Route::post('/inventories/add', [InventoryController::class, 'store'])->name('inventories.store');
    Route::get('/inventories/edit/{id}', [InventoryController::class, 'edit'])->name('inventories.edit');
    Route::get('/inventories/add-stock/{id}', [InventoryController::class, 'addStock'])->name('inventories.add-stock');
    Route::get('/inventories/edit1/{id}', [InventoryController::class, 'editStock'])->name('inventories.edit-stock');
    Route::post('/inventories/store-stock', [InventoryController::class, 'storeStock'])->name('inventories.stockStore');
    Route::post('/inventories/update-low-level-qty', [InventoryController::class, 'updateLowLevelQty'])->name('inventories.update-low-level-qty');
    Route::get('/inventories/get-low-level-products/{storeId}', [InventoryController::class, 'getLowLevelProducts'])->name('inventories.get-low-level-products');
    Route::post('/inventories/update-multiple-low-level-qty', [InventoryController::class, 'updateMultipleLowLevelQty'])->name('inventories.update-multiple-low-level-qty');
    Route::post('/inventories/check-inventory', [InventoryController::class, 'checkStock'])->name('inventory.check');

    // Route::get('/stock/list', [InventoryController::class, 'index'])->name('inventories.list');
    // Route::post('/inventories/get-data', [InventoryController::class, 'getData'])->name('inventories.getData');


    Route::get('/items/list', [ItemController::class, 'index'])->name('items.list');
    Route::get('/items/cart', [ItemController::class, 'cart'])->name('items.cart');

    Route::get('/items/cart-new', [ItemController::class, 'cartNew'])->name('items.cart.new');
    Route::get('/items/{id}', [ItemController::class, 'show'])->name('items.show');
    Route::post('/items/{id}/resume', [ItemController::class, 'resume'])->name('items.resume');

    Route::post('/items/get-data', [ItemController::class, 'getData'])->name('items.getData');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::post('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/categories/list', [CategoryController::class, 'index'])->name('categories.list');
    Route::post('/categories/get-data', [CategoryController::class, 'getData'])->name('categories.getData');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/subcategories/list', [SubCategoryController::class, 'index'])->name('subcategories.list');
    Route::post('/subcategories/get-data', [SubCategoryController::class, 'getData'])->name('subcategories.getData');
    Route::get('/subcategories/create', [SubCategoryController::class, 'create'])->name('subcategories.create');
    Route::post('/subcategories/store', [SubCategoryController::class, 'store'])->name('subcategories.store');
    Route::get('/subcategories/edit/{id}', [SubCategoryController::class, 'edit'])->name('subcategories.edit');
    Route::post('/subcategories/update', [SubCategoryController::class, 'update'])->name('subcategories.update');
    Route::delete('/subcategories/delete/{id}', [SubCategoryController::class, 'destroy'])->name('subcategories.destroy');

    Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('/view-invoice/{invoice}/{shift_id?}', [InvoiceController::class, 'viewInvoice'])->name('invoice.view-invoice');
    Route::get('/sales/edit-sales/{invoice_id}', [InvoiceController::class, 'editSales'])->name('sales.edit-sales');
    Route::get('/sales/add-sales/{branch_id}/{shift_id}', [InvoiceController::class, 'addSales'])->name('sales.add-sales');
    Route::get('/view-hold-invoice/{invoice}/{shift_id}', [InvoiceController::class, 'viewHoldInvoice'])->name('invoice.view-hold-invoice');
    Route::post('/invoice/{id}/add-item', [InvoiceController::class, 'addItem']);
    Route::post('/invoice/{id}/update-qty', [InvoiceController::class, 'updateQty']);
    Route::post('/invoice/{id}/delete-item', [InvoiceController::class, 'deleteItem']);
    Route::post('/sales/invoice/update-items/{id}', [InvoiceController::class, 'updateItems'])->name('sales.invoice.updateItems');
    Route::get('/invoice/{id}/history', [InvoiceController::class, 'fetchHistory'])->name('invoice.fetchHistory');
    Route::get('/party-customer-discount/{partyUserId}', [InvoiceController::class, 'getPartyCustomerDiscount'])->name('partyCustomerDiscount');
    Route::post('/sales/invoice/insert-sale', [InvoiceController::class, 'InsertSale'])->name('sales.invoice.insert-sale');

    Route::get('/pack-size/list', [PackSizeController::class, 'index'])->name('packsize.list');
    Route::post('/pack-size/get-data', [PackSizeController::class, 'getData'])->name('packsize.getData');
    Route::get('/pack-size/create', [PackSizeController::class, 'create'])->name('packsize.create');
    Route::post('/pack-size/store', [PackSizeController::class, 'store'])->name('packsize.store');
    Route::get('/pack-size/edit/{id}', [PackSizeController::class, 'edit'])->name('packsize.edit');
    Route::post('/pack-size/update', [PackSizeController::class, 'update'])->name('packsize.update');
    Route::delete('/pack-size/delete/{id}', [PackSizeController::class, 'destroy'])->name('packsize.destroy');

    // Route::middleware(['auth', 'admin'])->prefix('commission-users')->name('commission-users.')->group(function () {

    Route::get('/commission-users/list', [CommissionUserController::class, 'index'])->name('commission-users.list');
    Route::post('/commission-users/get-data', [CommissionUserController::class, 'getData'])->name('commission-users.getData');
    Route::get('/commission-users/create', [CommissionUserController::class, 'create'])->name('commission-users.create');
    Route::post('/commission-users/', [CommissionUserController::class, 'store'])->name('commission-users.store');
    Route::get('/commission-users/edit/{id}', [CommissionUserController::class, 'edit'])->name('commission-users.edit');
    Route::put('/commission-users/{Commissionuser}', [CommissionUserController::class, 'update'])->name('commission-users.update');
    Route::post('/commission-users/destroy', [CommissionUserController::class, 'destroy'])->name('commission-users.destroy');
    Route::get('/commission-cust/view/{id}', [CommissionUserController::class, 'view'])->name('commission-cust.view');
    Route::get('/commission-cust/trasaction-photo-view/{id}', [CommissionUserController::class, 'custTrasactionPhoto'])->name('commission-cust.trasaction-photo-view');
    Route::post('/commission-cust/get-commission-data', [CommissionUserController::class, 'getDataCommission'])->name('commission-cust.get.commission.data');
    Route::post('/commission-cust/status-change', [CommissionUserController::class, 'statusChange'])->name('commission-cust.status-change');
    // });

    // Route::middleware(['auth', 'admin'])->prefix('party-users')->name('party-users.')->group(function () {
    Route::get('/party-users/list', [PartyUserController::class, 'index'])->name('party-users.list');
    Route::post('/party-users/get-data', [PartyUserController::class, 'getData'])->name('party-users.getData');
    Route::get('/party-users/create', [PartyUserController::class, 'create'])->name('party-users.create');
    Route::post('/party-users/', [PartyUserController::class, 'store'])->name('party-users.store');
    Route::get('/party-users/edit/{id}', [PartyUserController::class, 'edit'])->name('party-users.edit');
    Route::get('/party-users/view/{id}', [PartyUserController::class, 'view'])->name('party-users.view');
    Route::put('/party-users/{Partyuser}', [PartyUserController::class, 'update'])->name('party-users.update');
    Route::delete('/party-users/{Partyuser}', [PartyUserController::class, 'destroy'])->name('party-users.destroy');
    Route::get('/cust-product-price-change/form', [PartyUserController::class, 'custProductPriceChangeForm'])->name('party-users.cust-product-price-change-form');
    Route::get('/party-user-credit/{partyUserId}', [PartyUserController::class, 'getCredit'])
        ->name('partyUserCredit');
    Route::get('/party-customer-discount/{partyUserId}/{productId}', [PartyUserController::class, 'getCustomerDiscount']);

    Route::post('/cust-product-price-change/price_change-store', [PartyUserController::class, 'custPriceChange'])->name('cust-product-price-change-store');
    Route::post('/party-users/get-commission-data', [PartyUserController::class, 'getDataCommission'])->name('party-users.get.commission.data');
    Route::get('/cust-trasaction-photo/view/{id}', [PartyUserController::class, 'custTrasactionPhoto'])->name('cust-trasaction-photo-view');
    Route::post('/party-users/status-change', [PartyUserController::class, 'statusChange'])->name('party-users.status-change');
    Route::post('/party-users/get-credit-history', [PartyUserController::class, 'getCreditHistory'])->name('party-users.get.credit.history');
    Route::post('/party-users/set-due-date', [PartyUserController::class, 'setDueDate'])->name('party-users.set.due.date');
    // });

    Route::get('/stock-transfer/craete-transfer', [StockTransferController::class, 'craeteTransfer'])->name('stock-transfer.craete-transfer');
    Route::get('/stock-transfer/list', [StockTransferController::class, 'index'])->name('stock-transfer.list');
    Route::get('/stock-transfer/get-transfer-data', [StockTransferController::class, 'getTransferData'])->name('stock-transfer.get-transfer-data');
    Route::get('/stock-transfer/view/{id}', [StockTransferController::class, 'view'])->name('stock-transfer.view');

    Route::post('/stock-transfer/store', [StockTransferController::class, 'store'])->name('stock-transfer.store');

    Route::get('/vendor/list', [VendorListController::class, 'index'])->name('vendor.list');
    Route::post('/vendor/get-data', [VendorListController::class, 'getData'])->name('vendor.getData');
    Route::get('/vendor/create', [VendorListController::class, 'create'])->name('vendor.create');
    Route::post('/vendor/', [VendorListController::class, 'store'])->name('vendor.store');
    Route::get('/vendor/edit/{id}', [VendorListController::class, 'edit'])->name('vendor.edit');
    Route::post('/vendor/update', [VendorListController::class, 'update'])->name('vendor.update');
    Route::delete('/vendor/{Partyuser}', [VendorListController::class, 'destroy'])->name('vendor.destroy');
    Route::post('/vendor/status-change', [VendorListController::class, 'statusChange'])->name('vendor.status-change');

    Route::get('/purchase/list', [PurchaseController::class, 'index'])->name('purchase.list');
    Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::post('/purchase/get-data', [PurchaseController::class, 'getData'])->name('purchase.getData');
    Route::get('/purchase/view/{id}', [PurchaseController::class, 'view'])->name('purchase.view');
    Route::get('/vendor/get-product-details/{id}', [PurchaseController::class, 'getProductDetails'])->name('vendor.get-product-details');
    Route::get('/vendor-products/{vendor}', [PurchaseController::class, 'getVendorProducts'])
        ->name('vendor.products');

    Route::get('/popup/form/{type}', [NotificationController::class, 'loadForm']);
    Route::get('/notifications/index', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/fetch-data', [NotificationController::class, 'getData'])->name('notifications.fetch-data');
    Route::get('/notifications/get-notification', [NotificationController::class, 'getNotication'])->name('notifications.get-notication');
    Route::get('/notifications/expired-product/{id}', [NotificationController::class, 'viewExpiredProducts'])->name('notifications-expired-product');


    // routes/web.php
    Route::get('sales/sales-list', [SalesReportController::class, 'salasList'])->name('sales.sales.list');
    Route::get('sales/sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::post('sales-report/data', [SalesReportController::class, 'getSalesReportData'])->name('sales.report.data');
    Route::post('sales/get-data', [SalesReportController::class, 'getData'])->name('sales.get.data');
    Route::get('/store-sales-summary', [SalesReportController::class, 'storeSummary'])->name('store-sales-summary');

    Route::get('/sales/sales-daily', [SalesReportController::class, 'salesDaily'])->name('sales.sales-daily');
    Route::get('/sales/branch-sales-report', [SalesReportController::class, 'branchSalesReport'])->name('sales.branch.sales.report');

    Route::get('/sales/stock-report', [SalesReportController::class, 'stockReport'])->name('sales.stock.report');
    Route::get('/sales/fetch-stock-data', [SalesReportController::class, 'fetchStockData'])->name('sales.fetch-stock-data');

    Route::get('/sales/commission-report', [SalesReportController::class, 'commissionReport'])->name('sales.commission.report');
    Route::get('/sales/fetch-commission-data', [SalesReportController::class, 'commissionInvoicesReport'])->name('sales.fetch-commission-data');
    Route::get('/sales-img-view/{id}', [SalesReportController::class, 'show'])->name('sales.img.view');

    Route::get('/exp-category/list', [ExpenseCategoryController::class, 'index'])->name('exp_category.list');
    Route::post('/exp-category/get-data', [ExpenseCategoryController::class, 'getData'])->name('exp_category.getData');
    Route::get('/exp-category/create', [ExpenseCategoryController::class, 'create'])->name('exp_category.create');
    Route::post('/exp-category/store', [ExpenseCategoryController::class, 'store'])->name('exp_category.store');
    Route::get('/exp-category/edit/{id}', [ExpenseCategoryController::class, 'edit'])->name('exp_category.edit');
    Route::post('/exp-category/update', [ExpenseCategoryController::class, 'update'])->name('exp_category.update');
    Route::delete('/exp-category/delete/{id}', [ExpenseCategoryController::class, 'destroy'])->name('exp_category.destroy');
    Route::post('/exp-category/status-change', [ExpenseCategoryController::class, 'statusChange'])->name('exp_category.status-change');


    Route::get('/purchase-ledger/list', [PurchaseLedgerController::class, 'index'])->name('purchase_ledger.list');
    Route::post('/purchase-ledger/get-data', [PurchaseLedgerController::class, 'getData'])->name('purchase_ledger.getData');
    Route::get('/purchase-ledger/create', [PurchaseLedgerController::class, 'create'])->name('purchase_ledger.create');
    Route::post('/purchase-ledger/store', [PurchaseLedgerController::class, 'store'])->name('purchase_ledger.store');
    Route::get('/purchase-ledger/edit/{id}', [PurchaseLedgerController::class, 'edit'])->name('purchase_ledger.edit');
    Route::post('/purchase-ledger/update', [PurchaseLedgerController::class, 'update'])->name('purchase_ledger.update');
    Route::delete('/purchase-ledger/delete/{id}', [PurchaseLedgerController::class, 'destroy'])->name('purchase_ledger.destroy');
    Route::post('/purchase-ledger/status-change', [PurchaseLedgerController::class, 'statusChange'])->name('purchase_ledger.status-change');

    Route::get('/exp/list', [ExpenseController::class, 'index'])->name('exp.list');
    Route::post('/exp/get-data', [ExpenseController::class, 'getData'])->name('exp.getData');
    Route::get('/exp/create', [ExpenseController::class, 'create'])->name('exp.create');
    Route::post('/exp/store', [ExpenseController::class, 'store'])->name('exp.store');
    Route::get('/exp/edit/{id}', [ExpenseController::class, 'edit'])->name('exp.edit');
    Route::post('/exp/update', [ExpenseController::class, 'update'])->name('exp.update');

    Route::prefix('demand-order')->name('demand-order.')->group(function () {
        Route::get('/list', [DemandOrderController::class, 'index'])->name('list');
        Route::post('/get-data', [DemandOrderController::class, 'getData'])->name('getData');
        Route::get('/create', [DemandOrderController::class, 'create'])->name('create');

        Route::get('/step-1', [DemandOrderController::class, 'step1'])->name('step1');
        Route::post('/step-1', [DemandOrderController::class, 'postStep1'])->name('post-step1');
        Route::get('/step-2', [DemandOrderController::class, 'step2'])->name('step2');
        Route::post('/step-2', [DemandOrderController::class, 'postStep2'])->name('post-step2');
        Route::get('/step-3', [DemandOrderController::class, 'step3'])->name('step3');
        Route::post('/step-3', [DemandOrderController::class, 'postStep3'])->name('post-step3');
        Route::get('/step-4', [DemandOrderController::class, 'step4'])->name('step4');
        Route::post('/step-4', [DemandOrderController::class, 'postStep4'])->name('post-step4');

        Route::post('/store', [DemandOrderController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [DemandOrderController::class, 'edit'])->name('edit');   // note: you used 'demand-orders.edit' earlier; keep consistent
        Route::get('/create-pre', [DemandOrderController::class, 'createPrediction'])->name('create.pre');
        Route::get('/view/{id}', [DemandOrderController::class, 'view'])->name('view');
    });

    // Product Import Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/import', [ProductImportController::class, 'showUploadForm'])->name('import');
        Route::post('/upload', [ProductImportController::class, 'import'])->name('upload');
        Route::get('/mapping/{filename}', [ProductImportController::class, 'showMappingForm'])->name('mapping');
        Route::post('/process-import', [ProductImportController::class, 'processImport'])->name('process');
        Route::get('/add-stocks', [ProductImportController::class, 'addStocks'])->name('add-stocks');
        Route::post('/import-stocks', [ProductImportController::class, 'importStocks'])->name('import.stocks');
    });

    Route::get('/shift-manage/list', [ShiftManageController::class, 'index'])->name('shift-manage.list');
    Route::post('/shift-manage/get-data', [ShiftManageController::class, 'getShiftClosingsData'])->name('shift-manage.getData');
    Route::post('shift-manage/invoices-by-branch', [ShiftManageController::class, 'getInvoicesByBranch'])->name('shift-manage.invoices-by-branch');
    Route::post('shift-manage/close-shift/{id}', [ShiftManageController::class, 'closeShift'])->name('shift-manage.close-shift');
    Route::get('/shift-manage/{id}', [ShiftManageController::class, 'showPhoto'])->name('shift-manage.photo');
    Route::get('/shift-manage/view/{id}/{shift_id}', [ShiftManageController::class, 'view'])->name('shift-manage.view');
    Route::get('/shift-manage/stock-details/{id}', [ShiftManageController::class, 'stockDetails'])->name('shift-manage.stock-details');
    Route::get('/shift-manage/print-shift/{id}', [ShiftManageController::class, 'printShift'])->name('purchase.print-shift');

    Route::post('/holidays', [HolidayController::class, 'store'])
        ->name('holidays.store');

    // routes/web.php
    Route::get('credit/credit-ledger', [CreditHistoryController::class, 'index'])->name('credit.credit-ledger');
    Route::post('credit/credit-ledger-data', [CreditHistoryController::class, 'creditLedgerData'])->name('credit.credit-ledger-data');

    Route::prefix('reports/')->group(function () {
        Route::get('', [ReportController::class, 'index'])->name('reports.list');
        Route::get('low-stock', [ReportController::class, 'lowLevel'])->name('reports.low_stock.page');
        Route::post('low-stock/data', [ReportController::class, 'getLowLevelData'])->name('reports.low_stock.data');
        Route::get('expiry',            [ReportController::class, 'expiryPage'])->name('reports.expiry.page');
        Route::post('expiry/get-data',   [ReportController::class, 'getExpiryData'])->name('reports.expiry.get_data');
        Route::get('profit-loss1',          [ReportController::class, 'profitLossPage'])->name('reports.pl.page');
        Route::post('profit-loss/get-data', [ReportController::class, 'getProfitLossData'])->name('reports.pl.get_data');
        Route::get('product-pl',          [ReportController::class, 'productPLPage'])->name('reports.product_pl.page');
        Route::post('product-pl/get-data', [ReportController::class, 'getProductPLData'])->name('reports.product_pl.get_data');
        Route::get('daily-cash',          [ReportController::class, 'dailyCashPage'])->name('reports.daily_cash.page');
        Route::post('daily-cash/get-data', [ReportController::class, 'getDailyCashData'])->name('reports.daily_cash.get_data');
        Route::get('credit-payments',          [ReportController::class, 'creditPaymentsPage'])->name('reports.credit_payments.page');
        Route::post('credit-payments/get-data', [ReportController::class, 'getCreditPaymentsData'])->name('reports.credit_payments.get_data');
        Route::get('category-sales',          [ReportController::class, 'categorySalesPage'])->name('reports.category_sales.page');
        Route::post('category-sales/get-data', [ReportController::class, 'getCategorySalesData'])->name('reports.category_sales.get_data');
        Route::get('discounts',          [ReportController::class, 'discountOfferPage'])->name('reports.discounts.page');
        Route::post('discounts/get-data', [ReportController::class, 'getDiscountOfferData'])->name('reports.discounts.get_data');
        Route::get('expenses',          [ReportController::class, 'expensesPage'])->name('reports.expenses.page');
        Route::post('expenses/get-data', [ReportController::class, 'getExpensesData'])->name('reports.expenses.get_data');
        Route::get('vendor-purchases',          [ReportController::class, 'vendorPurchasesPage'])->name('reports.vendor_purchases.page');
        Route::post('vendor-purchases/get-data', [ReportController::class, 'getVendorPurchasesData'])->name('reports.vendor_purchases.get_data');

        Route::get('customer-outstanding',          [ReportController::class, 'customerOutstandingPage'])->name('reports.customer_outstanding.page');
        Route::post('customer-outstanding/get-data', [ReportController::class, 'getCustomerOutstandingData'])->name('reports.customer_outstanding.get_data');

        Route::get('profit-loss',  [Report2Controller::class, 'profitLoss'])->name('reports.pnl_tally.view');
        Route::post('getProfitLossData', [Report2Controller::class, 'getProfitLossData'])->name('reports.pnl_tally.data');
        Route::get('/reports/profit-loss/pdf', [Report2Controller::class, 'profitLossPdf'])->name('reports.profit-loss.pdf');

        Route::get('product-wise',  [Report2Controller::class, 'productWise'])->name('reports.discount.product.view');
        Route::post('product-wise-data', [Report2Controller::class, 'getProductWiseData'])->name('reports.discount.product.data');
        Route::get('end-day-summary',  [Report2Controller::class, 'endDaySummary'])->name('reports.day_end.view');
        Route::post('end-day-summary-data', [Report2Controller::class, 'getEndDaySummaryData'])->name('reports.day_end.data');
        Route::get('best-selling-product',  [Report2Controller::class, 'bestSellingProducts'])->name('reports.best_selling.view');
        Route::post('get-best-selling-product-data', [Report2Controller::class, 'getBestSellingProductsData'])->name('reports.best_selling.data');
        Route::get('worst-selling-product',  [Report2Controller::class, 'worstSellingProducts'])->name('reports.worst_selling.view');
        Route::post('get-worst-selling-product-data', [Report2Controller::class, 'getWorstSellingProductsData'])->name('reports.worst_selling.data');
        Route::get('not-sale',  [Report2Controller::class, 'notSale'])->name('reports.not_sold.view');
        Route::post('not-sale-data', [Report2Controller::class, 'getNotSaleData'])->name('reports.not_sold.data');
        Route::get('stock-transfer',  [Report2Controller::class, 'stockTransfer'])->name('reports.stock_transfer.view');
        Route::post('get-stock-transfer-data', [Report2Controller::class, 'getStockTransferData'])->name('reports.stock_transfer.data');
        Route::get('purchase-report',  [Report2Controller::class, 'purchaseReport'])->name('reports.purchase.view');
        Route::post('get-purchase-report-data', [Report2Controller::class, 'getPurchaseReportData'])->name('reports.purchase.data');
        Route::get('purchase-by-product-report',  [Report2Controller::class, 'purchaseByProductReport'])->name('reports.purchase_by_product.view');
        Route::post('get-purchase-by-product-report-data', [Report2Controller::class, 'getPurchaseByProductReportData'])->name('reports.purchase_by_product.data');
        Route::get('closing-summary',  [Report2Controller::class, 'closingSummary'])->name('reports.closing_summary.view');
        Route::post('get-closing-summary-data', [Report2Controller::class, 'getClosingSummaryData'])->name('reports.closing_summary.data');
        Route::get('profit-on-sales-invoice',  [Report2Controller::class, 'profitOnSalesInvoice'])->name('reports.profit_invoice.view');
        Route::post('get-profit-on-sales-invoice-data', [Report2Controller::class, 'getProfitOnSalesInvoiceData'])->name('reports.profit_invoice.data');
        Route::get('product-inactive',  [Report2Controller::class, 'productInactive'])->name('reports.product_inactive.view');
        Route::post('get-product-inactive-data', [Report2Controller::class, 'getProductInactiveData'])->name('reports.product_inactive.data');

        Route::get('balance-sheet',        [Report2Controller::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::post('balance-sheet/data',   [Report2Controller::class, 'getBalanceSheetData'])->name('reports.balance-sheet.data');

        Route::get('pnl-drilldown/group/{group}',  [Report2Controller::class, 'group'])
            ->name('reports.pnl.group');

        Route::get('pnl-drilldown/ledger/{ledger}', [Report2Controller::class, 'ledger'])
            ->name('reports.pnl.ledger');

        Route::get('pnl/group',  [Report2Controller::class, 'pnlGroupDetail'])
            ->name('reports.pnl.group');

        Route::get('pnl/ledger', [Report2Controller::class, 'pnlLedgerDetail'])
            ->name('reports.pnl.ledger');
    });

    // routes/web.php
    Route::prefix('accounting')->name('accounting.')->middleware(['auth'])->group(function () {

        Route::get('/groups/list', [GroupController::class, 'index'])->name('groups.list');
        Route::post('/groups/get-data', [GroupController::class, 'getData'])->name('groups.getData');
        Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::post('/groups/store', [GroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/edit/{id}', [GroupController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/update', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/delete/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
        Route::get('/groups/children/{group}', [GroupController::class, 'children'])->name('groups.children');
        // Route::get('/ledgers/{vendor}', [LedgerController::class, 'getPurchaseLedgers'])
        // ->name('ledgers.products');

        Route::get('/ledgers/list', [LedgerController::class, 'index'])->name('ledgers.list');
        Route::post('/ledgers/get-data', [LedgerController::class, 'getData'])->name('ledgers.getData');
        Route::get('/ledgers/create', [LedgerController::class, 'create'])->name('ledgers.create');
        Route::post('/ledgers/store', [LedgerController::class, 'store'])->name('ledgers.store');
        Route::get('/ledgers/edit/{id}', [LedgerController::class, 'edit'])->name('ledgers.edit');
        Route::put('/ledgers/update', [LedgerController::class, 'update'])->name('ledgers.update');
        Route::delete('/ledgers/delete/{id}', [LedgerController::class, 'destroy'])->name('ledgers.destroy');

        Route::get('vouchers',        [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
        Route::post('vouchers/store',       [VoucherController::class, 'store'])->name('vouchers.store');
        Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy'); // optional
        Route::post('vouchers/get-data', [VoucherController::class, 'getData'])->name('vouchers.getData');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('roles/{role}/permissions',  [RolePermissionController::class, 'edit'])->name('roles.permissions.edit');
        Route::post('roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');
    });
});



require __DIR__ . '/auth.php';
