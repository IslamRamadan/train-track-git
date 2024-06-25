<?php

namespace App\Services\PaymentServices;

use App\Services\Order;
use PayMob\Facades\PayMob;

class PaymentServices
{

    public function pay($total_price, $order_id)
    {
        $auth = PayMob::AuthenticationRequest();
        $payment_link_image = asset('images/logo.png');
        $amount_cents = "1350";
        $full_name = "Islam Ramadan";
        $email = "eslam.iniesta@gmail.com";
        $description = "Short Description";
        return PayMob::createPaymentLink($auth->token, $payment_link_image, $amount_cents, $full_name, $email, $description);
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
