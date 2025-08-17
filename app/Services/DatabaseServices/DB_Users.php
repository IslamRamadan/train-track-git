<?php

namespace App\Services\DatabaseServices;

use App\Models\User;

class DB_Users
{

    public function get_user_info($id)
    {
        return User::with('coach', 'coach_client_client','client')->find($id);
    }

    public function get_user_for_delete($id)
    {
        return User::with('client','coach', 'coach_client_client', 'client_programs.exercises.log', 'program_clients', 'client_programs.comments',
            'client_programs.exercises.videos')->find($id);
    }

    public function create_user(mixed $name, mixed $email, mixed $phone, mixed $password, $due_date, $country_id, $gender_id, $user_type = "0")
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'user_type' => $user_type,
            'due_date' => $due_date,
            'country_id' => $country_id,
            'gender_id' => $gender_id,
        ]);
    }

    public function update_user(mixed $client_id, $name, $email, $phone, $country_id, $gender_id)
    {
        return User::query()->where('id', $client_id)->update([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'country_id' => $country_id,
            'gender_id' => $gender_id,
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

    public function update_user_due_date(mixed $user_id, mixed $due_date)
    {
        return User::query()->where('id', $user_id)->update([
            'due_date' => $due_date
        ]);
    }


    public function get_clients_have_not_exercises_in_date($coachId, $date, $clientHasExercisesInDate)
    {
        return User::query()
            ->whereNotIn('id', $clientHasExercisesInDate)
            ->whereHas('coach_client_client', function ($query) use ($coachId, $date) {
                $query->where('coach_id', $coachId)
                    ->where('status', "!=", "2");
        })
            ->where('user_type', "1") // Ensure we are selecting only clients
        ->get();
    }

    public function update_user_data($user, array $data)
    {
        return $user->update($data);
    }
}
