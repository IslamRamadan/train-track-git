<?php

namespace App\Services\SmsServices;

use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class TwilioVerifyService
{
    protected Client $client;
    protected string $serviceSid;

    public function __construct()
    {
        $this->client = new Client(
            config('twilio.sid'),
            config('twilio.token')
        );
        $this->serviceSid = config('twilio.verify_service_sid');
    }

    /**
     * Send OTP verification code to phone via SMS.
     *
     * @param string $phone E.164 format phone number
     * @return array{success: bool, status?: string, message?: string}
     */
    public function sendVerification(string $phone): array
    {
        try {
            $verification = $this->client->verify->v2->services($this->serviceSid)
                ->verifications
                ->create($phone, 'sms');

            return [
                'success' => true,
                'status' => $verification->status,
            ];
        } catch (RestException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify the OTP code for a phone number.
     *
     * @param string $phone E.164 format phone number
     * @param string $code The verification code
     * @return bool True if verification approved, false otherwise
     */
    public function checkVerification(string $phone, string $code): bool
    {
        try {
            $verificationCheck = $this->client->verify->v2->services($this->serviceSid)
                ->verificationChecks
                ->create([
                    'code' => $code,
                    'to' => $phone,
                ]);

            return $verificationCheck->status === 'approved';
        } catch (RestException $e) {
            return false;
        }
    }
}
