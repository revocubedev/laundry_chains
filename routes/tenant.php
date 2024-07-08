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
        //staff aka users
        Route::post('/switch-user', [App\Http\Controllers\UserController::class, "switchUser"]);
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
    });
});
