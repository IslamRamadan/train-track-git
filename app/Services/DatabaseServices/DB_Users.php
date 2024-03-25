<?php

namespace App\Services\DatabaseServices;

use App\Models\User;

class DB_Users
{

    public function get_user_info($id)
    {
        return User::with('coach', 'coach_client_client')->find($id);
    }

    public function create_user(mixed $name, mixed $email, mixed $phone, mixed $password, $user_type = "0")
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'user_type' => $user_type,
        ]);
    }

    public function update_user(mixed $client_id, $name, $email, $phone)
    {
        return User::query()->where('id', $client_id)->update([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    public function update_user_password($user, $new_password)
    {
        return $user->update(['password' => $new_password]);
    }

    public function find_user_by_email(mixed $email)
    {
        return User::query()->where('email', $email)->first();
    }
}
