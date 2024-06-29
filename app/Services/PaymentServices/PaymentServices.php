<?php

namespace App\Services\PaymentServices;

use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use PayMob\Facades\PayMob;

class PaymentServices
{
    public function __construct(protected DB_UserPayment $DB_UserPayment, protected DB_Users $DB_Users)
    {
    }

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

    public function checkout_response($request)
    {
        if ($request->success == "true") {
            $order_id = $request->order;
            $amount = $request->amount_cents / 100;
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "1");
            if ($get_the_order) {
                $get_the_coach = $this->DB_Users->get_user_info($get_the_order->coach_id);
                $coach_due_date = Carbon::parse($get_the_coach->due_date);
                if ($coach_due_date->lt(Carbon::today())) {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                } else {
                    $new_due_date = $coach_due_date->addMonth()->toDateString();
                }
                $this->DB_Users->update_user_due_date($get_the_coach->id, $new_due_date);
                $this->DB_UserPayment->update_user_payment_status($get_the_order, "2");
                $success_msg = __('translate.PaymentSuccessMsg') . $new_due_date;
                return view('payment.payment_done', compact('success_msg', 'order_id'));
            } else {
                return view('payment.payment_failed');
            }
        }
        return view('payment.payment_failed');
    }
}
