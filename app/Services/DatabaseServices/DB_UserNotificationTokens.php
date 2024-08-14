<?php

namespace App\Services\DatabaseServices;

use App\Models\User;
use App\Models\UserNotificationToken;

class DB_UserNotificationTokens
{


    public function get_users_tokens($user_type)
    {
        return UserNotificationToken::query()
            ->when($user_type == "client", function ($q) {
                $q->whereHas('user', function ($query) {
                    $query->where('user_type', '1');
                });
            })
            ->when($user_type == "coach", function ($q) {
                $q->whereHas('user', function ($query) {
                    $query->where('user_type', '0');
                });
            })
            ->pluck('token')->toArray();
    }
}
