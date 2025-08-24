<?php

namespace App\Services\PaymentServices;

use App\Services\SendHttpRequest;

class FlashServices
{
    public function __construct(protected SendHttpRequest $httpClient)
    {
    }

    public function generateAccessToken(): ?string
    {
        $response = $this->httpClient->sendRequest(
            'POST',
            config('flash.api.base_url') . "v1/auth/token",
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . config('flash.api.basic_token'),
            ]
        );
        if ($response['success'] && isset($response['data']['access_token'])) {
            return $response['data']['access_token'];
        }

        return null;
    }

    public function createPaymentLink($integrationId, $aggregatorOrderId, $amount, $customerPhone)
    {
        $accessToken = $this->generateAccessToken();
        if ($accessToken) {
            $response = $this->httpClient->sendRequest(
                'POST',
                config('flash.api.base_url') . "v1/orders",
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                [
                    "integrationId" => $integrationId,
                    "aggregatorOrderId" => $aggregatorOrderId,
                    "description" => "Subscription Payment",
                    "customer" => [
                        "phone" => $customerPhone
                    ],
                    "merchantName" => "Train Track",
                    "webEnabled" => true,
                    "amountCents" => $amount * 100, // Convert to cents
                    "currency" => "EGP",
                    "notifiers" => [[
                        "phoneNumber" => $customerPhone,
                        "type" => "sms"
                    ]],
                    "additionalInfo" => [
                        ["key" => "product-code", "value" => "Train Track"]
                    ]
                ]
            );
            if ($response['success'] && isset($response['data']['paymentLink'])) {
                return ['paymentLink' => $response['data']['paymentLink'],
                    'orderId' => $response['data']['order']['id']];
            }
        }
        return null;
    }


}
