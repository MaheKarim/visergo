<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponListController extends Controller
{
    public function index()
    {
        $coupons = Coupon::active()->where('expired_at', '>=', Carbon::now()->format('Y-m-d'))->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon List',
            'data' => $coupons,
        ]);
    }


}
