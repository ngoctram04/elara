<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
use App\Http\Controllers\Frontend\PointController;
use App\Http\Controllers\Frontend\RefundController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\Frontend\ProductQuestionController;
use App\Http\Controllers\Frontend\ChatController;

use App\Models\Order;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\Mail;

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
use App\Services\AI\GeminiService;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StockImportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ProductQuestionController as AdminProductQuestionController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

/*
|--------------------------------------------------------------------------
| FRONTEND – PUBLIC
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/policy', function () {
    return view('frontend.pages.policy');
})->name('policy');

// Shop
Route::get('/products', [ShopController::class, 'index'])->name('shop');

// Search autocomplete
Route::get('/search/suggest', [ShopController::class, 'suggest'])->name('search.suggest');
Route::get('/search/history', [ShopController::class, 'history']);
Route::post('/search/history/delete', [ShopController::class, 'deleteHistory']);

// Category
Route::get('/category/{slug}', [FrontendCategoryController::class, 'show'])->name('category.show');

// Product detail
Route::get('/product/{slug}', [FrontendProductController::class, 'show'])->name('products.show');

/*
|--------------------------------------------------------------------------
| BLOG - TIN TỨC
|--------------------------------------------------------------------------
*/
Route::get('/blogs', [FrontendBlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [FrontendBlogController::class, 'show'])->name('blogs.show');

/*
|--------------------------------------------------------------------------
| VNPAY RETURN (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/vnpay-return', [CheckoutController::class, 'vnpayReturn'])->name('vnpay.return');

/*
|--------------------------------------------------------------------------
| CART (KHÔNG CẦN LOGIN)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {

    Route::get('/', [CartController::class, 'index'])->name('index');

    Route::post('/add', [CartController::class, 'add'])->name('add');

    Route::post('/change-qty', [CartController::class, 'changeQty'])->name('changeQty');

    Route::post('/change-variant', [CartController::class, 'changeVariant'])->name('changeVariant');

    Route::delete('/remove/{variantId}', [CartController::class, 'remove'])->name('remove');

    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');

    Route::post('/apply-promotion', [CartController::class, 'applyPromotion'])->name('applyPromotion');
});

Route::post('/cart/apply-best-promotion', [CartController::class, 'applyBestPromotion'])
    ->name('cart.applyBestPromotion');

Route::post('/cart/get-available-promotions', [CartController::class, 'getAvailablePromotions'])
    ->name('cart.getAvailablePromotions');

/*
|--------------------------------------------------------------------------
| AI CHAT (PUBLIC - KHÔNG CẦN LOGIN)
|--------------------------------------------------------------------------
| SỬA QUAN TRỌNG:
| Bỏ middleware auth/check_active để khách chưa đăng nhập vẫn chat được
*/
Route::prefix('ai-chat')->name('chat.ai.')->group(function () {

    Route::get('/', [ChatController::class, 'aiChat'])->name('index');

    Route::post('/send', [ChatController::class, 'sendAI'])->name('send');
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

        Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buyNow');

        Route::post('/from-cart', [CheckoutController::class, 'fromCart'])->name('fromCart');

        Route::get('/', [CheckoutController::class, 'index'])->name('index');

        Route::post('/', [CheckoutController::class, 'store'])->name('store');

        Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShippingAjax'])
            ->name('calculateShipping');

        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');

        Route::post('/cancel/{id}', [CheckoutController::class, 'cancel'])->name('cancel');
    });

/*
|--------------------------------------------------------------------------
| ADDRESS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/profile/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::post('/profile/addresses/{id}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
    Route::delete('/profile/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
});

/*
|--------------------------------------------------------------------------
| POINTS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/points/history', [PointController::class, 'history'])->name('points.history');

    Route::get('/points/redeem', [PointController::class, 'redeemPage'])->name('points.redeem.page');

    Route::post('/points/redeem', [PointController::class, 'redeem'])->name('points.redeem');
});

/*
|--------------------------------------------------------------------------
| REFUND (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])->prefix('refund')->name('refund.')->group(function () {
    Route::get('/create/{order}', [RefundController::class, 'create'])->name('create');
    Route::post('/store', [RefundController::class, 'store'])->name('store');
    Route::get('/{id}', [OrderController::class, 'showRefund'])->name('show');
});

/*
|--------------------------------------------------------------------------
| ORDER HISTORY (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('orders')
    ->name('orders.')
    ->group(function () {

        Route::get('/', [OrderController::class, 'index'])->name('history');

        Route::get('/{id}', [OrderController::class, 'show'])->name('show');

        Route::put('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');

        Route::post('/{id}/reorder', [OrderController::class, 'reorder'])->name('reorder');

        Route::post('/{id}/confirm-received', [OrderController::class, 'confirmReceived'])
            ->name('confirmReceived');
    });

/*
|--------------------------------------------------------------------------
| WISHLIST (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('wishlist')
    ->name('wishlist.')
    ->group(function () {

        Route::get('/', [WishlistController::class, 'index'])->name('index');

        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
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

        Route::get('/', [ProfileController::class, 'edit'])->name('index');

        Route::patch('/', [ProfileController::class, 'update'])->name('update');

        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');

        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');

        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');

        Route::get('/address', fn() => view('frontend.profile.address'))->name('address');
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
        |--------------------------------------------------------------------------
        | REPORTS - THỐNG KÊ
        |--------------------------------------------------------------------------
        */
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');

        Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/customers/{customer}/orders', [ReportController::class, 'customerOrders'])
            ->name('reports.customerOrders');
        Route::get('/reports/slow-products', [ReportController::class, 'slowProducts'])->name('reports.slowProducts');
        Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.lowStock');
        Route::get('/reports/wishlist', [ReportController::class, 'wishlist'])->name('reports.wishlist');
        Route::get('reports/cancel-orders', [ReportController::class, 'cancelOrders'])->name('reports.cancelOrders');
        Route::get('/reports/refund-orders', [ReportController::class, 'refundOrders'])->name('reports.refundOrders');

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | CUSTOMERS
        |--------------------------------------------------------------------------
        */
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/{user}/toggle-status', [CustomerController::class, 'toggleStatus'])
            ->name('customers.toggle-status');

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */
        Route::resource('categories', CategoryController::class)->except(['update']);
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

        /*
        |--------------------------------------------------------------------------
        | BRANDS
        |--------------------------------------------------------------------------
        */
        Route::resource('brands', BrandController::class)->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');

        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminOrderController::class, 'show'])->name('show');
            Route::post('/update-status/{id}', [AdminOrderController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/cancel/{id}', [AdminOrderController::class, 'cancel'])->name('cancel');
        });

        /*
        |--------------------------------------------------------------------------
        | REFUNDS
        |--------------------------------------------------------------------------
        */
        Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{id}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{id}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{id}/refunded', [AdminRefundController::class, 'refunded'])->name('refunds.refunded');

        /*
        |--------------------------------------------------------------------------
        | PROMOTIONS
        |--------------------------------------------------------------------------
        */
        Route::prefix('promotions')->name('promotions.')->group(function () {

            Route::get('/', [PromotionController::class, 'index'])->name('index');

            Route::get('/create', [PromotionController::class, 'chooseType'])->name('create');
            Route::get('/create-product', [PromotionController::class, 'createProduct'])->name('createProduct');
            Route::get('/create-order', [PromotionController::class, 'createOrder'])->name('createOrder');
            Route::get('/create-reward', [PromotionController::class, 'createReward'])->name('createReward');

            Route::post('/', [PromotionController::class, 'store'])->name('store');
            Route::post('/store-reward', [PromotionController::class, 'storeReward'])->name('storeReward');

            Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])->name('edit');
            Route::put('/{promotion}', [PromotionController::class, 'update'])->name('update');
            Route::patch('/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('toggle');
            Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->name('destroy');

            Route::get('/reward/{reward}/edit', [PromotionController::class, 'editReward'])->name('editReward');
            Route::put('/reward/{reward}', [PromotionController::class, 'updateReward'])->name('updateReward');
            Route::patch('/reward/{reward}/toggle', [PromotionController::class, 'toggleReward'])->name('toggleReward');
        });

        /*
        |--------------------------------------------------------------------------
        | STOCK IMPORT
        |--------------------------------------------------------------------------
        */
        Route::get('/stock-import', [StockImportController::class, 'create'])->name('stock.create');
        Route::post('/stock-import', [StockImportController::class, 'store'])->name('stock.store');
        Route::get('/stock-import/history', [StockImportController::class, 'history'])->name('stock.history');
        Route::get('/stock-import/{code}', [StockImportController::class, 'show'])->name('stock.show');
        Route::get('/stock-import/{code}/pdf', [StockImportController::class, 'exportPdf'])->name('stock.exportPdf');
        Route::get('/stock-import/suppliers/search', [StockImportController::class, 'searchSuppliers'])
            ->name('stock.suppliers.search');
    Route::get('/stock-import/variant/{variant}/suggest-price', [StockImportController::class, 'suggestPrice'])
    ->name('stock.suggestPrice');

        /*
        |--------------------------------------------------------------------------
        | INVENTORY
        |--------------------------------------------------------------------------
        */
        Route::get('/inventory/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low');
        Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
        Route::get('/inventory/near-expiry', [InventoryController::class, 'nearExpiry'])->name('inventory.near_expiry');

        /*
        |--------------------------------------------------------------------------
        | BLOG
        |--------------------------------------------------------------------------
        */
        Route::resource('blogs', BlogController::class);
        Route::post('/blogs/{id}/toggle', [BlogController::class, 'toggle'])->name('blogs.toggle');
        Route::post('/blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.uploadImage');

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER SUPPORT - Q&A
        |--------------------------------------------------------------------------
        */
        Route::get('/questions', [AdminProductQuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions/answer', [AdminProductQuestionController::class, 'answer'])->name('questions.answer');

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER SUPPORT - CHAT
        |--------------------------------------------------------------------------
        */
        Route::get('/messages', [AdminChatController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [AdminChatController::class, 'show'])->name('messages.show');
        Route::post('/messages/{id}', [AdminChatController::class, 'send'])->name('messages.send');

        /*
        |--------------------------------------------------------------------------
        | REVIEWS
        |--------------------------------------------------------------------------
        */
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [AdminReviewController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminReviewController::class, 'show'])->name('show');
            Route::post('/{id}/toggle', [AdminReviewController::class, 'toggleVisibility'])->name('toggle');
            Route::post('/{id}/reply', [AdminReviewController::class, 'reply'])->name('reply');
        });
    });

