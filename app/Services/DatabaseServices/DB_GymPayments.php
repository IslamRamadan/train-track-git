<?php

namespace App\Services\DatabaseServices;

use App\Models\GymPayment;

class DB_GymPayments
{
    public function find_gym_payment($order_id, $amount, $status)
    {
        return GymPayment::query()->with('package')->where([
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => $status,
        ])->first();
    }

    public function find_gym_payment_with_id($id)
    {
        return GymPayment::query()->find($id);
    }

    public function create_gym_payment($gym_id, $order_id, $amount, $package_id, $upgrade, $status = "1")
    {
        GymPayment::query()->create([
            'gym_id' => $gym_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => $status,
            'package_id' => $package_id,
            'upgrade' => $upgrade,
        ]);
    }

    public function update_gym_payment_status($gym_payment, $status)
    {
        $gym_payment->update(['status' => $status]);
    }

    public function get_gym_payment_orders($gym_id)
    {
        return GymPayment::query()->with('package')->where('gym_id', $gym_id)->get();
    }
}
