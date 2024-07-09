<?php

namespace App\Services\DatabaseServices;

use App\Models\UsersPayment;

class DB_UserPayment
{
    public function find_user_payment($order_id, $amount, $status)
    {
        return UsersPayment::query()->with('package')->where([
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => $status,
        ])->first();
    }

    public function create_user_payment($coach_id, $order_id, $amount, $package_id,$upgrade, $status = "1", $first_pay = "0")
    {
        UsersPayment::query()->create([
            'coach_id' => $coach_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => $status,
            'first_pay' => $first_pay,
            'package_id' => $package_id,
            'upgrade' => $upgrade,
        ]);
    }

    public function update_user_payment_status($user_payment, $status)
    {
        $user_payment->update(['status' => $status]);
    }
}
