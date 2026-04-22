<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\Paginator;

use App\Models\Category;
use App\Models\Cart;
use App\Models\Wishlist;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
  
        Paginator::useBootstrapFive();


        View::composer('components.mega-menu', function ($view) {

            $categories = Category::whereNull('parent_id')
                ->with([
                    'children' => function ($q) {
                        $q->withCount('products')
                            ->orderBy('name');
                    }
                ])
                ->orderBy('name')
                ->get();

            $view->with('categories', $categories);
        });

        View::composer('*', function ($view) {

            $cartCount = 0;
            $favorites = [];

            if (Auth::check()) {

                $userId = Auth::id();

        
                $cartCount = Cart::where('user_id', $userId)
                    ->sum('quantity');

              
                $favorites = Wishlist::where('user_id', $userId)
                    ->pluck('product_id')
                    ->map(fn($id) => (int) $id)
                    ->toArray();
            } else {
        
                $cart = Session::get('cart', []);
                $cartCount = collect($cart)->sum('quantity');
            }

            $view->with([
                'cartCount' => $cartCount,
                'favorites' => $favorites
            ]);
        });
    }
}