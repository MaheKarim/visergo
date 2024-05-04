<?php

use Illuminate\Support\Facades\Route;

Route::middleware('driver')->name('driver.')->group(function () {
    Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
        Route::get('confirm', 'depositConfirm')->name('confirm');
    });

    Route::middleware('registration.complete')->namespace('Driver')->group(function () {
        Route::controller('DriverController')->group(function () {
            Route::any('deposit/history', 'depositHistory')->name('deposit.history');
        });
    });
});


