<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

/*
|--------------------------------------------------------------------------
| FRONTEND – USER
|--------------------------------------------------------------------------
*/

// Trang chủ (ai cũng vào được)
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// Trang shop – yêu cầu đăng nhập
Route::get('/shop', [ShopController::class, 'index'])
    ->middleware('auth')
    ->name('shop');


/*
|--------------------------------------------------------------------------
| BACKEND – ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {

        /*
        |-------------------------
        | Dashboard
        |-------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |-------------------------
        | Admin Profile (RIÊNG)
        |-------------------------
        */
        Route::get('/profile', [AdminProfileController::class, 'show'])
            ->name('profile.show');

        Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        /*
        |-------------------------
        | (Sau này mở rộng)
        |-------------------------
        */
        // Route::resource('users', UserController::class);
        // Route::resource('products', ProductController::class);
    });


/*
|--------------------------------------------------------------------------
| PROFILE – USER (GIỮ LẠI)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| AUTH ROUTES (LOGIN / REGISTER / LOGOUT)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';