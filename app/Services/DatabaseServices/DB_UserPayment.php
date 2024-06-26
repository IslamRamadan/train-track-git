<?php

namespace App\Services\DatabaseServices;

use App\Models\UsersPayment;

class DB_UserPayment
{
    public function find_user_payment($coach_id, $order_id, $amount)
    {
        UsersPayment::query()->where([
            'coach_id' => $coach_id,
            'order_id' => $order_id,
            'amount' => $amount,
        ])->first();
    }

    public function create_user_payment($coach_id, $order_id, $amount, $status = "1")
    {
        UsersPayment::query()->create([
            'coach_id' => $coach_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    public function update_user_payment_status($user_payment, $status)
    {
        $user_payment->update(['status' => $status]);
    }
}
