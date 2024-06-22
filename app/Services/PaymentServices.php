<?php

namespace App\Services;

use PayMob\Facades\PayMob;

class PaymentServices
{

    public function pay($total_price, $order_id)
    {
        $auth = PayMob::AuthenticationRequest();
        $order = PayMob::OrderRegistrationAPI([
            'auth_token' => $auth->token,
            'amount_cents' => $total_price * 100, //put your price
            'currency' => 'EGP',
            'delivery_needed' => false, // another option true
            'merchant_order_id' => $order_id, //put order id from your database must be unique id
            'items' => [] // all items information or leave it empty
        ]);
        $PaymentKey = PayMob::PaymentKeyRequest([
            'auth_token' => $auth->token,
                    'amount_cents' => 150 * 100, //put your price
            'currency' => 'EGP',
            'order_id' => $order->id,
            "billing_data" => [ // put your client information
                "apartment" => "803",
                "email" => "claudette09@exa.com",
                "floor" => "42",
                "first_name" => "Clifford",
                "street" => "Ethan Land",
                "building" => "8028",
                "phone_number" => "+86(8)9135210487",
                "shipping_method" => "PKG",
                "postal_code" => "01898",
                "city" => "Jaskolskiburgh",
                "country" => "CR",
                "last_name" => "Nicolas",
                "state" => "Utah"
            ]
        ]);

        return $PaymentKey->token;
    }

    public function checkout_processed($request)
    {
        $request_hmac = $request->hmac;
        $calc_hmac = PayMob::calcHMAC($request);
        dd($request->all());
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
