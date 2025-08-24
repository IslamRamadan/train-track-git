<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'status' => $this->status_text,
            'client' => [
                'name' => $this->client->name ?? null,
                'email' => $this->client->email ?? null,
                'phone' => $this->client->phone ?? null,
            ]
        ];
    }
}
