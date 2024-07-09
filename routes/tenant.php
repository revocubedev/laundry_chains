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

        //staff aka users
        Route::post('/users/switch', [App\Http\Controllers\UserController::class, "switchUser"]);
        Route::get('/users', [App\Http\Controllers\UserController::class, "index"]);
        Route::get('/users/{uuid}', [App\Http\Controllers\UserController::class, "details"]);
        Route::post('/users', [App\Http\Controllers\UserController::class, "create"])->middleware("staff.permission:create-user");
        Route::patch('/users/{uuid}', [App\Http\Controllers\UserController::class, "edit"]);
        Route::delete('/users/{uuid}', [App\Http\Controllers\UserController::class, "delete"]);

        //Routes
        Route::get('/routes', [App\Http\Controllers\RoutesController::class, "index"]);
        Route::post('/routes', [App\Http\Controllers\RoutesController::class, "create"]);
        Route::patch('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "edit"]);
        Route::get('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "show"]);
        Route::delete('/routes/{uuid}', [App\Http\Controllers\RoutesController::class, "delete"]);

        //locations
        Route::get('/locations', [App\Http\Controllers\LocationController::class, "index"]);
        Route::post('/locations', [App\Http\Controllers\LocationController::class, "create"]);
        Route::patch('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "edit"]);
        Route::get('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "show"]);
        Route::delete('/locations/{uuid}', [App\Http\Controllers\LocationController::class, "delete"]);

        //departments
        Route::get(
            '/departments',
            [App\Http\Controllers\DepartmentController::class, "index"]
        );
        Route::post('/departments', [App\Http\Controllers\DepartmentController::class, "create"]);
        Route::get('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "show"]);
        Route::patch('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "edit"]);
        Route::delete('/departments/{uuid}', [App\Http\Controllers\DepartmentController::class, "delete"]);
    });
});
