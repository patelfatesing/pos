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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

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
});

Route::get('/dashboard', function () {
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::post('/cash-in-hand', [CashInHandController::class, 'store'])->middleware('auth');

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
    Route::get('/products/check-barcode', [ProductController::class, 'check'])->name('products.checkbarcode');
    Route::get('/products/pic', [ProductController::class, 'pic'])->name('products.pic');
    Route::post('/products/upload-pic', [ProductController::class, 'uploadPhoto'])->name('products.uploadpic');
    // Route::post('/products/upload-pic', [ProductController::class, 'uploadPhotp'])->name('products.upload');
    Route::get('/products/availability/{id}', [ProductController::class, 'getAvailability']);

    Route::get('/stock/list', [StockController::class, 'index'])->name('stock.list');
    Route::post('/stock/get-data', [StockController::class, 'getData'])->name('stock.getData');
    Route::get('/stock/add', [StockController::class, 'add'])->name('stock.add');
    Route::post('/stock/store', [StockController::class, 'store'])->name('stock.store');
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
    
});
Route::middleware('auth')->prefix('commission-users')->name('commission-users.')->group(function () {
    Route::get('/list', [CommissionUserController::class, 'index'])->name('list');
    Route::post('/get-data', [CommissionUserController::class, 'getData'])->name('getData');
    Route::get('/create', [CommissionUserController::class, 'create'])->name('create');
    Route::post('/', [CommissionUserController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [CommissionUserController::class, 'edit'])->name('edit');
    Route::put('/{Commissionuser}', [CommissionUserController::class, 'update'])->name('update');
    Route::delete('/{Commissionuser}', [CommissionUserController::class, 'destroy'])->name('destroy');
});
Route::middleware('auth')->prefix('party-users')->name('party-users.')->group(function () {
    Route::get('/list', [PartyUserController::class, 'index'])->name('list');
    Route::post('/get-data', [PartyUserController::class, 'getData'])->name('getData');
    Route::get('/create', [PartyUserController::class, 'create'])->name('create');
    Route::post('/', [PartyUserController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PartyUserController::class, 'edit'])->name('edit');
    Route::put('/{Partyuser}', [PartyUserController::class, 'update'])->name('update');
    Route::delete('/{Partyuser}', [PartyUserController::class, 'destroy'])->name('destroy');
});


require __DIR__.'/auth.php';
