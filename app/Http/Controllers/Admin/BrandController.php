<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        if (Schema::hasTable('products')) {
            $query = Brand::withCount('products');
        } else {
            $query = Brand::query();
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("CONCAT('TH', LPAD(id, 4, '0')) LIKE ?", ['%' . $keyword . '%']);
            });
        }

        if ($request->sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $brands = $query->paginate(8)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:brands,name',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        Brand::create([
            'name'  => $data['name'],
            'slug'  => Str::slug($data['name']),
            'image' => $data['image'] ?? null,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Thêm thương hiệu thành công');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $updateData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ];

        if ($request->hasFile('image')) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }

            $updateData['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($updateData);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Không thể xóa thương hiệu đang có sản phẩm.');
        }

        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        $brand->delete();

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Xóa thương hiệu thành công.');
    }
}