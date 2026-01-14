<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /* =======================
        DANH SÁCH
    ======================== */
    public function index(Request $request)
    {
        $query = Product::with([
            'category.parent',
            'brand',
            'mainImage',
            'variants',
        ]);

        /* 🔍 TÌM THEO TÊN */
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        /* 🗂️ LỌC THEO DANH MỤC CON (QUAN TRỌNG) */
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        /* 🏷️ LỌC THEO THƯƠNG HIỆU */
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        /* 📦 TRẠNG THÁI KHO */
        if ($request->status === 'in_stock') {
            $query->whereHas('variants', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        if ($request->status === 'out_stock') {
            $query->whereDoesntHave('variants', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        $products = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString(); // giữ filter khi phân trang

        /* DỮ LIỆU FILTER */
        $categories = Category::whereNull('parent_id')
        ->with('children')
            ->get();

        $brands = Brand::orderBy('name')->get();

        return view('admin.products.index',
            compact(
                'products',
                'categories',
                'brands'
            )
        );
    }



    /* =======================
        FORM THÊM
    ======================== */
    public function create()
    {
        return view('admin.products.create', [
            // ✅ CHỈ LẤY DANH MỤC CON
            'categories' => Category::whereNotNull('parent_id')->orderBy('name')->get(),
            'brands'     => Brand::all(),
        ]);
    }

    /* =======================
        LƯU SẢN PHẨM
    ======================== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',

            // ✅ ÉP CHỈ ĐƯỢC CHỌN DANH MỤC CON
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNotNull('parent_id'),
            ],

            'brand_id'    => 'required|exists:brands,id',
            'description' => 'nullable|string',

            'main_image'  => 'required|image',
            'images.*'    => 'nullable|image',

            'variants'                   => 'required|array|min:1',
            'variants.*.attribute_name'  => 'required|string|max:100',
            'variants.*.attribute_value' => 'required|string|max:100',
            'variants.*.price'           => 'required|numeric|min:0',
            'variants.*.original_price'  => 'nullable|numeric|min:0',
            'variants.*.stock'           => 'required|integer|min:0',
            'variants.*.image'           => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data) {

            $product = Product::create([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']),
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
            ]);

            /* Ảnh đại diện */
            $product->images()->create([
                'image_path' => $request->file('main_image')->store('products', 'public'),
                'is_main'    => true,
            ]);

            /* Ảnh phụ */
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $product->images()->create([
                        'image_path' => $img->store('products', 'public'),
                        'is_main'    => false,
                    ]);
                }
            }

            /* Biến thể */
            foreach ($data['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'attribute_name'  => $variantData['attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
                    'original_price'  => $variantData['original_price'] ?? null,
                    'stock'           => $variantData['stock'],
                ]);

                if (!empty($variantData['image'])) {
                    $variant->images()->create([
                        'image_path' => $variantData['image']->store('variants', 'public'),
                        'is_main'    => true,
                    ]);
                }
            }

            $this->recalculateProduct($product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công');
    }

    /* =======================
        FORM SỬA
    ======================== */
    public function edit(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'images',
            'mainImage',
            'variants.images',
        ]);

        return view('admin.products.edit', [
            'product'    => $product,

            // ✅ CHỈ LẤY DANH MỤC CON
            'categories' => Category::whereNotNull('parent_id')->orderBy('name')->get(),
            'brands'     => Brand::all(),
        ]);
    }

    /* =======================
        CẬP NHẬT
    ======================== */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',

            // ✅ ÉP CHỈ ĐƯỢC CHỌN DANH MỤC CON
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNotNull('parent_id'),
            ],

            'brand_id'    => 'required|exists:brands,id',
            'description' => 'nullable|string',

            'main_image'  => 'nullable|image',
            'images.*'    => 'nullable|image',

            'variants'                   => 'required|array|min:1',
            'variants.*.id'              => 'nullable|exists:product_variants,id',
            'variants.*.attribute_name'  => 'required|string|max:100',
            'variants.*.attribute_value' => 'required|string|max:100',
            'variants.*.price'           => 'required|numeric|min:0',
            'variants.*.stock'           => 'required|integer|min:0',
            'variants.*.image'           => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data, $product) {

            $product->update([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']),
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
            ]);

            /* Ảnh đại diện */
            if ($request->hasFile('main_image')) {
                if ($product->mainImage) {
                    Storage::disk('public')->delete($product->mainImage->image_path);
                    $product->mainImage->delete();
                }

                $product->images()->create([
                    'image_path' => $request->file('main_image')->store('products', 'public'),
                    'is_main'    => true,
                ]);
            }

            /* Biến thể */
            $oldVariantIds = $product->variants->pluck('id')->toArray();
            $newVariantIds = [];

            foreach ($data['variants'] as $variantData) {
                $variant = !empty($variantData['id'])
                    ? $product->variants()->find($variantData['id'])
                    : $product->variants()->create([]);

                $variant->update([
                    'attribute_name'  => $variantData['attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
                    'stock'           => $variantData['stock'],
                ]);

                if (!empty($variantData['image'])) {
                    foreach ($variant->images as $img) {
                        Storage::disk('public')->delete($img->image_path);
                    }
                    $variant->images()->delete();

                    $variant->images()->create([
                        'image_path' => $variantData['image']->store('variants', 'public'),
                        'is_main'    => true,
                    ]);
                }

                $newVariantIds[] = $variant->id;
            }

            /* Xóa biến thể bị remove */
            $deleteIds = array_diff($oldVariantIds, $newVariantIds);
            if ($deleteIds) {
                $variants = $product->variants()->whereIn('id', $deleteIds)->get();
                foreach ($variants as $variant) {
                    foreach ($variant->images as $img) {
                        Storage::disk('public')->delete($img->image_path);
                    }
                    $variant->images()->delete();
                    $variant->delete();
                }
            }

            $this->recalculateProduct($product);
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công');
    }

    /* =======================
        XOÁ
    ======================== */
    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {

            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            foreach ($product->variants as $variant) {
                foreach ($variant->images as $img) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $variant->images()->delete();
            }

            $product->variants()->delete();
            $product->images()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công');
    }

    /* =======================
        HELPER
    ======================== */
    private function recalculateProduct(Product $product): void
    {
        $product->update([
            'min_price'   => $product->variants()->min('price'),
            'max_price'   => $product->variants()->max('price'),
            'total_stock' => $product->variants()->sum('stock'),
        ]);
    }

    /* =======================
        XEM CHI TIẾT
    ======================== */
    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'images',
            'mainImage',
            'variants.images',
        ]);

        return view('admin.products.show', compact('product'));
    }
}