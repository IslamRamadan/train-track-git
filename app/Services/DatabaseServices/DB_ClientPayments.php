<?php

namespace App\Services\DatabaseServices;

use App\Models\ClientPayment;

class DB_ClientPayments
{
    public function createClientPayment($data)
    {
        return ClientPayment::query()->create($data);
    }

    public function getClientsPayments($coachId, $search)
    {
        return ClientPayment::query()
            ->select([
                'id',           // from clients_payments
                'client_id',
                'order_id',
                'status',
                'amount',
                'renew_days',
//                'status_text',
                'created_at'
            ])
//            ->with('client.coach_client_client')
            ->with([
                'client:id,name,email,phone',
                'client.coach_client_client:id,client_id,coach_id',
                'client.coach_client_client.coach:id,name'
            ])
            ->whereHas('client.coach_client_client', function ($q) use ($coachId) {
                $q->where('coach_id', $coachId);
            })
            ->when($search, function ($query) use ($search) {

                $query->where('order_id', 'LIKE', "%{$search}%")
                ->orWhereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

    }

    public function findClientPaymentWithOrderId(mixed $orderId)
    {
        return ClientPayment::query()
            ->where('order_id', $orderId)
            ->first();
    }

    public function updateClientPayment($payment, array $data)
    {
        $payment->update($data);
    }
}
