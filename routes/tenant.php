<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::group([
    'prefix' => '/{tenant}',
    'middleware' => [
        'api',
        InitializeTenancyByPath::class,
    ],
], function () {
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('login', [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login');
        Route::post('refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
    });

    Route::group([
        'middleware' => 'check.token',
    ], function () {
        // clock in
        Route::get('/users/clockins', [App\Http\Controllers\ClockInController::class, "index"]);
        Route::post('/users/clockin', [App\Http\Controllers\ClockInController::class, "clockin"]);
        Route::post('/users/clockout', [App\Http\Controllers\ClockInController::class, "clockout"]);
        Route::get('/users/clockin_history/{uuid}', [App\Http\Controllers\ClockInController::class, "clockin_history"]);
        Route::get('/users/confirm_clockin/{uuid}', [App\Http\Controllers\ClockInController::class, "verify_clockin"]);

        // staff aka users
        Route::post('/users/switch', [App\Http\Controllers\UserController::class, "switchUser"]);
        Route::get('/users', [App\Http\Controllers\UserController::class, "index"]);
        Route::get('/users/{uuid}', [App\Http\Controllers\UserController::class, "details"]);
        Route::post('/users', [App\Http\Controllers\UserController::class, "create"])->middleware("staff.permission:create-user");
        Route::patch('/users/{uuid}', [App\Http\Controllers\UserController::class, "edit"]);
        Route::delete('/users/{uuid}', [App\Http\Controllers\UserController::class, "delete"]);

        // Routes
        Route::get('/routes', [App\Http\Controllers\RoutesController::class, "index"]);
        Route::post('/routes', [App\Http\Controllers\RoutesController::class, "create"]);
        Route::patch('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "edit"]);
        Route::get('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "show"]);
        Route::delete('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "delete"]);

        // locations
        Route::get('/locations', [App\Http\Controllers\LocationController::class, "index"]);
        Route::post('/locations', [App\Http\Controllers\LocationController::class, "create"]);
        Route::patch('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "edit"]);
        Route::get('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "show"]);
        Route::delete('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "delete"]);

        // departments
        Route::get(
            '/departments',
            [App\Http\Controllers\DepartmentController::class, "index"]
        );
        Route::post('/departments', [App\Http\Controllers\DepartmentController::class, "create"]);
        Route::get('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "show"]);
        Route::patch('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "edit"]);
        Route::delete('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "delete"]);

        // roles
        Route::apiResource('roles', App\Http\Controllers\RoleController::class);

        // Delivery Options
        Route::post('/delivery', [App\Http\Controllers\DeliveryOptionController::class, "create"]);
        Route::patch('/delivery/{uuid}', [App\Http\Controllers\DeliveryOptionController::class, "edit"]);
        Route::get('/delivery/{uuid}', [App\Http\Controllers\DeliveryOptionController::class, "getOne"]);
        Route::get('/delivery', [App\Http\Controllers\DeliveryOptionController::class, "getAll"]);
        Route::delete('/delivery/{uuid}', [App\Http\Controllers\DeliveryOptionController::class, "delete"]);
        Route::post('/order_option', [App\Http\Controllers\DeliveryOptionController::class, "createOrderOption"]);
        Route::get('/order_option', [App\Http\Controllers\DeliveryOptionController::class, "getOrderOption"]);
        Route::patch('/order_option', [App\Http\Controllers\DeliveryOptionController::class, "editOrderOption"]);

        // items
        Route::get('/item/settings', [App\Http\Controllers\ItemController::class, "getItemSetting"]);
        Route::post('/item/settings', [App\Http\Controllers\ItemController::class, "addItemSetting"]);
        Route::patch('/item/settings/color-brand', [App\Http\Controllers\ItemController::class, "addColorBrand"]);
        Route::post('/item/damages', [App\Http\Controllers\ItemController::class, "createDamage"]);
        Route::get('/item/damages', [App\Http\Controllers\ItemController::class, "getDamages"]);
        Route::patch('/item/damages/{uuid}', [App\Http\Controllers\ItemController::class, "editDamage"]);
        Route::delete('/item/damages/{uuid}', [App\Http\Controllers\ItemController::class, "deleteDamage"]);
        Route::post('/item/pattern', [App\Http\Controllers\ItemController::class, "createPattern"]);
        Route::get('/item/pattern', [App\Http\Controllers\ItemController::class, "getPattern"]);
        Route::post('/item/pattern/{uuid}', [App\Http\Controllers\ItemController::class, "editPattern"]);
        Route::delete('/item/pattern/{uuid}', [App\Http\Controllers\ItemController::class, "deletePattern"]);

        // item_history
        Route::post('/item/create_history', [App\Http\Controllers\ItemHistoryController::class, "create_history"]);
        Route::post('/item/batchScan', [App\Http\Controllers\ItemHistoryController::class, "batchScan"]);
        Route::get('/item/getHistory/{transId}', [App\Http\Controllers\ItemHistoryController::class, "get_recent_history"]);
        Route::post('/item/get_all_history', [App\Http\Controllers\ItemHistoryController::class, "get_all_history"]);
        Route::get('/item/report/{transId}', [App\Http\Controllers\ItemHistoryController::class, "export"]);

        //product_groups
        Route::get('/product_groups', [App\Http\Controllers\ProductController::class, "get_product_groups"]);
        Route::post('/product_group/add', [App\Http\Controllers\ProductController::class, "add_product_group"])->middleware("staff.permission:create-product");
        Route::post('/product_group/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit_product_group"])->middleware("staff.permission:create-product");
        Route::get('/product_group/single/{uuid}', [App\Http\Controllers\ProductController::class, "getSingleGroup"]);
        Route::delete('/product_group/{uuid}', [App\Http\Controllers\ProductController::class, "delete_product_group"])->middleware("staff.permission:create-product");

        //products
        Route::get('/products', [App\Http\Controllers\ProductController::class, "index"]);
        Route::get('/products/all', [App\Http\Controllers\ProductController::class, "all"]);
        Route::get('/product/view/{uuid}', [App\Http\Controllers\ProductController::class, "getProduct"]);
        Route::post('/product/create', [App\Http\Controllers\ProductController::class, "create"])->middleware("staff.permission:create-product");
        Route::post('/product/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit"])->middleware("staff.permission:create-product");
        Route::delete('/product/delete/{uuid}', [App\Http\Controllers\ProductController::class, "destroy"])->middleware("staff.permission:create-product");

        //product_options
        Route::get('/products_option', [App\Http\Controllers\ProductController::class, "getAllProductOption"]);
        Route::get('/product_options/single/{product_id}', [App\Http\Controllers\ProductController::class, "get_product_options"]);
        Route::post('/product_option/add', [App\Http\Controllers\ProductController::class, "add_product_option"])->middleware("staff.permission:create-product");
        Route::post('/product_option/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit_product_option"])->middleware("staff.permission:create-product");
        Route::delete('/product_option/delete/{uuid}', [App\Http\Controllers\ProductController::class, "delete_product_option"])->middleware("staff.permission:create-product");

        //transaction
        Route::get('/transactions', [App\Http\Controllers\TransactionController::class, "index"]);
        Route::get('/transaction/view/{tag_id}', [App\Http\Controllers\TransactionController::class, "view"]);
        Route::post('/transaction/edit', [App\Http\Controllers\TransactionController::class, "edit"]);
        Route::post('/transaction/delete', [App\Http\Controllers\TransactionController::class, "delete"]);
    });
});
