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

    const RIDE_ACTIVE = 1;
    const RIDE_INITIATED = 2;
    const RIDE_CLOSE = 3;
    const RIDE_COMPLETED = 4;

    const RIDE = 1;
    const INTER_CITY = 2;
    const RENTAL = 3;
    const RESERVE = 4;

    const FIXED = 1;
    const PERCENTAGE = 2;

    const RIDER = 1;
    const DRIVER = 2;

}