/*
|--------------------------------------------------------------------------
| PRODUCT QUESTIONS (Q&A)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('questions')
    ->name('questions.')
    ->group(function () {

        Route::post('/store', [ProductQuestionController::class, 'store'])->name('store');
        Route::post('/answer', [ProductQuestionController::class, 'answer'])->name('answer');
        Route::delete('/question/{id}', [ProductQuestionController::class, 'deleteQuestion'])->name('deleteQuestion');
        Route::delete('/answer/{id}', [ProductQuestionController::class, 'deleteAnswer'])->name('deleteAnswer');
    });

/*
|--------------------------------------------------------------------------
| REVIEWS (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('reviews')
    ->name('reviews.')
    ->group(function () {

        Route::get('/order/{order}', [ReviewController::class, 'create'])->name('create');
        Route::post('/order/{order}', [ReviewController::class, 'store'])->name('store');
    });

/*
|--------------------------------------------------------------------------
| CHAT WITH STAFF (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('chat')
    ->name('chat.')
    ->group(function () {

        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/messages', [ChatController::class, 'messages'])->name('messages');
        Route::post('/send', [ChatController::class, 'send'])->name('send');
        Route::get('/unread-count', [ChatController::class, 'unreadCount'])->name('unreadCount');
    });

/*
|--------------------------------------------------------------------------
| TEST MAIL - ORDER COMPLETED
|--------------------------------------------------------------------------
*/
Route::get('/test-order-completed', function () {

    $order = Order::with('user')->first();

    if (!$order) {
        return 'Không có đơn hàng trong database';
    }

    if (!$order->user || !$order->user->email) {
        return 'Đơn hàng không có user hoặc email';
    }

    Mail::to($order->user->email)->send(new OrderCompletedMail($order));

    return 'Order Completed Mail Sent';
});

