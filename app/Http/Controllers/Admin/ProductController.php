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
    ======================= */
    public function index(Request $request)
    {
        $query = Product::with([
            'category.parent',
            'brand',
            'mainImage',
            'variants',
        ]);

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        return view('admin.products.index', [
            'products'   => $query->orderByDesc('created_at')->paginate(10)->withQueryString(),
            'categories' => Category::whereNull('parent_id')->with('children')->get(),
            'brands'     => Brand::orderBy('name')->get(),
        ]);
    }

    /* =======================
        FORM THÊM
    ======================= */
    public function create()
    {
        return view('admin.products.create', [
            'categories' => Category::whereNotNull('parent_id')->orderBy('name')->get(),
            'brands'     => Brand::all(),
        ]);
    }

    /* =======================
        LƯU
    ======================= */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNotNull('parent_id'),
            ],
            'brand_id'    => 'required|exists:brands,id',
            'description' => 'nullable|string',

            'main_image' => 'required|image',

            // 🔥 BIẾN THỂ TỰ NHẬP
            'variant_attribute_name'        => 'required|string|max:100',
            'variants'                      => 'required|array|min:1',
            'variants.*.attribute_value'    => 'required|string|max:100',
            'variants.*.price'              => 'required|numeric|min:0',
            'variants.*.stock'              => 'required|integer|min:0',
            'variants.*.image'              => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data) {

            $product = Product::create([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']),
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
            ]);

            /* Ảnh chính */
            $product->images()->create([
                'image_path' => $request->file('main_image')->store('products', 'public'),
                'is_main'    => true,
            ]);

            /* Biến thể */
            foreach ($data['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'attribute_name'  => $data['variant_attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
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

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công');
    }

    /* =======================
        FORM SỬA
    ======================= */
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
            'categories' => Category::whereNotNull('parent_id')->orderBy('name')->get(),
            'brands'     => Brand::all(),
        ]);
    }

    /* =======================
        CẬP NHẬT
    ======================= */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNotNull('parent_id'),
            ],
            'brand_id'    => 'required|exists:brands,id',
            'description' => 'nullable|string',

            'variant_attribute_name'        => 'required|string|max:100',
            'variants'                      => 'required|array|min:1',
            'variants.*.id'                 => 'nullable|exists:product_variants,id',
            'variants.*.attribute_value'    => 'required|string|max:100',
            'variants.*.price'              => 'required|numeric|min:0',
            'variants.*.stock'              => 'required|integer|min:0',
            'variants.*.image'              => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data, $product) {

            $product->update([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']),
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
            ]);

            $oldIds = $product->variants->pluck('id')->toArray();
            $newIds = [];

            foreach ($data['variants'] as $variantData) {
                $variant = !empty($variantData['id'])
                    ? $product->variants()->find($variantData['id'])
                    : $product->variants()->create([]);

                $variant->update([
                    'attribute_name'  => $data['variant_attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
                    'stock'           => $variantData['stock'],
                ]);

                $newIds[] = $variant->id;
            }

            $deleteIds = array_diff($oldIds, $newIds);
            if ($deleteIds) {
                $product->variants()->whereIn('id', $deleteIds)->delete();
            }

            $this->recalculateProduct($product);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công');
    }

    /* =======================
        HELPER
    ======================= */
    private function recalculateProduct(Product $product): void
    {
        $product->update([
            'min_price'   => $product->variants()->min('price'),
            'max_price'   => $product->variants()->max('price'),
            'total_stock' => $product->variants()->sum('stock'),
        ]);
    }

    /* =======================
        CHI TIẾT
    ======================= */
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
    /* =======================
    XÓA
======================= */
    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {

            // Xóa ảnh chính & ảnh phụ
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Xóa ảnh biến thể
            foreach ($product->variants as $variant) {
                foreach ($variant->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Xóa biến thể
            $product->variants()->delete();

            // Xóa sản phẩm
            $product->delete();
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm thành công');
    }

}