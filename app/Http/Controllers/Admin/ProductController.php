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
            'products'   => $query->latest()->paginate(10)->withQueryString(),
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

            'main_image'   => 'required|image',
            'sub_images.*' => 'nullable|image',

            'is_featured' => 'nullable|boolean',

            'variant_attribute_name'     => 'required|string|max:100',
            'variants'                   => 'required|array|min:1',
            'variants.*.attribute_value' => 'required|string|max:100',
            'variants.*.price'           => 'required|numeric|min:0',
            'variants.*.image'           => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data) {

            /* SLUG */
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            /* PRODUCT */
            $product = Product::create([
                'name'        => $data['name'],
                'slug'        => $slug,
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
                'is_active'   => true,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            /* MAIN IMAGE */
            $product->images()->create([
                'image_path' => $request->file('main_image')->store('products', 'public'),
                'is_main'    => 1,
            ]);

            /* SUB IMAGES */
            if ($request->hasFile('sub_images')) {
                foreach ($request->file('sub_images') as $image) {
                    $product->images()->create([
                        'image_path' => $image->store('products/sub', 'public'),
                        'is_main'    => 0,
                    ]);
                }
            }

            /* VARIANTS */
            foreach ($data['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'attribute_name'  => $data['variant_attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
                    'cost_price'      => 0,
                    'stock_quantity'  => 0,
                    'sold_quantity'   => 0,
                ]);

                if (!empty($variantData['image'])) {
                    $variant->images()->create([
                        'image_path' => $variantData['image']->store('variants', 'public'),
                        'is_main'    => 1,
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
            'mainImage',
            'subImages',
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

            'main_image'   => 'nullable|image',
            'sub_images.*' => 'nullable|image',

            'variant_attribute_name'     => 'required|string|max:100',
            'variants'                   => 'required|array|min:1',
            'variants.*.id'              => 'nullable|exists:product_variants,id',
            'variants.*.attribute_value' => 'required|string|max:100',
            'variants.*.price'           => 'required|numeric|min:0',
            'variants.*.image'           => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data, $product) {

            /* PRODUCT */
            $product->update([
                'name'        => $data['name'],
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
            ]);

            /* VARIANTS */
            $existingIds = $product->variants()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($data['variants'] as $variantData) {

                if (!empty($variantData['id'])) {
                    $variant = $product->variants()->find($variantData['id']);
                    if (!$variant) continue;

                    $variant->update([
                        'attribute_name'  => $data['variant_attribute_name'],
                        'attribute_value' => $variantData['attribute_value'],
                        'price'           => $variantData['price'],
                    ]);

                    $submittedIds[] = $variant->id;
                } else {
                    $variant = $product->variants()->create([
                        'attribute_name'  => $data['variant_attribute_name'],
                        'attribute_value' => $variantData['attribute_value'],
                        'price'           => $variantData['price'],
                        'cost_price'      => 0,
                        'stock_quantity'  => 0,
                        'sold_quantity'   => 0,
                    ]);

                    $submittedIds[] = $variant->id;
                }
            }

            /* KHÔNG XÓA variant còn tồn hoặc đã bán */
            $toDelete = array_diff($existingIds, $submittedIds);
            if ($toDelete) {
                $variants = $product->variants()->whereIn('id', $toDelete)->get();

                foreach ($variants as $variant) {
                    if ($variant->stock_quantity > 0 || $variant->sold_quantity > 0) {
                        continue;
                    }
                    $variant->delete();
                }
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
            'total_stock' => $product->variants()->sum('stock_quantity'),
            'total_sold'  => $product->variants()->sum('sold_quantity'),
        ]);
    }

    /* =======================
        XÓA
    ======================= */
    public function destroy(Product $product)
    {
        if ($product->variants()->sum('stock_quantity') > 0) {
            return back()->with('error', 'Sản phẩm còn tồn kho, không thể xóa');
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm');
    }
}