<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $pageTitle = 'Coupon Management';
        $coupons = Coupon::latest()->searchable(['name'])->paginate(getPaginate());

        return view('admin.coupon.index', compact('pageTitle', 'coupons'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|max:40|min:3',
            'discount_value' => 'required|numeric|gt:0|lt:100',
            'discount_type' => 'required',
            'expire_at' => 'required|date|after_or_equal:'.Carbon::now()->format('Y-m-d'),
        ]);

        if(!$id) {
            $coupon = new Coupon();
            $notification = 'Coupon added successfully';
        } else {
            $coupon = Coupon::findOrFail($id);
            $notification = 'Coupon updated successfully';
        }
        $coupon->name = $request->name;
        $coupon->discount_value = $request->discount_value;
        $coupon->discount_type = $request->discount_type;
        $coupon->expire_at = $request->expire_at;
        $coupon->save();

        $notify[] = ['success', $notification];

        return back()->withNotify($notify);
    }

}
