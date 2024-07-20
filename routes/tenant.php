<?php

declare(strict_types=1);

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
        Route::get('reports-trail', [App\Http\Controllers\ReportsController::class, 'generateItemReport']);
        Route::get('reports-sales', [App\Http\Controllers\ReportsController::class, 'salesReport']);
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

        // routes
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

        // delivery options
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

        // product_groups
        Route::get('/product_groups', [App\Http\Controllers\ProductController::class, "get_product_groups"]);
        Route::post('/product_group/add', [App\Http\Controllers\ProductController::class, "add_product_group"])->middleware("staff.permission:create-product");
        Route::post('/product_group/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit_product_group"])->middleware("staff.permission:create-product");
        Route::get('/product_group/single/{uuid}', [App\Http\Controllers\ProductController::class, "getSingleGroup"]);
        Route::delete('/product_group/{uuid}', [App\Http\Controllers\ProductController::class, "delete_product_group"])->middleware("staff.permission:create-product");

        // products
        Route::get('/products', [App\Http\Controllers\ProductController::class, "index"]);
        Route::get('/products/all', [App\Http\Controllers\ProductController::class, "all"]);
        Route::get('/product/view/{uuid}', [App\Http\Controllers\ProductController::class, "getProduct"]);
        Route::post('/product/create', [App\Http\Controllers\ProductController::class, "create"])->middleware("staff.permission:create-product");
        Route::post('/product/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit"])->middleware("staff.permission:create-product");
        Route::delete('/product/delete/{uuid}', [App\Http\Controllers\ProductController::class, "destroy"])->middleware("staff.permission:create-product");

        // product_options
        Route::get('/products_option', [App\Http\Controllers\ProductController::class, "getAllProductOption"]);
        Route::get('/product_options/single/{product_id}', [App\Http\Controllers\ProductController::class, "get_product_options"]);
        Route::post('/product_option/add', [App\Http\Controllers\ProductController::class, "add_product_option"])->middleware("staff.permission:create-product");
        Route::post('/product_option/edit/{uuid}', [App\Http\Controllers\ProductController::class, "edit_product_option"])->middleware("staff.permission:create-product");
        Route::delete('/product_option/delete/{uuid}', [App\Http\Controllers\ProductController::class, "delete_product_option"])->middleware("staff.permission:create-product");

        // transaction
        Route::get('/transactions', [App\Http\Controllers\TransactionController::class, "index"]);
        Route::get('/transaction/view/{tag_id}', [App\Http\Controllers\TransactionController::class, "view"]);
        Route::post('/transaction/edit', [App\Http\Controllers\TransactionController::class, "edit"]);
        Route::post('/transaction/delete', [App\Http\Controllers\TransactionController::class, "delete"]);

        // outbound
        Route::post('/outbound/{item_id}', [App\Http\Controllers\OutboundController::class, "handleOrderItems"]);

        // track items
        Route::get('/track/{tagId}', [App\Http\Controllers\TransactionController::class, "trackItem"]);

        // customers
        Route::post('/customer/merge', [App\Http\Controllers\CustomerController::class, "mergeCustomer"]);
        Route::post('/customer/create', [App\Http\Controllers\CustomerController::class, "create"])->middleware("staff.permission:add-customer");
        Route::post('/customer/edit/{uuid}', [App\Http\Controllers\CustomerController::class, "edit"]);
        Route::post('/customer/wallet/{uuid}', [App\Http\Controllers\CustomerController::class, "addBalToWallet"])->middleware("staff.permission:fund-wallet");
        Route::get('/customer/view/{uuid}', [App\Http\Controllers\CustomerController::class, "detail"]);
        Route::delete('/customer/delete/{uuid}', [App\Http\Controllers\CustomerController::class, "delete"])->middleware("staff.permission:delete-customer");
        Route::get('/customer/all', [App\Http\Controllers\CustomerController::class, "getAllCustomers"]);
        Route::get('/customer/export', [App\Http\Controllers\CustomerController::class, "exportCustomers"]);
        Route::post('/customer/bulk-upload', [App\Http\Controllers\CustomerController::class, "uploadCustomer"]);

        // reports 
        Route::get('/report/locations', [App\Http\Controllers\ReportsController::class, "getTotalNumbers"]);
        Route::get('/report/departments', [App\Http\Controllers\ReportsController::class, "getGarmentsNumbers"]);
        Route::get('/report/orders/department', [App\Http\Controllers\ReportsController::class, "totalGarmentsDepartment"]);
        // Route::get('/report/rewash', [App\Http\Controllers\ReportsController::class, "ItemsInReclean"]);
        // Route::get('/report/rejected', [App\Http\Controllers\ReportsController::class, "ItemsInDamaged"]);
        Route::get('/report/individual', [App\Http\Controllers\ReportsController::class, "ItemsByIndividual"]);
        Route::get('/report/orders', [App\Http\Controllers\ReportsController::class, "totalLeftFactory"]);

        // dashboard
        Route::get('/order/dashboard-clone', [App\Http\Controllers\OrderController::class, "clone"])->middleware("staff.permission:view-dashboard");

        // metrics & anayltics
        Route::get('/reportsPerHour', [App\Http\Controllers\TransactionController::class, "ordersPerHour"]);
        Route::get('/metrics', [App\Http\Controllers\TransactionController::class, "ordersCompare"]);
        Route::get('/overview-numbers', [App\Http\Controllers\MetricsController::class, "overviewNumbers"])->middleware("staff.permission:view-dashboard");
        Route::get('/overview', [App\Http\Controllers\MetricsController::class, "dueOrders"]);
        Route::get('/revenue', [App\Http\Controllers\MetricsController::class, "revenuePage"]);
        Route::get('/unpaid', [App\Http\Controllers\MetricsController::class, "unpaidPage"]);
        Route::get('/order-metric', [App\Http\Controllers\MetricsController::class, "orderPage"]);
        Route::get('/customer-metric', [App\Http\Controllers\MetricsController::class, "customersPage"]);
        Route::get('/customer-details', [App\Http\Controllers\MetricsController::class, "customersDetails"]);
        Route::get('/cleaning', [App\Http\Controllers\MetricsController::class, "cleaningPage"]);
        Route::get('/order-finance', [App\Http\Controllers\MetricsController::class, "orderFinances"]);
        Route::get('/wallet-funding', [App\Http\Controllers\MetricsController::class, "walletFunding"]);

        // miscelle
        Route::post('/discount-types', [App\Http\Controllers\OrderController::class, "createDiscountType"]);
        Route::patch('/discount-types', [App\Http\Controllers\OrderController::class, "editDiscountType"]);
        Route::get('/discount-types', [App\Http\Controllers\OrderController::class, "getDiscountTypes"]);
        Route::post('/charge', [App\Http\Controllers\OrderController::class, "createCharge"]);
        Route::patch('/charge', [App\Http\Controllers\OrderController::class, "editCharge"]);
        Route::get('/charge', [App\Http\Controllers\OrderController::class, "getCharges"]);

        // orders
        Route::post('/order/generate-movement-list', [App\Http\Controllers\OrderController::class, "createMovementList"]);
        Route::get('/order/get-movement-lists', [App\Http\Controllers\OrderController::class, "getMovementList"]);
        Route::get('/order/get-single-list', [App\Http\Controllers\OrderController::class, "getSingleMovementList"]);
        Route::post('/order/create', [App\Http\Controllers\OrderController::class, "createOrder"]);
        Route::get('/order', [App\Http\Controllers\OrderController::class, "show"]);
        Route::post('/order/edit-item', [App\Http\Controllers\OrderController::class, "editOrderItem"]);
        Route::get('/get-order/{id}', [App\Http\Controllers\OrderController::class, "getByOrder"]);
        Route::get('/order-by-id/{id}', [App\Http\Controllers\OrderController::class, "getByOrderID"]);
        Route::get('/all_order', [App\Http\Controllers\OrderController::class, "index"]);
        Route::post('/order/edit/{id}', [App\Http\Controllers\OrderController::class, "edit"])->middleware("staff.permission:edit-order");
        Route::delete('/order/delete/{id}', [App\Http\Controllers\OrderController::class, "delete"])->middleware("staff.permission:delete-order");
        Route::get('/order/customer', [App\Http\Controllers\OrderController::class, "getCustomerOrders"]);
        Route::post('/order/preorder', [App\Http\Controllers\OrderController::class, "createPreOrder"]);
        Route::post('/order/make-payment', [App\Http\Controllers\OrderController::class, "markOrderPaid"]);
        Route::post('/order/invoice', [App\Http\Controllers\OrderController::class, "createInvoice"]);
        Route::patch('/order/invoice', [App\Http\Controllers\OrderController::class, "editInvoice"]);
        Route::delete('/order/invoice', [App\Http\Controllers\OrderController::class, "deleteInvoice"]);
        Route::get('/order/invoice', [App\Http\Controllers\OrderController::class, "allInvoice"]);
        Route::post('/order/invoice/mark', [App\Http\Controllers\OrderController::class, "markAsPaid"]);
        Route::post('/order/rack/add/{id}', [App\Http\Controllers\OrderController::class, "addRack"]);
        Route::get('/order/generate-code/{order_id}', [App\Http\Controllers\ItemController::class, "generateOrderCode"]);
        Route::post('/update-scan', [App\Http\Controllers\OrderController::class, "updateByScan"]);
    });
});
