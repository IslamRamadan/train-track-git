<?php

namespace App\Services\DatabaseServices;

use App\Models\UserNotification;
use App\Models\UserNotification_token;
use App\Models\UserNotificationToken;

class DB_Notifications
{
    public function get_user_notifications($user_id)
    {
        return UserNotification::where("user_id", $user_id)->get();
    }

    public function get_num_of_user_unread_notifications($request)
    {
        return UserNotification::where("user_id", $request->user()->id)->where('state', "0")->count();
    }

//    public function get_general_notification($clientCode)
//    {
//        return General_notification::where("clientCode", $clientCode)->where('created_at', '>=', Auth::user()->created_at)->get();
//    }

//    public function storeGeneralNotification($request)
//    {
//        return General_notification::create([
//            'title' => $request->title,
//            'msg' => $request->msg,
//            "clientCode" => $request->user()->ClientCode,
//        ]);
//    }

//    public function findUserNotificationToken($id)
//    {
//        return UserNotification_token::where("user_id", $id)->first();
//    }

    public function create_user_notification($user_id, string $title, string $body)
    {
        return UserNotification::create([
            "user_id" => $user_id,
            "title" => $title,
            "notification" => $body,
            "state" => "0",
        ]);
    }

    public function updateUserNotificationStateAsRead($UserNotification)
    {
        return $UserNotification->update([
            "state" => "1",
        ]);
    }

    public function create_admin_violation_Notification($admin_id, string $title, string $body)
    {

        return Admin_violation_notification::create([
            "admin_id" => $admin_id,
            "title" => $title,
            "notification" => $body
        ]);
    }


    public function update_admin_violation_Notification_to_read(mixed $notification)
    {
        return $notification->update(['status' => "1"]);
    }

    public function delete_UserNotifications($id)
    {
        UserNotification::where('user_id', '=', $id)->delete();
    }

//    public function delete_UserNotification_tokens($id)
//    {
//        UserNotification_token::where('user_id', '=', $id)->delete();
//    }
    public function find_user_notification_token($user_id)
    {
        return UserNotificationToken::where("user_id", $user_id)->first();
    }

    public function create_user_notification_token($user_id, $token)
    {
        return UserNotificationToken::create([
            "user_id" => $user_id,
            "token" => $token,
        ]);
    }

    public function uodate_user_notification_token($userNotificationToken, $token)
    {
        $userNotificationToken->update([
            "token" => $token
        ]);
    }

}
