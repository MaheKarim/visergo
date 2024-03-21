<?php

namespace App\Constants;

class Status{

    const ENABLE = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO = 0;

    const VERIFIED = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_PENDING = 2;
    const PAYMENT_REJECT = 3;

    CONST TICKET_OPEN = 0;
    CONST TICKET_ANSWER = 1;
    CONST TICKET_REPLY = 2;
    CONST TICKET_CLOSE = 3;

    CONST PRIORITY_LOW = 1;
    CONST PRIORITY_MEDIUM = 2;
    CONST PRIORITY_HIGH = 3;

    const USER_ACTIVE = 1;
    const USER_BAN = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING = 2;
    const KYC_VERIFIED = 1;

    const RIDE_INITIATED = 1; // When User Ride Request
    const RIDE_ACTIVE = 2; // When Driver Accept
    const RIDE_ONGOING = 3; // When Driver Give OTP
    const RIDE_END = 4; // When Driver End
    const RIDE_COMPLETED = 5; // When Payment Done
    const RIDE_CANCELED = 6; // When Canceled

    const RIDE_SERVICE = 1;
    const INTER_CITY_SERVICE = 2;
    const RENTAL_SERVICE = 3;
    const RESERVE_SERVICE = 4;

    const FIXED = 1;
    const PERCENTAGE = 2;

    const RIDER = 1;
    const DRIVER = 2;

    const DRIVER_ACTIVE = 1;
    const DRIVER_BAN = 0;

    const ONLINE = 1;
    const OFFLINE = 0;

    const CANCEL = 1;
    const CANCELABLE = 0;

    const RIDE_FOR_OWN = 1;
    const RIDE_FOR_PILLION = 2;

    const DRIVING = 1 ;
    const IDLE = 0 ;
    const CASH_PAYMENT = 1;
    const ONLINE_PAYMENT = 2;
    const WALLET_PAYMENT = 3;

}
