<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS "From" Number
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the phone number that will be used as
    | the "from" number for all outgoing text messages. You should provide
    | the number you have already reserved within your Web2sms dashboard.
    |
    */

    'sms_from' => env('WEB2SMS_SMS_FROM', ''),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | The following configuration options contain your API credentials, which
    | may be accessed from your Web2sms dashboard. These credentials may be
    | used to authenticate with the Web2sms API so you may send messages.
    |
    */

    'api_key' => env('WEB2SMS_KEY'),

    'api_secret' => env('WEB2SMS_SECRET'),
];