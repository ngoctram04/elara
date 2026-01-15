<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', 1)
            ->with(['mainImage', 'variants', 'promotions'])
            ->firstOrFail();

        return view('frontend.products.show', compact('product'));
    }
}