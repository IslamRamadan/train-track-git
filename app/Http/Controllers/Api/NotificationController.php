<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationServices;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationServices;

    public function __construct(NotificationServices $notificationServices)
    {
        $this->notificationServices = $notificationServices;
    }

    public function list_notifications(Request $request)
    {
        return $this->notificationServices->list_user_notifications($request);
    }

    public function callSendToTopic(Request $request)
    {
        return $this->notificationServices->callSendToTopic($request);
    }
//    public function send_coaches_notification(Request $request)
//    {
//        return $this->notificationServices->send_coaches_notification($request);
//    }
}
