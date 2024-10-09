<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Notifications;
use App\Services\DatabaseServices\DB_UserNotificationTokens;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;

class NotificationServices
{
    public function __construct(protected ValidationServices        $validation,
                                protected DB_Notifications          $DB_Notifications,
                                protected DB_UserNotificationTokens $DB_UserNotificationTokens
    )
    {
    }

    public function send_notification_to_user($user_id, $title, $message, $payload = [])
    {
        // send notification to supplier to telling hin that thre exist user reserved appointment with him
        $this->DB_Notifications->create_user_notification($user_id, $title, $message);

        $userNotificationToken = $this->DB_Notifications->find_user_notification_token($user_id);
        if ($userNotificationToken) {
            $body = $message;

          $this->send($userNotificationToken->token, $title, $body,$payload);

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

    public function send($user_token, $title, $description, $payloadData = [])
    {
        $projectId = "wod-connect";

        $credentialsFilePath = storage_path('app/json/wod-connect-firebase-adminsdk-xour6-1ca0510951.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();

        $token = $client->getAccessToken();
        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $user_token,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
                "android" => [
                    "priority" => "high" // HTTP v1 protocol
                ],
                "data" => $payloadData
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return $response;
    }

    public function sendPushNotification($fields)
    {
        // Set POST variables
//        $url = 'https://fcm.googleapis.com/fcm/send';
        $url = 'https://fcm.googleapis.com/v1/projects/wod-connect/messages:send';

        $headers =
            [
                'Authorization' => 'Bearer ' . env('FIREBASE_API_KEY'),
                'Content-Type' => 'application/json'
            ];
        dd($url, $headers);
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

//    public function send_coaches_notification(Request $request)
//    {
//        $this->validation->send_coaches_notification($request);
//        $message = $request->message;
//        $title = $request->title;
//        $user_type = $request->user_type;
//        $tokens = $this->DB_UserNotificationTokens->get_users_tokens($user_type);
//        try {
//            foreach ($tokens as $token) {
//                $this->send($token, $title, $message);
//            }
//        } catch (\Exception $exception) {
//            return sendError('Error');
//        }
//        return sendResponse(['message' => "Notification sent successfully"]);
//    }
}
