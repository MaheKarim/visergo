<?php

use App\Models\Driver;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->name('api.')->group(function () {

    Route::get('general-setting', function () {
        $general = GeneralSetting::first();
        $notify[] = 'General setting data';
        return response()->json([
            'remark' => 'general_setting',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'general_setting' => $general,
            ],
        ]);
    });

    Route::get('tips', function () {
        $general = GeneralSetting::first('tips');
        $notify[] = 'Tips';
        return response()->json([
            'remark' => 'tips_data',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'general_setting' => $general,
            ],
        ]);
    });

    Route::get('get-countries', function () {
        $c = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $notify[] = 'General setting data';
        foreach ($c as $k => $country) {
            $countries[] = [
                'country' => $country->country,
                'dial_code' => $country->dial_code,
                'country_code' => $k,
            ];
        }
        return response()->json([
            'remark' => 'country_data',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'countries' => $countries,
            ],
        ]);
    });

    Route::namespace('Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        //authorization
        Route::controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('user-data-submit', 'UserController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {
                Route::get('dashboard', function () {
                    return auth()->user();
                });

                Route::get('user-info', function () {
                    $notify[] = 'User information';
                    return response()->json([
                        'remark' => 'user_info',
                        'status' => 'success',
                        'message' => ['success' => $notify],
                        'data' => [
                            'user' => auth()->user()
                        ]
                    ]);
                });

                Route::controller('UserController')->group(function () {
                    //KYC
                    Route::get('kyc-form', 'kycForm')->name('kyc.form');
                    Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                    //Report
                    Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                    Route::get('transactions', 'transactions')->name('transactions');
                });

                //Profile setting
                Route::controller('UserController')->group(function () {
                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');

                    // Cancellation Reason API
                    Route::get('/cancellation-reasons', 'userCancelReason')->name('user.cancel.reason');
                });

                // Withdraw
                Route::controller('WithdrawController')->group(function () {
                    Route::get('withdraw-method', 'withdrawMethod')->name('withdraw.method')->middleware('kyc');
                    Route::post('withdraw-request', 'withdrawStore')->name('withdraw.money')->middleware('kyc');
                    Route::post('withdraw-request/confirm', 'withdrawSubmit')->name('withdraw.submit')->middleware('kyc');
                    Route::get('withdraw/history', 'withdrawLog')->name('withdraw.history');
                });

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::get('deposit/methods', 'methods')->name('deposit');
                    Route::post('deposit/insert', 'depositInsert')->name('deposit.insert');
                    Route::get('deposit/confirm', 'depositConfirm')->name('deposit.confirm');
                    Route::get('deposit/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                    Route::post('deposit/manual', 'manualDepositUpdate')->name('deposit.manual.update');
                });

                Route::namespace('User')->group(function () {
                    // User Address Management
                    Route::controller('AddressController')->group(function () {
                        Route::get('address', 'addresses')->name('address');
                        Route::post('address/insert', 'store')->name('address.insert');
                        Route::post('address/update/{id}', 'store')->name('address.update');
                        Route::post('address/delete/{id}', 'delete')->name('address.delete');
                    });

                    // Contact List Management
                    Route::controller('ContactListController')->name('user.')->group(function () {
                        Route::get('contacts', 'contacts')->name('contact');
                        Route::post('contact/insert', 'contactInsert')->name('contact.insert');
                        Route::post('contact/update/{id}', 'contactUpdate')->name('contact.update');
                        Route::post('contact/delete/{id}', 'contactDelete')->name('contact.delete');
                    });
                    // Ride Request Controller
                    Route::controller('RideController')->name('ride.')->group(function () {
                        Route::post('ride-search', 'rideSearch');
                        Route::post('ride/create/', 'rideRequest');
                        Route::post('ride/tips/add/{id}', 'rideTips');
                        Route::get('ride/completed/history', 'rideCompleted');
                        Route::get('ride/ongoing/history', 'rideOngoing');
                        Route::middleware('userRideCancel')->group(function () {
                            Route::post('ride/cancel/{id}', 'rideCancel');
                        });
                        Route::post('ride/review/{id}', 'rideReview');
                    });
                    // Coupon List For User
                    Route::controller('CouponListController')->name('coupon.')->group(function () {
                        Route::get('coupons', 'index')->name('coupon');
                    });
                    // Gateway List For User // WIP Middleware Check
                    Route::controller('PaymentController')->name('payment.')->group(function () {
                        Route::get('methods', 'methods')->name('methods');
                        Route::get('method/{id}', 'method')->name('method');
                        Route::post('payment/insert/{id}', 'depositInsert')->name('insert');
                    });
                });
            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });


    /*
    |--------------------------------------------------------------------------
    |                           Driver API Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        // Ride Chat
        Route::get('ride/chat/{id}/messages', 'ChatController@rideChatMessages')->name('ride.chat.messages');
        Route::post('ride/chat/{id}', 'ChatController@sendRideChat')->name('ride.chat');
    });


    Route::namespace('Driver\Auth')->group(function () {
        Route::post('driver/login', 'DriverLoginController@login')->name('driver.login');
        Route::post('driver/register', 'DriverRegisterController@register');

        Route::controller('DriverForgotPasswordController')->name('driver.')->prefix('driver')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');
        });
    });

    Route::middleware('auth:sanctum')->namespace('Driver')->group(function () {
        // Authorization
        Route::controller('DriverAuthorizationController')->name('driver.')->prefix('driver')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['driver.check.status'])->group(function () {
            // Profile Complete
            Route::post('driver-data-submit', 'DriverController@driverDataSubmit')->name('driver.data.submit');

            Route::controller('DriverController')->prefix('driver')->group(function () {
                //KYC
                Route::get('verification-form', 'verificationForm')->name('kyc.form');
                Route::post('verificationForm-submit', 'verificationFormSubmit')->name('kyc.submit');
                // Vehicle Verification
                Route::get('vehicle-verification', 'vehicleVerification')->name('vehicle.verification');
                Route::post('vehicle-verification-submit', 'vehicleVerificationSubmit')->name('vehicle.verification.submit');
            });

            // Driver License & Vehicle Registration Verification Middleware
            Route::middleware('driver.verification')->group(function () {
                // Deposit & Transactions
                Route::controller('DriverController')->prefix('driver')->group(function () {
                    Route::post('current-status', 'currentStatus')->name('current.status');
                    Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                    Route::get('transactions', 'transactions')->name('transactions');
                    Route::get('/cancellation-reasons', 'driverCancelReason')->name('driver.cancel.reason');
                });

                // Driver Online Status Middleware
                Route::middleware('isDriverOnline')->group(function () {
                    Route::controller('RideRequestController')->name('ride.')->prefix('driver')->group(function () {
                        // Live Requests
                        Route::get('ride/requests', 'rideRequests');
                        Route::get('ride/ongoing-requests', 'ongoingRequests');
                        // Can  Not Accept Multiple Ride Requests Middleware
                        Route::middleware('drivingCheck')->group(function () {
                            Route::post('ride/requests/accept/{id}', 'rideRequestAccept');
                        });
                        Route::post('ride/requests/start/{id}', 'rideRequestStart');
                        Route::post('ride/requests/end/{id}', 'rideRequestEnd');

                        Route::middleware('driverRideCancel')->group(function () {
                            Route::post('ride/requests/cancel/{id}', 'rideRequestCancel');
                        });

                    });
                });
                Route::get('driver-info', function () {
                    $user = auth()->user();
                    if ($user instanceof Driver) {
                        return response()->json([
                            'remark' => 'user_info',
                            'status' => 'success',
                            'message' => ['success' => 'Driver information'],
                            'data' => [
                                'user' => $user
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'remark' => 'user_info',
                            'status' => 'success',
                            'message' => ['success' => 'No driver found'],
                        ]);
                    }
                });
            });
        });

        Route::get('driver/logout', 'Auth\DriverLoginController@logout');
    });
});
