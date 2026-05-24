<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmsServices\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    public function __construct(protected PhoneVerificationService $phoneVerificationService)
    {
    }

    public function send_phone_otp(Request $request): JsonResponse
    {
        return $this->phoneVerificationService->send_phone_otp($request);
    }

    public function verify_phone_otp(Request $request): JsonResponse
    {
        return $this->phoneVerificationService->verify_phone_otp($request);
    }
}
