<?php

namespace App\Services\SmsServices;

use App\Services\ValidationServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PhoneVerificationService
{
    protected const CACHE_TTL = 600; // 10 minutes
    protected const CACHE_KEY_PREFIX = 'phone_verified_';

    public function __construct(
        protected TwilioVerifyService $twilioVerifyService,
        protected ValidationServices $validationServices,
    ) {}

    public function send_phone_otp(Request $request)
    {
        $this->validationServices->send_phone_otp($request);

        $result = $this->twilioVerifyService->sendVerification($request->phone);

        if (!$result['success']) {
            return sendError($result['message'] ?? 'Failed to send verification code', 404);
        }

        return sendResponse([
            'message' => 'Message sent successfully',
            'status' => $result['status'] ?? 'pending',
        ]);
    }

    public function verify_phone_otp(Request $request)
    {
        $this->validationServices->verify_phone_otp($request);

        $isValid = $this->twilioVerifyService->checkVerification($request->phone, $request->code);

        if (!$isValid) {
            return sendError('Invalid or expired code', 404);
        }

        Cache::put(
            self::CACHE_KEY_PREFIX . $request->phone,
            $request->phone,
            self::CACHE_TTL
        );

        return sendResponse(['status' => 'Phone verified successfully']);
    }

    /**
     * Get the cache key for a user ID (for use by role-specific update_phone services).
     */
    public static function getCacheKey(string $phone): string
    {
        return self::CACHE_KEY_PREFIX . $phone;
    }

    /**
     * Clear the verified phone cache for a user (for use after successful update).
     */
    public static function clearVerifiedPhone(int $userId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $userId);
    }
}
