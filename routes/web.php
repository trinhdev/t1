<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\FileController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Hi_FPT\AppController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UnitsController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\ProductUnitsController;
use App\Http\Controllers\Admin\StockTransactionController;


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


Auth::routes();
Route::group(['middleware' => ['auth'], 'prefix'=>'admin'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::prefix('home')->group(function () {
        Route::get('/', 'HomeController@index')->name('home');
        Route::get('/getDataChart', 'HomeController@getDataChart')->name('home.getDataChart');
        Route::get('/getPaymentErrorTableData', 'HomeController@getPaymentErrorTableData')->name('home.getPaymentErrorTableData');
    });
    Route::prefix('file')->controller(FileController::class)->group(function () {
        Route::any('/uploadImageExternal', 'uploadImageExternal')->name('uploadImageExternalB');
        Route::post('/importPhone', 'importPhone')->name('file.importPhone');

    });
    Route::namespace('Admin')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('/', 'SettingsController@index')->name('settings.index');
            Route::get('/2', 'SettingsController@index2')->name('settings.index2');
            Route::get('/edit/{id}', 'SettingsController@edit')->name('settings.edit');
            Route::get('/create', 'SettingsController@create')->name('settings.create');
            Route::post('/store', 'SettingsController@store')->name('settings.store');
            Route::put('/update/{id}', 'SettingsController@update')->name('settings.update');
            Route::post('/destroy', 'SettingsController@destroy')->name('settings.destroy');
            Route::get('/initDatatable', 'SettingsController@initDatatable')->name('settings.initDatatable');
        });

        Route::prefix('setting')->group(function () {
            Route::get('/', 'GeneralSettingsController@index')->name('general_settings.index');
            Route::post('/edit', 'GeneralSettingsController@postEdit')->name('general_settings.edit');
            Route::post('/saveUriSetting', 'GeneralSettingsController@saveUriSetting')->name('general_settings.saveUriSetting');
            Route::post('/sendMailManually', 'GeneralSettingsController@sendMailManually')->name('general_settings.sendMailManually');
        });


        Route::prefix('user')->group(function () {
            Route::get('/', 'UserController@index')->name('user.index');
            Route::get('/edit/{id}', 'UserController@edit')->name('user.edit');
            Route::get('/create', 'UserController@create')->name('user.create');
            Route::post('/store', 'UserController@store')->name('user.store');
            Route::post('/login', 'UserController@login')->name('user.login');
            Route::put('/update/{id}', 'UserController@update')->name('user.update');
            Route::post('/destroy', 'UserController@destroy')->name('user.destroy');
        });

        Route::prefix('products')->group(function () {
            Route::get('/import-view', [ProductsController::class, 'importView'])->name('products.import.view');
            Route::post('/import', [ProductsController::class, 'import'])->name('products.import');
            Route::get('/', 'ProductsController@index')->name('products.index');
            Route::get('/edit/{id}', 'ProductsController@edit')->name('products.edit');
            Route::get('/create', 'ProductsController@create')->name('products.create');
            Route::post('/store', 'ProductsController@store')->name('products.store');
            Route::post('/login', 'ProductsController@login')->name('products.login');
            Route::put('/update/{id}', 'ProductsController@update')->name('products.update');
            Route::post('/destroy', 'ProductsController@destroy')->name('products.destroy');
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', 'CategoriesController@index')->name('categories.index');
            Route::get('/edit/{id}', 'CategoriesController@edit')->name('categories.edit');
            Route::get('/create', 'CategoriesController@create')->name('categories.create');
            Route::post('/show', 'CategoriesController@show')->name('categories.show');
            Route::post('/store', 'CategoriesController@store')->name('categories.store');
            Route::post('/login', 'CategoriesController@login')->name('categories.login');
            Route::put('/update/{id}', 'CategoriesController@update')->name('categories.update');
            Route::post('/destroy', 'CategoriesController@destroy')->name('categories.destroy');
        });

        Route::prefix('categories-parent')->group(function () {
            Route::get('/', 'CategoriesParentController@index')->name('categoriesparent.index');
            Route::get('/edit/{id}', 'CategoriesParentController@edit')->name('categoriesparent.edit');
            Route::post('/show', 'CategoriesParentController@show')->name('categoriesparent.show');
            Route::get('/create', 'CategoriesParentController@create')->name('categoriesparent.create');
            Route::post('/store', 'CategoriesParentController@store')->name('categoriesparent.store');
            Route::put('/update/{id}', 'CategoriesParentController@update')->name('categoriesparent.update');
            Route::post('/destroy', 'CategoriesParentController@destroy')->name('categoriesparent.destroy');
            Route::post('/change-status', 'CategoriesParentController@changeStatus')->name('categoriesparent.change-status');
            Route::get('/initDatatable', 'CategoriesParentController@initDatatable')->name('categoriesparent.initDatatable');
        });

        Route::prefix('brand')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('brand.index');
            Route::get('/import-view', [BrandController::class, 'importView'])->name('brand.import.view');
            Route::post('/import', [BrandController::class, 'import'])->name('brand.import');
            Route::get('/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
            Route::post('/show', [BrandController::class, 'show'])->name('brand.show');
            Route::get('/create', [BrandController::class, 'create'])->name('brand.create');
            Route::post('/store', [BrandController::class, 'store'])->name('brand.store');
            Route::put('/update/{id}', [BrandController::class, 'update'])->name('brand.update');
            Route::post('/destroy', [BrandController::class, 'destroy'])->name('brand.destroy');
            Route::post('/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
            Route::get('/initDatatable', [BrandController::class, 'initDatatable'])->name('brand.initDatatable');
        });

        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomersController::class, 'index'])->name('customers.index');
            Route::get('/import-view', [CustomersController::class, 'importView'])->name('customers.import.view');
            Route::post('/import', [CustomersController::class, 'import'])->name('customers.import');
            Route::get('/edit/{id}', [CustomersController::class, 'edit'])->name('customers.edit');
            Route::post('/show', [CustomersController::class, 'show'])->name('customers.show');
            Route::get('/create', [CustomersController::class, 'create'])->name('customers.create');
            Route::post('/store', [CustomersController::class, 'store'])->name('customers.store');
            Route::put('/update/{id}', [CustomersController::class, 'update'])->name('customers.update');
            Route::post('/destroy', [CustomersController::class, 'destroy'])->name('customers.destroy');
            Route::post('/change-status', [CustomersController::class, 'changeStatus'])->name('customers.change-status');
            Route::get('/initDatatable', [CustomersController::class, 'initDatatable'])->name('customers.initDatatable');
        });

        Route::prefix('units')->group(function () {
            Route::get('/', [UnitsController::class, 'index'])->name('units.index');
            Route::get('/import-view', [UnitsController::class, 'importView'])->name('units.import.view');
            Route::post('/import', [UnitsController::class, 'import'])->name('units.import');
            Route::get('/edit/{id}', [UnitsController::class, 'edit'])->name('units.edit');
            Route::post('/show', [UnitsController::class, 'show'])->name('units.show');
            Route::get('/create', [UnitsController::class, 'create'])->name('units.create');
            Route::post('/store', [UnitsController::class, 'store'])->name('units.store');
            Route::put('/update/{id}', [UnitsController::class, 'update'])->name('units.update');
            Route::post('/destroy', [UnitsController::class, 'destroy'])->name('units.destroy');
            Route::post('/change-status', [UnitsController::class, 'changeStatus'])->name('units.change-status');
            Route::get('/initDatatable', [UnitsController::class, 'initDatatable'])->name('units.initDatatable');
        });
        
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/import-view', [OrderController::class, 'importView'])->name('orders.import.view');
            Route::post('/import', [OrderController::class, 'import'])->name('orders.import');
            Route::get('/edit/{id}', [OrderController::class, 'edit'])->name('orders.edit');
            Route::post('/show', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('/store', [OrderController::class, 'store'])->name('orders.store');
            Route::put('/update/{id}', [OrderController::class, 'update'])->name('orders.update');
            Route::post('/destroy', [OrderController::class, 'destroy'])->name('orders.destroy');
            Route::post('/orders/update-status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

            Route::get('/initDatatable', [OrderController::class, 'initDatatable'])->name('orders.initDatatable');
        });

        Route::prefix('product-units')->group(function () {
            Route::get('/', [ProductUnitsController::class, 'index'])->name('product-units.index');
            Route::get('/import-view', [ProductUnitsController::class, 'importView'])->name('product-units.import.view');
            Route::post('/import', [ProductUnitsController::class, 'import'])->name('product-units.import');
            Route::get('/edit/{id}', [ProductUnitsController::class, 'edit'])->name('product-units.edit');
            Route::post('/show', [ProductUnitsController::class, 'show'])->name('product-units.show');
            Route::get('/create', [ProductUnitsController::class, 'create'])->name('product-units.create');
            Route::post('/store', [ProductUnitsController::class, 'store'])->name('product-units.store');
            Route::put('/update/{id}', [ProductUnitsController::class, 'update'])->name('product-units.update');
            Route::post('/destroy', [ProductUnitsController::class, 'destroy'])->name('product-units.destroy');
            Route::post('/change-status', [ProductUnitsController::class, 'changeStatus'])->name('product-units.change-status');
            Route::get('/initDatatable', [ProductUnitsController::class, 'initDatatable'])->name('product-units.initDatatable');
        });

        Route::prefix('comment')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('comment.index');
            Route::post('/show', [CommentController::class, 'show'])->name('comment.show');
            Route::post('/destroy', [CommentController::class, 'destroy'])->name('comment.destroy');
            Route::post('/change-status', [CommentController::class, 'changeStatus'])->name('comment.change-status');
            Route::get('/initDatatable', [CommentController::class, 'initDatatable'])->name('comment.initDatatable');
        });

        Route::prefix('stock-transaction')->group(function () {
            Route::get('/', [StockTransactionController::class, 'index'])->name('stock-transaction.index');
            Route::get('/import-view', [StockTransactionController::class, 'importView'])->name('stock-transaction.import.view');
            Route::post('/import-excel', [StockTransactionController::class, 'importExcel'])->name('stock-transaction.import.excel');
            Route::post('/storeImport', [StockTransactionController::class, 'storeImport'])->name('stock-transaction.storeImport');
            Route::post('/storeExport', [StockTransactionController::class, 'storeExport'])->name('stock-transaction.storeExport');
            Route::get('/get-product-units/{productId}', [StockTransactionController::class, 'getProductUnits']);
            Route::post('/show', [StockTransactionController::class, 'show'])->name('stock-transaction.show');
            Route::get('/import', [StockTransactionController::class, 'import'])->name('stock-transaction.import');
            Route::get('/list', [StockTransactionController::class, 'list'])->name('stock-transaction.list');
            Route::get('/export', [StockTransactionController::class, 'export'])->name('stock-transaction.export');
            Route::get('/initDatatable', [StockTransactionController::class, 'initDatatable'])->name('comment.initDatatable');
        });

        Route::prefix('vouchers')->group(function () {
            Route::get('/', [VoucherController::class, 'index'])->name('vouchers.index');
            Route::get('/edit/{id}', [VoucherController::class, 'edit'])->name('vouchers.edit');
            Route::post('/show', [VoucherController::class, 'show'])->name('vouchers.show');
            Route::get('/create', [VoucherController::class, 'create'])->name('vouchers.create');
            Route::post('/store', [VoucherController::class, 'store'])->name('vouchers.store');
            Route::put('/update/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
            Route::post('/destroy', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
            Route::post('/change-status', [VoucherController::class, 'changeStatus'])->name('vouchers.change-status');
            Route::get('/initDatatable', [VoucherController::class, 'initDatatable'])->name('vouchers.initDatatable');
        });

        Route::prefix('warehouse')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('warehouse.index');
            Route::get('/import-view', [WarehouseController::class, 'importView'])->name('warehouse.import.view');
            Route::post('/import', [WarehouseController::class, 'import'])->name('warehouse.import');
            Route::get('/edit/{id}', [WarehouseController::class, 'edit'])->name('warehouse.edit');
            Route::post('/show', [WarehouseController::class, 'show'])->name('warehouse.show');
            Route::get('/create', [WarehouseController::class, 'create'])->name('warehouse.create');
            Route::post('/store', [WarehouseController::class, 'store'])->name('warehouse.store');
            Route::put('/update/{id}', [WarehouseController::class, 'update'])->name('warehouse.update');
            Route::post('/destroy', [WarehouseController::class, 'destroy'])->name('warehouse.destroy');
            Route::post('/change-status', [WarehouseController::class, 'changeStatus'])->name('warehouse.change-status');
            Route::get('/initDatatable', [WarehouseController::class, 'initDatatable'])->name('warehouse.initDatatable');
        });

        Route::prefix('stock')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('stock.index');
            Route::get('/edit/{id}', [StockController::class, 'edit'])->name('stock.edit');
            Route::post('/show', [StockController::class, 'show'])->name('stock.show');
            Route::get('/create', [StockController::class, 'create'])->name('stock.create');
            Route::post('/store', [StockController::class, 'store'])->name('stock.store');
            Route::put('/update/{id}', [StockController::class, 'update'])->name('stock.update');
            Route::post('/destroy', [StockController::class, 'destroy'])->name('stock.destroy');
            Route::post('/change-status', [StockController::class, 'changeStatus'])->name('stock.change-status');
            Route::get('/initDatatable', [StockController::class, 'initDatatable'])->name('stock.initDatatable');
        });

        Route::prefix('supplier')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('supplier.index');
            Route::get('/import-view', [SupplierController::class, 'importView'])->name('supplier.import.view');
            Route::post('/import', [SupplierController::class, 'import'])->name('supplier.import');
            Route::get('/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
            Route::post('/show', [SupplierController::class, 'show'])->name('supplier.show');
            Route::get('/create', [SupplierController::class, 'create'])->name('supplier.create');
            Route::post('/store', [SupplierController::class, 'store'])->name('supplier.store');
            Route::put('/update/{id}', [SupplierController::class, 'update'])->name('supplier.update');
            Route::post('/destroy', [SupplierController::class, 'destroy'])->name('supplier.destroy');
            Route::post('/change-status', [SupplierController::class, 'changeStatus'])->name('supplier.change-status');
            Route::get('/initDatatable', [SupplierController::class, 'initDatatable'])->name('supplier.initDatatable');
        });

        Route::prefix('groups')->group(function () {
            Route::get('/', 'GroupsController@index')->name('groups.index');
            Route::get('/edit/{id?}', 'GroupsController@edit')->name('groups.edit');
            Route::get('/create', 'GroupsController@create')->name('groups.create');
            Route::post('/save', 'GroupsController@save')->name('groups.store');
            Route::post('/destroy', 'GroupsController@destroy')->name('groups.destroy');
            Route::get('/getList', 'GroupsController@getList')->name('groups.getList');
        });
        Route::prefix('modules')->group(function () {
            Route::get('/', 'ModulesController@index')->name('modules.index');
            Route::get('/edit/{id}', 'ModulesController@edit')->name('modules.edit');
            Route::post('/show', 'ModulesController@show')->name('modules.show');
            Route::get('/create', 'ModulesController@create')->name('modules.create');
            Route::post('/store', 'ModulesController@store')->name('modules.store');
            Route::post('/update/{id}', 'ModulesController@update')->name('modules.update');
            Route::post('/destroy', 'ModulesController@destroy')->name('modules.destroy');
            Route::get('/initDatatable', 'ModulesController@initDatatable')->name('modules.initDatatable');
        });

        Route::prefix('groupmodule')->group(function () {
            Route::get('/', 'GroupmoduleController@index')->name('groupmodule.index');
            Route::get('/edit/{id}', 'GroupmoduleController@edit')->name('groupmodule.edit');
            Route::get('/create', 'GroupmoduleController@create')->name('groupmodule.create');
            Route::post('/store', 'GroupmoduleController@store')->name('groupmodule.store');
            Route::put('/update/{id}', 'GroupmoduleController@update')->name('groupmodule.update');
            Route::post('/destroy', 'GroupmoduleController@destroy')->name('groupmodule.destroy');
            Route::get('/initDatatable', 'GroupmoduleController@initDatatable')->name('groupmodule.initDatatable');
        });
        Route::prefix('roles')->group(function () {
            Route::get('/', 'RolesController@index')->name('roles.index');
            Route::get('/edit/{id?}', 'RolesController@edit')->name('roles.edit');
            Route::get('/create', 'RolesController@create')->name('roles.create');
            Route::post('/save', 'RolesController@save')->name('roles.save');
            Route::post('/destroy', 'RolesController@destroy')->name('roles.destroy');
            Route::get('/getList', 'RolesController@getList')->name('roles.getList');

        });

        Route::prefix('role')->group(function () {
            Route::get('/', 'RoleController@index')->name('role.index');
            Route::get('/edit/{id?}', 'RoleController@edit')->name('role.edit');
            Route::post('/update/{id?}', 'RoleController@update')->name('role.update');
            Route::get('/create', 'RoleController@create')->name('role.create');
            Route::post('/store', 'RoleController@store')->name('role.store');
            Route::post('/destroy', 'RoleController@destroy')->name('role.destroy');
        });

        Route::prefix('logactivities')->group(function () {
            Route::get('/', 'LogactivitiesController@index')->name('logactivities.index');
            Route::post('/clearLog', 'LogactivitiesController@clearLog')->name('logactivities.clearLog');
            Route::delete('/destroy/{id}', 'LogactivitiesController@destroy')->name('logactivities.destroy');
            Route::get('/initDatatable', 'LogactivitiesController@initDatatable')->name('logactivities.initDatatable');
        });

    });
    
    Route::prefix('profile')->group(function () {
        Route::post('/changePassword', 'ProfileController@changePassword')->name('profile.changePassword');
        Route::post('/updateprofile', 'ProfileController@updateprofile')->name('profile.updateprofile');
    });

   
    
}
);
