<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AppliedCoupon;
use App\Models\Coupon;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::active()->where('ends_at', '>=', Carbon::now()->format('Y-m-d'))->get();

        return formatResponse('coupons', 'success', 'Coupons', $coupons);
    }

    public function applyCoupon(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(errorResponse('coupon_validation_error', $validator->errors()->first()), 422);
        }

        $ride = Ride::ongoingRide()->where('user_id', auth()->id())->find($id);

        if (!$ride) {
            return formatResponse('ride_not_found', 'error', 'No Ride Found');
        }

        $exitsCoupon = AppliedCoupon::where('ride_id', $ride->id)->first();

        if ($exitsCoupon) {
            return formatResponse('coupon_already_applied', 'error', 'The coupon has already been applied');
        }

        $coupon = $this->isValidCoupon($request->coupon_code, $ride->total);

        if (isset($coupon['error'])) {
            return errorResponse('coupon_validation_error',$coupon['error']);
        }

        $discountAmount = $coupon->discountAmount($ride->total);

        $applyCoupon = new AppliedCoupon();
        $applyCoupon->user_id = $ride->user_id;
        $applyCoupon->coupon_id = $coupon->id;
        $applyCoupon->ride_id = $ride->id;
        $applyCoupon->amount = $discountAmount;
        $applyCoupon->save();

        $ride->applied_coupon_id = $applyCoupon->id;
        $ride->save();

        $notify[] =  'Coupon applied successfully';
        return formatResponse('coupon_applied', 'success', $notify, $ride);

    }

    public function isValidCoupon($couponCode, $amount)
    {
        $coupon = $this->getCouponByCode($couponCode);

        if (!$coupon) {
            return ['error' => "Invalid coupon code"];
        }

        $general      = gs();
        $minimumSpend = $coupon->minimum_spend;
        $maximumSpend = $coupon->maximum_spend;

        // Check Minimum Subtotal
        if ($minimumSpend && $amount < $minimumSpend) {
            return ['error' => "Your ride amount must not be less than {$minimumSpend} {$general->cur_text} to avail yourself of this coupon"];
        }

        // Check Maximum Subtotal
        if ($maximumSpend && $amount > $maximumSpend) {
            return ['error' => "Your ride amount must not be greater than {$maximumSpend} {$general->cur_text} to avail yourself of this coupon"];
        }

        //Check Limit Per Coupon
        if ($coupon->usage_limit_per_coupon && $coupon->applied_coupons_count >= $coupon->usage_limit_per_coupon) {
            return ['error' => "This coupon has exceeded the maximum limit for usage"];
        }

        //Check Limit Per User
        if ($coupon->usage_limit_per_coupon && $coupon->user_applied_count >= $coupon->usage_limit_per_user) {
            return ['error' => "You have already reached the maximum usage limit for this coupon"];
        }
        return $coupon;
    }

    private function getCouponByCode(string $code)
    {
        $currentDate = today();

        $couponGet = Coupon::where('status', Status::ENABLE)
            ->where('starts_from', '<=', $currentDate)
            ->where('ends_at', '>=', $currentDate)
            ->withCount('appliedCoupons')
            ->withCount(['appliedCoupons as user_applied_count' => function ($appliedCoupon) {
                $appliedCoupon->where('user_id', auth()->id());
            }])
            ->first();

        return $couponGet;

    }

    public function removeCoupon($id)
    {
        $ride = Ride::ongoingRide()->where('user_id', auth()->id())->find($id);

        if (!$ride) {
            return response()->json(errorResponse('ride_not_found','The ride is invalid'));
        }

        $exitsCoupon = AppliedCoupon::where('ride_id', $id)->first();

        if (!$exitsCoupon) {
            return response()->json(errorResponse('coupon_not_found','You have not applied the coupon for this ride'));
        }

        $exitsCoupon->delete();

        $notify[] =  'Coupon delete successfully';

        return formatResponse('coupon_removed', 'success', $notify, $ride);
    }

}