/*
|--------------------------------------------------------------------------
| TEST MAIL - ORDER CREATED (KHÁCH + ADMIN)
|--------------------------------------------------------------------------
*/
Route::get('/test-order-created', function () {

    $order = Order::with('user')->first();

    if (!$order) {
        return 'Không có đơn hàng trong database';
    }

    if (!$order->user || !$order->user->email) {
        return 'Đơn hàng không có user hoặc email';
    }

    Mail::to($order->user->email)->send(new OrderCreatedMail($order));

    $adminEmail = config('mail.from.address');

    if ($adminEmail) {
        Mail::to($adminEmail)->send(new OrderCreatedMail($order, true));
    }

    return 'Order Created Mail Sent (Customer + Admin)';
});

/*
|--------------------------------------------------------------------------
| NOTIFICATIONS
|--------------------------------------------------------------------------
*/
Route::post('/notifications/mark-all-read', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    abort_unless($user, 401);

    $user->unreadNotifications->markAsRead();

    return response()->json(['success' => true]);
})->middleware('auth')->name('notifications.markAllRead');

Route::get('/notification/{id}', function (\Illuminate\Http\Request $request, $id) {
    $user = $request->user();
    abort_unless($user, 401);

    $noti = $user->notifications()->findOrFail($id);
    $noti->markAsRead();

    return redirect($noti->data['url'] ?? route('home'));
})->middleware('auth')->name('notification.redirect');
/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';