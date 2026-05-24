<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for Twilio API. Create a Verify service in the Twilio
    | console and set TWILIO_VERIFY_SERVICE_SID.
    |
    */

    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_TOKEN'),
    'verify_service_sid' => env('TWILIO_VERIFY_SERVICE_SID'),

];
