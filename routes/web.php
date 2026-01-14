<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

// Frontend
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;

// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;

// User profile (Laravel default)
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| FRONTEND – USER
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/shop', [ShopController::class, 'index'])
    ->middleware('auth')
    ->name('shop');

/*
|--------------------------------------------------------------------------
| BACKEND – ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {

        /*
        | Dashboard
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        | Admin Profile
        */
        Route::get('/profile', [AdminProfileController::class, 'show'])
            ->name('profile.show');

        Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        /*
        | Category Management
        */
        Route::resource('categories', CategoryController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        /*
        | Brand Management
        */
        Route::resource('brands', BrandController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ]);

        /*
        | Product Management
        | ✔ index, create, store
        | ✔ show  → 👁 xem chi tiết (có biến thể)
        | ✔ edit  → ✏️ chỉnh sửa (có biến thể)
        | ✔ update, destroy
        */
        Route::resource('products', ProductController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);
    });

/*
|--------------------------------------------------------------------------
| PROFILE – USER (DEFAULT)
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
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';