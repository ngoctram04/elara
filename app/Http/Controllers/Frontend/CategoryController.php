<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Nếu là danh mục cha → lấy tất cả con
        $categoryIds = $category->parent_id
            ? [$category->id]
            : $category->children()->pluck('id')->push($category->id);

        $products = Product::whereIn('category_id', $categoryIds)
            ->where('is_active', 1)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Sidebar
        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('frontend.category.show', compact(
            'category',
            'products',
            'allCategories'
        ));
    }
}