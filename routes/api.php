<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\WorldController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Api\WorldEntryController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 旧ログインボーナスAPI（非推奨）
// Route::post('/login-bonus', [LoginController::class, 'processLoginBonus']);

// 認証不要のルート
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthLoginController::class, 'login']);

// 認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthLoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ワールド関連のルート
    Route::get('/world', [WorldController::class, 'index']);
    Route::post('/world', [WorldController::class, 'store']);
    Route::delete('/world/{world}', [WorldController::class, 'destroy']);
    Route::post('/world/{world}/select', [WorldController::class, 'select']);

    // ワールドエントリー関連のルート
    Route::post('/world-entry', [WorldEntryController::class, 'store']);
    Route::get('/world-entry/today', [WorldEntryController::class, 'getTodayEntry']);
    Route::get('/world-entry/weekly-bonus', [WorldEntryController::class, 'weeklyBonus']);
});
