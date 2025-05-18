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
})->name('login');
Route::post('/shift-close/store', [ShiftClosingController::class, 'store'])->name('shift-close.store');
Route::post('/shift-close/withdraw', [ShiftClosingController::class, 'withdraw'])->name('shift-close.withdraw');


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
    Route::post('/cash-in-hand', [CashInHandController::class, 'store'])->name('cash-in-hand')->middleware('auth');

    Route::get('/roles/list', [RolesController::class, 'index'])->name('roles.list');
    Route::post('/roles/get-data', [RolesController::class, 'getData'])->name('roles.getData');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/edit/{id}', [RolesController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/update', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');

    Route::get('/store/list', [BranchController::class, 'index'])->name('branch.list');
    Route::post('/store/get-data', [BranchController::class, 'getData'])->name('user.getData');
    Route::get('/store/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/store/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/store/edit/{id}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/store/update', [BranchController::class, 'update'])->name('branch.update');
    Route::post('/store/delete', [BranchController::class, 'destroy'])->name('branch.destroy');


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
    Route::post('/stock-requests/{id}/approve', [StockController::class, 'approve'])
    ->name('stock-requests.approve');
    Route::get('/stock-requests/popup-details/{id}', [StockController::class, 'stockShow'])->name('stock.popupDetails');

    Route::get('/stock/edit/{id}', [StockController::class, 'edit'])->name('stock.edit');
    Route::get('/stock/add-warehouse', [StockController::class, 'addWarehouse'])->name('addWarehouse');
    Route::post('/stock/store-warehouse', [StockController::class, 'storeWarehouse'])->name('stock.warehouse');
    Route::get('/stock/send-request-list', [StockController::class, 'showSendRequest'])->name('stock.requestSendList');
    Route::post('/stock/get-send-request-data', [StockController::class, 'getSendRequestData'])->name('stock.getSendRequestData');
  
    // Route::get('/stock/send-store-request-list', [StockController::class, 'showStoreSendRequest'])->name('stock.requestStoreSendList');
    // Route::post('/stock/get-send-store-request-data', [StockController::class, 'getStoreSendRequestData'])->name('stock.getSendStoreRequestData');
   

    Route::get('/products/subcategory/{category_id}', [ProductController::class, 'getSubcategories'])->name('get.subcategories');
    Route::get('/products/getpacksize/{category_id}', [ProductController::class, 'getPackSize'])->name('get.getpacksize');
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
    // Route::get('/stock/list', [InventoryController::class, 'index'])->name('inventories.list');
    // Route::post('/inventories/get-data', [InventoryController::class, 'getData'])->name('inventories.getData');
    
    
    Route::get('/items/list', [ItemController::class, 'index'])->name('items.list');
    Route::get('/items/cart', [ItemController::class, 'cart'])->name('items.cart');
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
    Route::get('/view-invoice/{invoice}', [InvoiceController::class, 'viewInvoice'])->name('invoice.view-invoice');
    

    Route::get('/pack-size/list', [PackSizeController::class, 'index'])->name('packsize.list');
    Route::post('/pack-size/get-data', [PackSizeController::class, 'getData'])->name('packsize.getData');
    Route::get('/pack-size/create', [PackSizeController::class, 'create'])->name('packsize.create');
    Route::post('/pack-size/store', [PackSizeController::class, 'store'])->name('packsize.store');
    Route::get('/pack-size/edit/{id}', [PackSizeController::class, 'edit'])->name('packsize.edit');
    Route::post('/pack-size/update', [PackSizeController::class, 'update'])->name('packsize.update');
    Route::delete('/pack-size/delete/{id}', [PackSizeController::class, 'destroy'])->name('packsize.destroy');

});

