<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $pageTitle = 'Coupon Management';
        $coupons = Coupon::where('expire_at', '>=', Carbon::now()->format('Y-m-d'))
            ->searchable(['name'])->paginate(getPaginate());

        return view('admin.coupon.index', compact('pageTitle', 'coupons'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => [
                'required',
                'max:40',
                'min:3',
                Rule::unique('coupons')->where(function ($query) {
                    return $query->where('expire_at', '>=', Carbon::now()->format('Y-m-d'))
                        ->where('id', '!=', request()->id);
                })
            ],
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
        $coupon->name = strtoupper($request->name);
        $coupon->discount_value = $request->discount_value;
        $coupon->discount_type = $request->discount_type;
        $coupon->expire_at = $request->expire_at;
        $coupon->description = $request->description;
        $coupon->save();

        $notify[] = ['success', $notification];

        return back()->withNotify($notify);
    }

}
