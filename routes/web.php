<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMovingController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SeasonDisountController;
use App\Http\Controllers\StoreDataController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

Route::middleware(["auth"])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/laporan-laba', [PurchaseOrderController::class, 'laporanLabaKotor'])->name('laporan-laba');

    Route::resource('/sales', SalesOrderController::class);
    Route::resource('/purchase', PurchaseOrderController::class);
    Route::resource('/category', CategoryController::class);
    Route::resource('/supplier', SupplierController::class);
    Route::resource('/product', ProductController::class);
    Route::resource('/warehouse', WarehouseController::class);
    Route::resource('/product', ProductController::class);
    Route::resource('/customer', CustomerController::class);
    Route::resource('/delivery-note', DeliveryNoteController::class);

    Route::post('warehouse/getEditForm', [WarehouseController::class, 'getEditForm'])->name("warehouse.getEditForm");

    Route::post('/purchase/add-product', [PurchaseOrderController::class, 'addProduct'])->name('purchase.addProduct');
    Route::post('/purchase/calculate-total', [PurchaseOrderController::class, 'calculateTotal'])->name('purchase.calculateTotal');

    Route::get('sales-configuration', [SalesOrderController::class, 'showConfiguration'])->name('sales.configuration');
    Route::get('purchase-configuration', [PurchaseOrderController::class, 'showConfiguration'])->name('purchase.configuration');
    Route::get('inventory-configuration', [ProductController::class, 'showConfiguration'])->name('product.configuration');

    Route::post('/sales-configuration/save', [SalesOrderController::class, 'save'])->name('sales.configuration.save');
    Route::post('/purchase-configuration/save', [PurchaseOrderController::class, 'save'])->name('purchase.configuration.save');
    Route::post('/inventory-configuration/save', [ProductController::class, 'save'])->name('inventory.configuration.save');


    Route::get('/sales-data', [SalesOrderController::class, 'showData'])->name('sales.data');
    Route::get('/purchase-data', [PurchaseOrderController::class, 'showData'])->name('purchase.data');

    Route::post('purchase/paymentForm', [PurchaseOrderController::class, 'paymentForm'])->name("purchase.paymentForm");
    Route::put('purchase/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase.update');

    // Route::post('category/formSubCategory', [CategoryController::class, 'formSubCategory'])->name("category.formSubCategory");
    // Route::post('category/addSub', [CategoryController::class, 'addSub'])->name('category.addSub');

    Route::get('report-stock', [ProductController::class, 'showReportStock'])->name('product.reportstock');

    Route::post('/sales/getCreateCust', [SalesOrderController::class, 'getCreateCust'])->name('sales.getCreateCust');

    Route::resource('/dataStore',  StoreDataController::class);

    Route::post('sales/getNota', [SalesOrderController::class, 'getNota'])->name("sales.getNota");
    Route::get('sales/nota/{id}', [SalesOrderController::class, 'showNota'])->name('sales.showNota');

    Route::post('purchase/getNota', [PurchaseOrderController::class, 'getNota'])->name("purchase.getNota");
    Route::get('purchase/nota/{id}', [PurchaseOrderController::class, 'showNota'])->name('purchase.showNota');

    Route::resource('/productMove', ProductMovingController::class);

    Route::get('/subCategory/{category_id}', [ProductController::class, 'getSubCategory']);

    Route::resource('/subCategory', SubCategoryController::class);
    Route::get('/subCategory/create/{id_category}', [SubCategoryController::class, 'create'])->name('subCategory.create');

    // Route::post('sales/getProduct', [SalesOrderController::class, 'getProduct'])->name("sales.getProduct");
    Route::get('/sales/showProd/{id}', [SalesOrderController::class, 'showProd'])->name('sales.showProd');

    Route::get('/purchase/showProd/{id}', [PurchaseOrderController::class, 'showProd'])->name('purchase.showProd');
    Route::post('/purchase/terima', [DeliveryNoteController::class, 'storeTerima'])->name('delivery-note.storeTerima');
    Route::resource('/pindahProduk', controller: DeliveryNoteController::class);

    Route::get('/get-products/{warehouse_id}', [ProductController::class, 'getProductsByWarehouse']);
    Route::post('/pindah', [DeliveryNoteController::class, 'storePindah'])->name('delivery-note.storePindah');

    Route::post('sales-configuration/getDiskon', [SalesOrderController::class,'getDiskon'])->name("sales.getDiskon");
    Route::post('/hapus-kategori-diskon', [SalesOrderController::class, 'hapusKategoriDiskon'])->name('hapus.kategori.diskon');

    Route::resource('/seasonDiscount', SeasonDisountController::class);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
