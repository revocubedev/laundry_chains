<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('register', [TenantAuthController::class, 'registerTenant']);
});

// Tenant routes
Route::group([
    'prefix' => '/{tenant}',
    'middleware' => InitializeTenancyByPath::class,
], function () {
    Route::group([
        'prefix' => 'auth',
        'controller' => AuthController::class,
    ], function () {
        Route::post('/login', 'login');
        Route::post('/refresh', 'refreshToken');
    });

    Route::group([
        'middleware' => [
            // 'api',
            'check.token'
        ],
        'controller' => UserController::class,
    ], function () {
        Route::post('/switch-user', 'switchUser');
        Route::post('/user/create', 'create');
    });
});
