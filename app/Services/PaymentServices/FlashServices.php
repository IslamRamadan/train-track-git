<?php

namespace App\Services\PaymentServices;

class FlashServices
{
    private function generateAccessToken()
    {

    }

    public function createPaymentLink($integrationId, $aggregatorOrderId, $amount, $customerPhone)
    {
        $accessToken = $this->generateAccessToken();

    }
}