// Route::middleware(['auth', 'admin'])->prefix('commission-users')->name('commission-users.')->group(function () {
  
    Route::get('/commission-users/list', [CommissionUserController::class, 'index'])->name('commission-users.list');
    Route::post('/commission-users/get-data', [CommissionUserController::class, 'getData'])->name('commission-users.getData');
    Route::get('/commission-users/create', [CommissionUserController::class, 'create'])->name('commission-users.create');
    Route::post('/commission-users/', [CommissionUserController::class, 'store'])->name('commission-users.store');
    Route::get('/commission-users/edit/{id}', [CommissionUserController::class, 'edit'])->name('commission-users.edit');
    Route::put('/commission-users/{Commissionuser}', [CommissionUserController::class, 'update'])->name('commission-users.update');
    Route::delete('/commission-users/{Commissionuser}', [CommissionUserController::class, 'destroy'])->name('commission-users.destroy');
    Route::get('/commission-cust/view/{id}', [CommissionUserController::class, 'view'])->name('commission-cust.view');
    Route::get('/commission-cust/trasaction-photo-view/{id}', [CommissionUserController::class, 'custTrasactionPhoto'])->name('commission-cust.trasaction-photo-view');
    Route::post('/commission-cust/get-commission-data', [CommissionUserController::class, 'getDataCommission'])->name('commission-cust.get.commission.data');
    
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
    Route::get('/cust-product-price-change/form/{id}', [PartyUserController::class, 'custProductPriceChangeForm']);
    Route::post('/cust-product-price-change/price_change-store', [PartyUserController::class, 'custPriceChange'])->name('cust-product-price-change-store');
    Route::post('/party-users/get-commission-data', [PartyUserController::class, 'getDataCommission'])->name('party-users.get.commission.data');
    Route::get('/cust-trasaction-photo/view/{id}', [PartyUserController::class, 'custTrasactionPhoto'])->name('cust-trasaction-photo-view');
    

    // });

    Route::get('/stock-transfer/list', [StockTransferController::class, 'index'])->name('stock-transfer.list');
    Route::post('/stock-transfer/store', [StockTransferController::class, 'store'])->name('stock-transfer.store');

    Route::get('/vendor/list', [VendorListController::class, 'index'])->name('vendor.list');
    Route::post('/vendor/get-data', [VendorListController::class, 'getData'])->name('vendor.getData');
    Route::get('/vendor/create', [VendorListController::class, 'create'])->name('vendor.create');
    Route::post('/vendor/', [VendorListController::class, 'store'])->name('vendor.store');
    Route::get('/vendor/edit/{id}', [VendorListController::class, 'edit'])->name('vendor.edit');
    Route::put('/vendor/{Partyuser}', [VendorListController::class, 'update'])->name('vendor.update');
    Route::delete('/vendor/{Partyuser}', [VendorListController::class, 'destroy'])->name('vendor.destroy');

    Route::get('/purchase/list', [PurchaseController::class, 'index'])->name('purchase.list');
    Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::post('/purchase/get-data', [PurchaseController::class, 'getData'])->name('purchase.getData');
    Route::get('/purchase/view/{id}', [PurchaseController::class, 'view'])->name('purchase.view');
    Route::get('/vendor/get-product-details/{id}', [PurchaseController::class, 'getProductDetails'])->name('vendor.get-product-details');

    Route::get('/popup/form/{type}', [NotificationController::class, 'loadForm']);
    Route::get('/notifications/index', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/fetch-data', [NotificationController::class, 'getData'])->name('notifications.fetch-data');
    Route::get('/notifications/get-notification', [NotificationController::class, 'getNotication'])->name('notifications.get-notication');
    

    // routes/web.php
    Route::get('sales/sales-list', [SalesReportController::class, 'salasList'])->name('sales.sales.list');
    Route::get('sales/sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::get('sales-report/data', [SalesReportController::class, 'getSalesReportData'])->name('sales.report.data');
    Route::post('sales/get-data', [SalesReportController::class, 'getData'])->name('sales.get.data');
    Route::get('/store-sales-summary', [SalesReportController::class, 'storeSummary'])->name('store-sales-summary');

    Route::get('/sales/sales-daily', [SalesReportController::class, 'salesDaily'])->name('sales.sales-daily');
    Route::get('/sales/branch-sales-report', [SalesReportController::class, 'branchSalesReport'])->name('sales.branch.sales.report');
    
    Route::get('/sales/stock-report', [SalesReportController::class, 'stockReport'])->name('sales.stock.report');
    Route::get('/sales/fetch-stock-data', [SalesReportController::class, 'fetchStockData'])->name('sales.fetch-stock-data');
    
    Route::get('/sales/commission-report', [SalesReportController::class, 'commissionReport'])->name('sales.commission.report');
    Route::get('/sales/fetch-commission-data', [SalesReportController::class, 'commissionInvoicesReport'])->name('sales.fetch-commission-data');
    
    Route::get('/exp-category/list', [ExpenseCategoryController::class, 'index'])->name('exp_category.list');
    Route::post('/exp-category/get-data', [ExpenseCategoryController::class, 'getData'])->name('exp_category.getData');
    Route::get('/exp-category/create', [ExpenseCategoryController::class, 'create'])->name('exp_category.create');
    Route::post('/exp-category/store', [ExpenseCategoryController::class, 'store'])->name('exp_category.store');
    Route::get('/exp-category/edit/{id}', [ExpenseCategoryController::class, 'edit'])->name('exp_category.edit');
    Route::post('/exp-category/update', [ExpenseCategoryController::class, 'update'])->name('exp_category.update');
    Route::delete('/exp-category/delete/{id}', [ExpenseCategoryController::class, 'destroy'])->name('exp_category.destroy');

    Route::get('/exp/list', [ExpenseController::class, 'index'])->name('exp.list');
    Route::post('/exp/get-data', [ExpenseController::class, 'getData'])->name('exp.getData');
    Route::get('/exp/create', [ExpenseController::class, 'create'])->name('exp.create');
    Route::post('/exp/store', [ExpenseController::class, 'store'])->name('exp.store');
    Route::get('/exp/edit/{id}', [ExpenseController::class, 'edit'])->name('exp.edit');
    Route::post('/exp/update', [ExpenseController::class, 'update'])->name('exp.update');

    Route::get('/demand-order/list', [DemandOrderController::class, 'index'])->name('demand-order.list');
    Route::post('/demand-order/get-data', [DemandOrderController::class, 'getData'])->name('demand-order.getData');
    Route::get('/demand-order/create', [DemandOrderController::class, 'create'])->name('demand-order.create');
    Route::post('/demand-order/store', [DemandOrderController::class, 'store'])->name('demand-order.store');
    Route::get('/demand-order/edit/{id}', [DemandOrderController::class, 'edit'])->name('demand-orders.edit');
    Route::get('/demand-order/create-pre', [DemandOrderController::class, 'createPrediction'])->name('demand-order.create.pre');
    
    Route::get('/products/import', [ProductImportController::class, 'showUploadForm'])->name('products.import');
    Route::post('/products/upload', [ProductImportController::class, 'import'])->name('products.upload');
    Route::post('/products/import-data', [ProductImportController::class, 'importData'])->name('products.import.data');

    Route::get('/shift-manage/list', [ShiftManageController::class, 'index'])->name('shift-manage.list');
    Route::post('/shift-manage/get-data', [ShiftManageController::class, 'getShiftClosingsData'])->name('shift-manage.getData');
    Route::post('shift-manage/invoices-by-branch', [ShiftManageController::class, 'getInvoicesByBranch'])->name('shift-manage.invoices-by-branch');
    Route::post('shift-manage/close-shift/{id}', [ShiftManageController::class, 'closeShift'])->name('shift-manage.close-shift');


require __DIR__.'/auth.php';
