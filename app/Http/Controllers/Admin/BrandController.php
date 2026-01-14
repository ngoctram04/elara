<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $brands = $query->get();

        return view('admin.brands.index', compact('brands'));
    }

    // FORM THÊM
    public function create()
    {
        return view('admin.brands.create');
    }

    // LƯU THÊM
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Thêm thương hiệu thành công');
    }

    // FORM SỬA
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    // LƯU SỬA
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công');
    }

    // XÓA
    public function destroy(Brand $brand)
    {
        // Sau này có products thì chặn xoá ở đây
        $brand->delete();

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Đã xóa thương hiệu');
    }
}