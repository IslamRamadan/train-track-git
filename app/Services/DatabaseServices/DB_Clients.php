<?php

namespace App\Services\DatabaseServices;

use App\Models\CoachClient;
use App\Models\User;

class DB_Clients
{

    public function get_all_clients(mixed $coach_id, mixed $search, $status)
    {
        return CoachClient::with('coach', 'client')->where(['coach_id' => $coach_id])
            ->when(!empty($search), function ($q) use ($search) {
                $q->whereHas('client', function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%');
                });
            })
            ->when($status == 'pending', function ($q) use ($search) {
                $q->where('status', '0');
            })
            ->when($status == 'active', function ($q) use ($search) {
                $q->where('status', '1');
            })
            ->when($status == 'archived', function ($q) use ($search) {
                $q->where('status', '2');
            })
            ->get();
    }

    public function create_client(mixed $name, mixed $email, mixed $phone, mixed $password)
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'user_type' => "1",
        ]);
    }

    public function assign_client_to_coach(mixed $coach_id, mixed $client_id, $status = "1")
    {
        CoachClient::query()->create([
            'coach_id' => $coach_id,
            'client_id' => $client_id,
            'status' => $status,
        ]);
    }

    public function get_coach_clients_count($coach_id, $status = null)
    {
        return CoachClient::query()->where(['coach_id' => $coach_id])
            ->when($status != null, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->count();
    }

    public function archive_client(mixed $client_id, $status)
    {
        return CoachClient::query()->where(['client_id' => $client_id])->update([
            'status' => $status
        ]);
    }

    public function verify_client_id($coach_id, $client_id)
    {
        return CoachClient::query()->where(['coach_id' => $coach_id, 'client_id' => $client_id])->exists();
    }

    public function find_coach_id($client_id)
    {
        return CoachClient::query()->where(['client_id' => $client_id])->first();
    }

}
