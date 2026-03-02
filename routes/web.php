<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FRONTEND CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CategoryController as FrontendCategoryController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\AddressController;
/*
|--------------------------------------------------------------------------
| USER PROFILE
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StockImportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
/*
|--------------------------------------------------------------------------
| FRONTEND – PUBLIC
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
// Shop
Route::get('/products', [ShopController::class, 'index'])->name('shop');
// Search autocomplete
Route::get('/search/suggest', [ShopController::class, 'suggest'])
    ->name('search.suggest');
Route::get('/search/history', [ShopController::class, 'history']);
Route::post('/search/history/delete', [ShopController::class, 'deleteHistory']);
// Category
Route::get('/category/{slug}', [FrontendCategoryController::class, 'show'])
    ->name('category.show');

// Product detail
Route::get('/product/{slug}', [FrontendProductController::class, 'show'])
    ->name('products.show');


/*
|--------------------------------------------------------------------------
| VNPAY RETURN (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/vnpay-return', [CheckoutController::class, 'vnpayReturn'])
    ->name('vnpay.return');


/*
|--------------------------------------------------------------------------
| CART (KHÔNG CẦN LOGIN)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {

    Route::get('/', [CartController::class, 'index'])->name('index');

    Route::post('/add', [CartController::class, 'add'])->name('add');

    Route::post('/change-qty', [CartController::class, 'changeQty'])
    ->name('changeQty');

    Route::post('/change-variant', [CartController::class, 'changeVariant'])
    ->name('changeVariant');

    Route::delete('/remove/{variantId}', [CartController::class, 'remove'])
    ->name('remove');

    Route::delete('/clear', [CartController::class, 'clear'])
    ->name('clear');

    // ⭐ THÊM Ở ĐÂY
    Route::post('/apply-promotion', [CartController::class, 'applyPromotion'])
    ->name('applyPromotion');
});


/*
|--------------------------------------------------------------------------
| CHECKOUT (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
->prefix('checkout')
->name('checkout.')
->group(function () {

    Route::post('/buy-now', [CheckoutController::class, 'buyNow'])
        ->name('buyNow');

    // ⭐ THÊM DÒNG NÀY
    Route::post('/from-cart', [CheckoutController::class, 'fromCart'])
    ->name('fromCart');

    Route::get('/', [CheckoutController::class, 'index'])
    ->name('index');

    Route::post('/', [CheckoutController::class, 'store'])
    ->name('store');
    Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShippingAjax'])
    ->name('calculateShipping');

    Route::get('/success/{order}', [CheckoutController::class, 'success'])
    ->name('success');

    Route::post('/cancel/{id}', [CheckoutController::class, 'cancel'])
    ->name('cancel');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/profile/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::post('/profile/addresses/{id}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
    Route::delete('/profile/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])
    ->name('addresses.update');
    Route::get('/membership', [ProfileController::class, 'membership'])
    ->name('membership');
});

/*
|--------------------------------------------------------------------------
| ORDER HISTORY (LOGIN)  ⭐ QUAN TRỌNG
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'check_active'])
->prefix('orders')
->name('orders.')
->group(function () {

    // Danh sách đơn (orders.history)
    Route::get('/', [OrderController::class, 'index'])
    ->name('history');

    // Chi tiết đơn
    Route::get('/{id}', [OrderController::class, 'show'])
    ->name('show');

    // Huỷ đơn
    Route::put('/{id}/cancel', [OrderController::class, 'cancel'])
    ->name('cancel');

    // Mua lại
    Route::post('/{id}/reorder', [OrderController::class, 'reorder'])
        ->name('reorder');
        
});

/*
|------------------------------------------------------------------
| WISHLIST (LOGIN)
|------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
->prefix('wishlist')
    ->name('wishlist.')
    ->group(function () {

        Route::get('/', [WishlistController::class, 'index'])
        ->name('index');

        // BỎ {product}
        Route::post('/toggle', [WishlistController::class, 'toggle'])
        ->name('toggle');
    });
/*
|--------------------------------------------------------------------------
| USER PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {

        Route::get('/', [ProfileController::class, 'edit'])
            ->name('index');

        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');

        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('avatar');

        Route::post('/password', [ProfileController::class, 'updatePassword'])
            ->name('password');

        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');

        Route::get('/address', fn() => view('frontend.profile.address'))
            ->name('address');
    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
->name('admin.')
->middleware(['auth', 'check_active', 'is_admin'])
->group(function () {

    /*
        |--------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------
        */
    /*
|--------------------------------------------------
| REPORTS - THỐNG KÊ
|--------------------------------------------------
*/

    // Dashboard
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    // Xuất PDF
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])
        ->name('reports.exportPdf');


    // ===== Trang chi tiết báo cáo =====

    // Top sản phẩm
    Route::get('/reports/products', [ReportController::class, 'products'])
    ->name('reports.products');

    // Top khách hàng
    Route::get('/reports/customers', [ReportController::class, 'customers'])
        ->name('reports.customers');

    // Sản phẩm tồn lâu
    Route::get('/reports/slow-products', [ReportController::class, 'slowProducts'])
        ->name('reports.slowProducts');

    // Sản phẩm sắp hết hàng
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])
        ->name('reports.lowStock');

    // ⭐ Sản phẩm được yêu thích
    Route::get('/reports/wishlist', [ReportController::class, 'wishlist'])
        ->name('reports.wishlist');

    
    /*
        |--------------------------------------------------
        | PROFILE
        |--------------------------------------------------
        */
    Route::get('/profile', [AdminProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
    ->name('profile.edit');

    Route::put('/profile', [AdminProfileController::class, 'update'])
    ->name('profile.update');

    /*
        |--------------------------------------------------
        | CUSTOMERS
        |--------------------------------------------------
        */
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/{user}', [CustomerController::class, 'show'])
    ->name('customers.show');

    Route::post(
        '/customers/{user}/toggle-status',
        [CustomerController::class, 'toggleStatus']
    )->name('customers.toggle-status');

    /*
        |--------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------
        */
    Route::resource('categories', CategoryController::class)
        ->except(['update']);

    Route::put(
        'categories/{category}',
        [CategoryController::class, 'update']
    )->name('categories.update');

    /*
        |--------------------------------------------------
        | BRANDS
        |--------------------------------------------------
        */
    Route::resource('brands', BrandController::class)
    ->except(['show']);

    /*
        |--------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------
        */
    Route::resource('products', ProductController::class);

    /*
        |--------------------------------------------------
        | ORDERS  🔥 QUẢN LÝ ĐƠN HÀNG
        |--------------------------------------------------
        */
    Route::prefix('orders')->name('orders.')->group(function () {

        // Danh sách đơn
        Route::get('/', [AdminOrderController::class, 'index'])
        ->name('index');

        // Chi tiết đơn
        Route::get('/{id}', [AdminOrderController::class,
            'show'
        ])
        ->name('show');

        // Cập nhật trạng thái
        Route::post('/update-status/{id}', [AdminOrderController::class, 'updateStatus'])
        ->name('updateStatus');

        // ===== THÊM DÒNG NÀY =====
        Route::post('/cancel/{id}', [AdminOrderController::class, 'cancel'])
        ->name('cancel');
    });

    /*
|--------------------------------------------------
| PROMOTIONS
|--------------------------------------------------
*/
    Route::prefix('promotions')->name('promotions.')->group(function () {

        // List
        Route::get('/', [PromotionController::class, 'index'])
        ->name('index');

        // ⭐ Trang chọn loại (thay cho create)
        Route::get('/create', [PromotionController::class, 'chooseType'])
        ->name('create');

        // Form tạo theo loại
        Route::get('/create-product', [PromotionController::class, 'createProduct'])
        ->name('createProduct');

        Route::get('/create-order', [PromotionController::class, 'createOrder'])
        ->name('createOrder');

        // Store
        Route::post('/', [PromotionController::class, 'store'])
        ->name('store');

        // Edit
        Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])
        ->name('edit');

        Route::put('/{promotion}', [PromotionController::class, 'update'])
        ->name('update');

        // Toggle active
        Route::patch('/{promotion}/toggle', [PromotionController::class, 'toggle'])
        ->name('toggle');

        // (Nếu có destroy)
        Route::delete('/{promotion}', [PromotionController::class, 'destroy'])
        ->name('destroy');
    });

    /*
        |--------------------------------------------------
        | STOCK IMPORT
        |--------------------------------------------------
        */
    Route::get('/stock-import', [StockImportController::class, 'create'])
    ->name('stock.create');

    Route::post('/stock-import', [StockImportController::class, 'store'])
    ->name('stock.store');

    Route::get('/stock-import/history', [StockImportController::class, 'history'])
    ->name('stock.history');
});
/*
|------------------------------------------------------------------
| REVIEWS (LOGIN)
|------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
->prefix('reviews')
->name('reviews.')
->group(function () {

    Route::get('/{orderItem}', [ReviewController::class, 'create'])
    ->name('create');

    Route::post('/{orderItem}', [ReviewController::class, 'store'])
    ->name('store');

});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';