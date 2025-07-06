<?php

namespace App\Services\DatabaseServices;

use App\Models\Client;
use App\Models\CoachClient;
use App\Models\User;

class DB_Clients
{

    public function get_all_clients(mixed $coach_id, mixed $search, $status)
    {
        return CoachClient::with('coach', 'client.client')
            ->when(!empty($search), function ($q) use ($search) {
                $q->whereHas('client', function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('client', function ($query2) use ($search) {
                            $query2->where('tag', 'LIKE', '%' . $search . '%');
                        });
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
            ->where(['coach_id' => $coach_id])
            ->get();
    }

    public function list_client_details(mixed $coach_id, $client_id)
    {
        return CoachClient::with('coach', 'client.client')
            ->where(['coach_id' => $coach_id, 'client_id' => $client_id])
            ->first();
    }

    public function get_active_clients_between_dates(mixed $coach_id, mixed $search, $date_from, $date_to)
    {
        return CoachClient::query()
            ->with('coach', 'client.client')
            ->when(!empty($search), function ($q) use ($search) {
                $q->whereHas('client', function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('client', function ($query2) use ($search) {
                            $query2->where('tag', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->whereHas('client', function ($q) use ($date_from, $date_to) {
                $q->whereBetween('last_active', [$date_from, $date_to]);
            })
            ->where(['coach_id' => $coach_id])
            ->get();
    }

    public function create_client(mixed $name, mixed $email, mixed $phone, mixed $password, $country_id, $gender_id)
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'country_id' => $country_id,
            'gender_id' => $gender_id,
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

    public function get_active_clients($coach_id)
    {
        return CoachClient::query()->where(['coach_id' => $coach_id, "status" => "1"])->count();
    }


    public function get_client_info(mixed $client_id)
    {
        return Client::query()
            ->where('user_id', $client_id)
            ->first();
    }

    public function update_client_payment_link(mixed $client_info, mixed $payment_link)
    {
        $client_info->update([
            'payment_link' => $payment_link
        ]);
    }

    public function create_client_payment_link(mixed $client_id, mixed $payment_link)
    {
        Client::query()
            ->create([
                'user_id' => $client_id,
                'payment_link' => $payment_link
            ]);
    }

    public function update_client_tag(mixed $client_info, mixed $payment_link)
    {
        $client_info->update([
            'tag' => $payment_link
        ]);
    }
public function update_client_info(mixed $client_info, mixed $data)
    {
        $client_info->update($data);
    }
    public function create_client_data(mixed $data)
    {
        Client::query()
            ->create($data);
    }
    public function create_client_tag(mixed $client_id, mixed $payment_link)
    {
        Client::query()
            ->create([
                'user_id' => $client_id,
                'tag' => $payment_link
            ]);
    }

}
