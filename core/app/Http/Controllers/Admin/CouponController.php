<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppliedCoupon;

class CouponController extends Controller
{
    public function index()
    {
        $pageTitle = "All Coupons";
        $coupons   = Coupon::paginate(getPaginate());
        return view('admin.coupons.index', compact('pageTitle', 'coupons'));
    }

    public function create()
    {
        $pageTitle  = "Create New Coupon";
        return view('admin.coupons.create', compact('pageTitle'));
    }

    public function edit($id)
    {
        $coupon = Coupon::whereId($id)->firstOrFail();
        $pageTitle  = "Edit Coupon";
        return view('admin.coupons.create', compact('pageTitle', 'coupon'));
    }

    public function save(Request $request, $id)
    {

        $this->validation($request, $id);

        if ($id == 0) {
            $coupon   = new Coupon();
            $notify[] = ['success', 'Coupon created successfully'];
        } else {
            $coupon   = Coupon::findOrFail($id);
            $notify[] = ['success', 'Coupon updated successfully'];
        }

        $coupon->coupon_name             = $request->coupon_name;
        $coupon->coupon_code             = $request->coupon_code;
        $coupon->discount_type           = $request->discount_type;
        $coupon->coupon_amount           = $request->amount;
        $coupon->description             = $request->description;
        $coupon->starts_from             = $request->starts_from;
        $coupon->ends_at                 = $request->ends_at;
        $coupon->minimum_spend           = $request->minimum_spend;
        $coupon->maximum_spend           = $request->maximum_spend;
        $coupon->usage_limit_per_coupon  = $request->usage_limit_per_coupon;
        $coupon->usage_limit_per_user    = $request->usage_limit_per_customer;
        $coupon->save();

        return to_route('admin.coupon.index')->withNotify($notify);
    }

    public function changeStatus(Request $request)
    {
        $coupon         = Coupon::findOrFail($request->id);
        $coupon->status = !$coupon->status;
        $coupon->save();

        $message = $coupon->status ? 'Coupon activated successfully' : 'Coupon deactivated successfully';

        return formatResponse('coupon_activation_status','success', $message);
    }

    private function validation(Request $request, int $id)
    {
        $request->validate([
            "coupon_name"              => 'required|string|max:40',
            "coupon_code"              => 'required|string|max:40|unique:coupons,coupon_code,' . $id,
            "discount_type"            => 'required|in:1,2',
            "amount"                   => 'required|numeric',
            "starts_from"              => 'required|date|date_format:Y-m-d',
            "ends_at"                  => 'required|date|date_format:Y-m-d|after:starts_from',
            "description"              => 'nullable|string',
            "minimum_spend"            => 'nullable|numeric|gt:0',
            "maximum_spend"            => 'nullable|numeric|gte:minimum_spend',
            "usage_limit_per_coupon"   => 'nullable|integer',
            "usage_limit_per_customer" => 'nullable|integer',
        ]);
    }

    public function detail($id)
    {
        $coupon    = Coupon::find($id);
        $pageTitle = 'Applied Coupons Details - ' . $coupon->coupon_name;
        $query     = AppliedCoupon::where('coupon_id', $id);

        $querySum    = clone $query;
        $totalAmount = $querySum->sum('amount');

        $queryCount  = clone $query;
        $couponCount = $querySum->count();

        $queryAppliedCoupon = clone $query;
        $appliedCoupons     = $queryAppliedCoupon->with(['user', 'ride'])->where('coupon_id', $id)->latest()->paginate(getPaginate());

        return view('admin.coupons.detail', compact('pageTitle', 'appliedCoupons', 'totalAmount', 'couponCount'));
    }
}
