<?php

namespace App\Services\PaymentServices;

use PayMob\Facades\PayMob;

class PaymentServices
{

    public function pay($amount, $full_name, $email, $description)
    {
        $auth = PayMob::AuthenticationRequest();
        $payment_link_image = asset('images/logo.png');
        return PayMob::createPaymentLink($auth->token, $payment_link_image, $amount * 100, $full_name, $email, $description);
    }

    public function checkout_processed($request)
    {
        $request_hmac = $request->hmac;
        $calc_hmac = PayMob::calcHMAC($request);
        dd($request->all(), $request_hmac == $calc_hmac);
        if ($request_hmac == $calc_hmac) {
            $order_id = $request->obj['order']['merchant_order_id'];
            $amount_cents = $request->obj['amount_cents'];
            $transaction_id = $request->obj['id'];

            $order = Order::find($order_id);

            if ($request->obj['success'] == true && ($order->total_price * 100) == $amount_cents) {
                $order->update([
                    'payment_status' => 'finished',
                    'transaction_id' => $transaction_id
                ]);
            } else {
                $order->update([
                    'payment_status' => "failed",
                    'transaction_id' => $transaction_id
                ]);
            }
        }
    }
}
