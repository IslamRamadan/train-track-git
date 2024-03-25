<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Notifications;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationServices
{
    public function __construct(protected ValidationServices $validation, protected DB_Notifications $DB_Notifications)
    {
    }

    public function send_notification_to_user($user_id, $title, $message)
    {
        // send notification to supplier to telling hin that thre exist user reserved appointment with him
        $userNotificationToken = $this->DB_Notifications->find_user_notification_token($user_id);
        if ($userNotificationToken) {
            $body = $message;

            $sent = $this->send($userNotificationToken->token, $title, $body);
            $res = json_decode($sent);
            if ($res && $res->success) {
                $this->DB_Notifications->create_user_notification($user_id, $title, $body);
            }
        }
    }

    public function list_user_notifications($request)
    {
        $user_id = $request->user()->id;
        $userNotifications = $this->DB_Notifications->get_user_notifications(user_id: $user_id);
//        $generalNotifications = $this->DB_Notifications->get_general_notification($req->clientCode);
        $notifications_list = [];
        foreach ($userNotifications as $userNotification) {
            $array = [
                "title" => $userNotification['title'],
                "notification" => $userNotification['notification'],
                "is_read" => $userNotification['state'],
                "date" => Carbon::parse($userNotification['created_at'])->toDateString(),
                "time" => Carbon::parse($userNotification['created_at'])->toTimeString(),
            ];
            $this->DB_Notifications->updateUserNotificationStateAsRead($userNotification);
            $notifications_list[] = $array;
        }
        try {
            $this->send_notification_to_user($user_id, "train track", "Hello notification");
        } catch (\Exception $exception) {
            dd($exception);
        }
//        foreach ($generalNotifications as $generalNotification) {
//            $general = [
//                "title" => $generalNotification['title'],
//                "notification" => $generalNotification['msg'],
//                "created_at" => $generalNotification['created_at'],
//            ];
//            array_push($notifications_list, $general);
//
//        }
        // to order notification from new to old
//        $sortedArray = collect($notifications_list)->sortByDesc('created_at')->values()->all();
        return response()->json($notifications_list);
    }

    // sending push message to single user by firebase reg id
    public function send($to, $title, $body)
    {
        $fields = array(
            'to' => $to, //token
            'notification' => ["title" => $title, "body" => $body],
            'priority' => 'high',
            'content_available' => true,
        );
        return $this->sendPushNotification($fields);
    }


    // Sending message to a topic by topic name
    public function sendToTopic($to, $title, $body)
    {
        $fields = array(
            'to' => '/topics/' . $to,
            'notification' => ["title" => $title, "body" => $body],
            'priority' => 'high',
        );

        return $this->sendPushNotification($fields);
    }

    // sending push message to multiple users by firebase registration ids
    public function sendMultiple($registration_ids, $title, $body)
    {
        $fields = array(
            'to' => json_encode($registration_ids), //tokens array
            'notification' => ["title" => $title, "body" => $body],
        );

        return $this->sendPushNotification($fields);
    }

    // function makes curl request to firebase servers
    public function sendPushNotification($fields)
    {
        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
//            FIREBASE_API_KEY we take from mobile team
            'Authorization: key=' . env('FIREBASE_API_KEY'),
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarily
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        return $result;
    }

    public function callSendToTopic(Request $request)
    {
//        $this->validation->sendNotificationToTopic($request);
//        concatenate with tested to not send this notification for online users
        $topicName = $request->user()->ClientCode . "tested";
        $sent = $this->sendToTopic($topicName, $request->title, $request->msg);
        if ($sent) {
            $this->DB_Notifications->storeGeneralNotification($request);
        }
        return redirect()->back()->with("msg", "notification successfully sent");
    }
}
