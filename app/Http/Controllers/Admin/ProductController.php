<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
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
            'variants'
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

        // 🔥 THÊM ĐOẠN NÀY
        if ($request->status === 'in_stock') {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        if ($request->status === 'out_stock') {
            $query->whereDoesntHave('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
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

            // Slug unique
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            // Product
            $product = Product::create([
                'name'        => $data['name'],
                'slug'        => $slug,
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
                'is_active'   => true,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            // Main image
            $product->images()->create([
                'image_path' => $request->file('main_image')->store('products', 'public'),
                'is_main'    => 1,
            ]);

            // Sub images
            if ($request->hasFile('sub_images')) {
                foreach ($request->file('sub_images') as $image) {
                    $product->images()->create([
                        'image_path' => $image->store('products/sub', 'public'),
                        'is_main'    => 0,
                    ]);
                }
            }

            // Variants
            foreach ($data['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'attribute_name'  => $data['variant_attribute_name'],
                    'attribute_value' => $variantData['attribute_value'],
                    'price'           => $variantData['price'],
                    'cost_price'      => 0,
                    'stock_quantity'  => 0,
                    'sold_quantity'   => 0, // Chỉ tăng khi đơn đã giao
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

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công');
    }

    /* =======================
        SỬA
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
    public function show(Product $product)
    {
        $product->load([
            'category.parent',
            'brand',
            'images',
            'mainImage',
            'subImages',
            'variants.images'
        ]);

        return view('admin.products.show', compact('product'));
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

            // 🔥 thêm
            'delete_images'   => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',

            'variant_attribute_name'     => 'required|string|max:100',
            'variants'                   => 'required|array|min:1',
            'variants.*.id'              => 'nullable|exists:product_variants,id',
            'variants.*.attribute_value' => 'required|string|max:100',
            'variants.*.price'           => 'required|numeric|min:0',
            'variants.*.image'           => 'nullable|image',
        ]);

        DB::transaction(function () use ($request, $data, $product) {

            /* ==================================================
            UPDATE PRODUCT
        ================================================== */
            $product->update([
                'name'        => $data['name'],
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'],
                'description' => $data['description'] ?? null,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            /* ==================================================
            XÓA ẢNH PHỤ
        ================================================== */
            if (!empty($data['delete_images'])) {

                $images = $product->images()
                ->whereIn('id', $data['delete_images'])
                ->where('is_main', 0)
                ->get();

                foreach ($images as $img) {
                    // xóa file
                    if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                        Storage::disk('public')->delete($img->image_path);
                    }

                    // xóa DB
                    $img->delete();
                }
            }

            /* ==================================================
            MAIN IMAGE
        ================================================== */
            if ($request->hasFile('main_image')) {

                $oldMain = $product->images()->where('is_main', 1)->first();

                if ($oldMain && Storage::disk('public')->exists($oldMain->image_path)) {
                    Storage::disk('public')->delete($oldMain->image_path);
                }

                $product->images()->where('is_main', 1)->delete();

                $product->images()->create([
                    'image_path' => $request->file('main_image')->store('products', 'public'),
                    'is_main'    => 1,
                ]);
            }

            /* ==================================================
            ADD SUB IMAGES
        ================================================== */
            if ($request->hasFile('sub_images')) {
                foreach ($request->file('sub_images') as $image) {
                    $product->images()->create([
                        'image_path' => $image->store('products/sub', 'public'),
                        'is_main'    => 0,
                    ]);
                }
            }

            /* ==================================================
            VARIANTS
        ================================================== */
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
                } else {

                    $variant = $product->variants()->create([
                        'attribute_name'  => $data['variant_attribute_name'],
                        'attribute_value' => $variantData['attribute_value'],
                        'price'           => $variantData['price'],
                        'cost_price'      => 0,
                        'stock_quantity'  => 0,
                        'sold_quantity'   => 0,
                    ]);
                }

                // variant image
                if (!empty($variantData['image'])) {

                    $oldImage = $variant->images()->first();
                    if ($oldImage && Storage::disk('public')->exists($oldImage->image_path)) {
                        Storage::disk('public')->delete($oldImage->image_path);
                    }

                    $variant->images()->delete();

                    $variant->images()->create([
                        'image_path' => $variantData['image']->store('variants', 'public'),
                        'is_main'    => 1,
                    ]);
                }

                $submittedIds[] = $variant->id;
            }

            /* ==================================================
            DELETE VARIANT (nếu tồn = 0 và chưa bán)
        ================================================== */
            $toDelete = array_diff($existingIds, $submittedIds);

            if ($toDelete) {
                $variants = $product->variants()->whereIn('id',
                    $toDelete
                )->get();

                foreach ($variants as $variant) {
                    if ($variant->stock_quantity > 0 || $variant->sold_quantity > 0) {
                        continue;
                    }
                    $variant->delete();
                }
            }

            $this->recalculateProduct($product);
        });

        return redirect()->route('admin.products.index')
        ->with('success', 'Cập nhật sản phẩm thành công');
    }
    /* =======================
        HELPER
    ======================= */
    private function recalculateProduct(Product $product): void
    {
        $product->update([
            'min_price' => $product->variants()->min('price'),
            'max_price' => $product->variants()->max('price'),
        ]);
    }

    /* =======================
        XÓA
    ======================= */
    public function destroy(Product $product)
    {
        // kiểm tra tồn kho
        $stock = $product->variants()->sum('stock_quantity');

        if ($stock > 0) {
            return back()->with('error', 'Sản phẩm còn tồn kho nên không thể xóa');
        }

        // kiểm tra có nằm trong đơn hàng không
        $hasOrder = DB::table('order_items')
        ->join('product_variants', 'order_items.variant_id', '=', 'product_variants.id')
        ->where('product_variants.product_id', $product->id)
        ->exists();

        if ($hasOrder) {
            return back()->with('error', 'Sản phẩm đã có trong đơn hàng nên không thể xóa');
        }

        $product->delete();

        return redirect()
        ->route('admin.products.index')
        ->with('success', 'Đã xóa sản phẩm');
    }
    public function toggle(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $message = $product->is_active
            ? 'Sản phẩm đã được hiển thị trên website'
            : 'Sản phẩm đã được ẩn khỏi website';

        return redirect()
            ->route('admin.products.index')
            ->with('success', $message);
    }
}