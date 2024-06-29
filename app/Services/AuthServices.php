<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Notifications;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthServices
{
    public function __construct(protected DB_Users           $DB_Users,
                                protected ValidationServices $validationServices,
                                protected DB_Clients         $DB_Clients,
                                protected DB_PendingClients  $DB_PendingClients,
                                protected DB_Coaches         $DB_Coaches,
                                protected DB_Notifications   $DB_Notifications,
                                protected DB_OneToOneProgram $DB_OneToOneProgram, protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises
    )
    {
    }

    public function login($request)
    {
        $this->validationServices->login($request);
        $notification_token = $request['notification_token'];
        if ($request->phone) {
            $verify = Auth::attempt(['phone' => $request->phone, 'password' => $request->password]);
        } else {
            $verify = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
        }
        if ($verify) {
            // successfully authenticated
            $user = $this->DB_Users->get_user_info(Auth::user()->id);

            if ($user->user_type == "1" && $user->coach_client_client->status == "2") {
                return sendError("Archived client");
            }
            if ($user->user_type == "0" && $user->coach->status == "0") {
                return sendError("Blocked Coach");
            }
            if ($user->user_type == "0") {
                $due_date = Carbon::parse($user->due_date);
            } else {
                $coach_id = $user->coach_client_client->coach_id;
                $client_coach = $this->DB_Users->get_user_info($coach_id);
                $due_date = Carbon::parse($client_coach->due_date);
            }
            if ($due_date->lt(Carbon::today())) {
                return sendError("Coach subscription expired", 401);
            }
            $this->check_user_notification_token(token: $notification_token, user_id: $user->id);
            return sendResponse($this->user_info_arr($user));
        } else {
            // failure to authenticate
            return sendError("Wrong credentials");
        }
    }

    public function client_register($request)
    {
        $this->validationServices->client_register($request);
        $name = $request['name'];
        $email = $request['email'];
        $phone = $request['phone'];
        $password = $request['password'];
        $pending_client = $this->DB_PendingClients->get_pending_client_by_email($email);
        $coach_id = $pending_client->coach_id;
        $coach_info = $this->DB_Users->get_user_info($coach_id);
        DB::beginTransaction();
        $client = $this->DB_Clients->create_client($name, $email, $phone, $password);
//        delete email from pending clients
        $this->DB_PendingClients->delete_pending_client($email);
//        create coach_clients record
        $this->DB_Clients->assign_client_to_coach($coach_id, $client->id);

        $oto_program = $this->DB_OneToOneProgram->create_one_to_program("Welcome " . $name,
            $name . " welcome program", $client->id, $coach_id);

        $this->DB_OneToOneProgramExercises->create_one_to_one_program_exercises("Welcome " . $name,
            "", "", 1, Carbon::today()->toDateString(), $oto_program->id);
        DB::commit();
        return sendResponse(['message' => "Client Created Successfully and added to coach " . $coach_info->name]);
    }

    public function coach_register($request)
    {
        $this->validationServices->coach_register($request);
        $name = $request['name'];
        $email = $request['email'];
        $phone = $request['phone'];
        $password = $request['password'];
        $gym = $request['gym'];
        $speciality = $request['speciality'];
        $certificates = $request['certificates'];
        $due_date = Carbon::today()->addMonth()->toDateString();
        $user = $this->DB_Users->create_user($name, $email, $phone, $password, $due_date);
        $this->DB_Coaches->create_coach($gym, $speciality, $certificates, $user->id);
        return sendResponse(['message' => "Coach Created Successfully"]);
    }

    /**
     * @param $user
     * @return array that has id , email , name , phone , user_type , token
     */
    public function user_info_arr($user): array
    {
        $success = [
            "id" => $user->id,
            "email" => $user->email,
            "name" => $user->name,
            "phone" => $user->phone,
            "user_type" => $user->user_type_text,//Coach or Athlete
            "due_date" => $user->due_date??"",
            "token" => $user->createToken('appToken')->accessToken,
        ];
        return $success;
    }

    public function change_password($request)
    {
        $this->validationServices->change_password($request);
        $check_password = Hash::check($request->old_password, $request->user()->password);
        if ($check_password) {

            $this->DB_Users->update_user_password($request->user(), $request->new_password);
            return sendResponse(['message' => "Password Changed Successfully"]);
        }
        return sendError("Wrong password");
    }

    private function check_user_notification_token($token, $user_id)
    {
        if ($token) {
            $userNotificationToken = $this->DB_Notifications->find_user_notification_token($user_id);
            if ($userNotificationToken) {
                $this->DB_Notifications->uodate_user_notification_token($userNotificationToken, $token);

            } else {
                $this->DB_Notifications->create_user_notification_token($user_id, $token);
            }
        }

    }

    public function forget_password($request)
    {
        $this->validationServices->forget_password($request);
        $email = $request['email'];
        $user = $this->DB_Users->find_user_by_email($email);
        $new_password = $this->generate_random_password();
        $this->DB_Users->update_user_password(user: $user, new_password: $new_password);

        $user_name = $user->f_name . ' ' . $user->l_name;

        Mail::to($email)->send(new ResetPasswordMail(email: $email, name: $user_name, password: $new_password));

        $response = [
            'message' => 'The new password sent to your email'
        ];
        return response()->json($response, 201);

    }

    private function generate_random_password()
    {
        $password = "";

        for ($i = 0; $i < 12; $i++) { // Change 10 to your desired length
            $char = chr(mt_rand(48, 90)); // Range for alphanumeric characters (48-57: numbers, 65-90: uppercase letters)
            if (preg_match("/[A-Z0-9!@#$%^&*()_-]/", $char)) { // Filter out unwanted characters
                $password .= $char;
            }
        }
        return $password;
    }
}
