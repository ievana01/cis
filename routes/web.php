<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::resource('/sales', SalesOrderController::class);
Route::resource('/purchase', PurchaseOrderController::class);
Route::resource('/category', CategoryController::class);
Route::resource('/supplier', SupplierController::class);
Route::resource('/product', ProductController::class);
Route::resource('/warehouse', WarehouseController::class);
Route::resource('/product', ProductController::class);
Route::resource('/customer', CustomerController::class);

Route::get('/get-product-price/{id}', [ProductController::class, 'getPrice']);

Route::post('warehouse/getEditForm', [WarehouseController::class, 'getEditForm'])->name("warehouse.getEditForm");


// Route::post('/sales/add-product', [SalesOrderController::class, 'addProduct'])->name('sales.addProduct');
// Route::post('/sales/shipping-cost', [SalesOrderController::class, 'addShippingCost'])->name('sales.addShippingCost');
// Route::post('/sales/discount', [SalesOrderController::class, 'addDiscount'])->name('sales.addDiscount');
// Route::post('/sales/calculate-total', [SalesOrderController::class, 'calculateTotal'])->name('sales.calculateTotal');

// Route::post('/sales/add-product', [SalesOrderController::class, 'addProduct'])->name('sales.addProduct');
// Route::post('/sales/update-shipping', [SalesOrderController::class, 'updateShipping'])->name('sales.updateShipping');
// Route::post('/sales/update-discount', [SalesOrderController::class, 'updateDiscount'])->name('sales.updateDiscount');

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

Route::post('category/formSubCategory', [CategoryController::class, 'formSubCategory'])->name("category.formSubCategory");
Route::post('category/addSub', [CategoryController::class, 'addSub'])->name('category.addSub');




