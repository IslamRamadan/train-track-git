<?php

namespace App\Services\DatabaseServices;

use App\Models\PendingClient;

class DB_PendingClients
{

    public function create_pending_client(mixed $coach_id, mixed $email)
    {
        return PendingClient::query()->create([
            'coach_id' => $coach_id,
            'email' => $email,
        ]);
    }

    public function delete_pending_client(mixed $email)
    {
        return PendingClient::where('email', $email)->delete();
    }

    public function get_pending_client_by_email(mixed $email)
    {
        return PendingClient::where('email', $email)->first();
    }

    public function get_coach_pending_clients(mixed $coach_id, $search)
    {
        return PendingClient::where('coach_id', $coach_id)
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('email', 'LIKE', '%' . $search . '%');
            })
            ->get();
    }

    public function get_pending_clients(mixed $coach_id)
    {
        return PendingClient::where('coach_id', $coach_id)->count();
    }

    /**
     * Get total pending clients count for multiple coaches in one query
     */
    public function get_pending_clients_count_for_coaches(array $coach_ids): int
    {
        if (empty($coach_ids)) {
            return 0;
        }
        return PendingClient::query()
            ->whereIn('coach_id', $coach_ids)
            ->count();
    }
}
