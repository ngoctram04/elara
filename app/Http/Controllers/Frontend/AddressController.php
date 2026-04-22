<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserAddress;

class AddressController extends Controller
{

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('frontend.profile.addresses', compact('addresses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'receiver_name'  => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'province'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'address_detail' => 'required|string|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {

           
            $isDefault = $request->has('is_default') || $user->addresses()->count() == 0;

            if ($isDefault) {
                $user->addresses()->update(['is_default' => 0]);
            }

            $user->addresses()->create([
                'receiver_name'  => $request->receiver_name,
                'phone'          => $request->phone,
                'province'       => $request->province,
                'district'       => $request->district,
                'ward'           => $request->ward,
                'address_detail' => $request->address_detail,
                'is_default'     => $isDefault,
            ]);
        });

        return back()->with('success', 'Thêm địa chỉ thành công');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'receiver_name'  => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'province'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'address_detail' => 'required|string|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($request, $user, $id) {

            $address = $user->addresses()
                ->where('id', $id)
                ->firstOrFail();

            if ($request->has('is_default')) {
                $user->addresses()->update(['is_default' => 0]);
                $address->is_default = 1;
            }

            $address->update([
                'receiver_name'  => $request->receiver_name,
                'phone'          => $request->phone,
                'province'       => $request->province,
                'district'       => $request->district,
                'ward'           => $request->ward,
                'address_detail' => $request->address_detail,
                'is_default'     => $request->has('is_default')
                    ? 1
                    : $address->is_default,
            ]);
        });

        return back()->with('success', 'Cập nhật địa chỉ thành công');
    }

    public function setDefault($id)
    {
        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($user, $id) {

            $user->addresses()->update(['is_default' => 0]);

            $address = $user->addresses()
                ->where('id', $id)
                ->firstOrFail();

            $address->update(['is_default' => 1]);
        });

        return back()->with('success', 'Đã đặt làm địa chỉ mặc định');
    }

    public function destroy($id)
    {
        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($user, $id) {

            $address = $user->addresses()
                ->where('id', $id)
                ->firstOrFail();

            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $newDefault = $user->addresses()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => 1]);
                }
            }
        });

        return back()->with('success', 'Đã xóa địa chỉ');
    }
}