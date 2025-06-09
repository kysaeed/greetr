<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\SettingsController;

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

// ログイン済みユーザーのみアクセス可能なルート
Route::middleware(['auth'])->group(function () {
    // トップページ（Phaserゲーム）
    Route::get('/', function () {
        return view('game');
    })->name('home');

    Route::get('/world', [WorldController::class, 'index'])->name('world');
    Route::post('/world', [WorldController::class, 'store'])->name('world.store');
    Route::delete('/world/{world}', [WorldController::class, 'destroy'])->name('world.destroy');
    Route::post('/world/{world}/select', [WorldController::class, 'select'])->name('world.select');
    Route::get('/setting', [SettingsController::class, 'index'])->name('setting');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// 未ログインユーザーのみアクセス可能なルート
Route::middleware(['guest'])->group(function () {
    // ユーザー登録ページ
    Route::get('/register', function () {
        return view('register');
    })->name('register');

    // ログインページ
    Route::get('/login', function () {
        return view('login');
    })->name('login');
});

// その他のページは404
Route::get('/{any}', function () {
    abort(404);
})->where('any', '.*');
