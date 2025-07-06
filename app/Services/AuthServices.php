<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Mail\VerifyMail;
use App\Models\RequestInfoLog;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_GymJoinRequest;
use App\Services\DatabaseServices\DB_Notifications;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_Settings;
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
                                protected DB_Settings $DB_Settings,
                                protected DB_OneToOneProgram $DB_OneToOneProgram, protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises,
                                protected DB_GymJoinRequest  $DB_GymJoinRequest,
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

            if ($user->user_type == "0") {
                if ($user->coach->status == "0") {
                return sendError("Blocked Coach");
                }
                if ($user->email_verified_at == null) {
                    return sendError("Email is not verified");
                }
            }
            $version = $this->DB_Settings->get_version();
            $this->check_user_notification_token(token: $notification_token, user_id: $user->id);
            return sendResponse($this->user_info_arr($user, $version));
        } else {
            // failure to authenticate
            return sendError("Wrong credentials");
        }
    }

    public function client_register($request)
    {
        $this->validationServices->client_register($request);
        RequestInfoLog::query()->create([
            "user_id" => null,
            "ip" => $request->ip(),
            "user_agent" => $request->header('User-Agent'),
            "route" => $request->getPathInfo(),
            "body" => $request->getContent(),
        ]);
        $name = $request['name'];
        $email = $request['email'];
        $phone = $request['phone'];
        $password = $request['password'];
        $country_id = $request['country_id'];
        $gender_id = $request['gender_id'];
        $pending_client = $this->DB_PendingClients->get_pending_client_by_email($email);
        $coach_id = $pending_client->coach_id;
        $coach_info = $this->DB_Users->get_user_info($coach_id);
        DB::beginTransaction();
        $client = $this->DB_Clients->create_client($name, $email, $phone, $password, $country_id, $gender_id);
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
        $country_id = $request['country_id'];
        $gender_id = $request['gender_id'];

        $due_date = Carbon::today()->addMonth()->toDateString();
        DB::beginTransaction();
        $user = $this->DB_Users->create_user($name, $email, $phone, $password, $due_date, $country_id, $gender_id);
        $this->DB_Coaches->create_coach($gym, $speciality, $certificates, $user->id);
        $email_is_invited_to_gym = $this->DB_GymJoinRequest->find_email_is_invited_to_gym($email);
        if ($email_is_invited_to_gym) {
            $this->DB_GymJoinRequest->update_join_request($email_is_invited_to_gym, $user->id);
        }
        DB::commit();
        try {
            Mail::to($email)->send(new VerifyMail(name: $name, user_id: $user->id));
        } catch (\Exception $exception) {
        }
        return sendResponse(['message' => "Coach Created Successfully"]);
    }

    /**
     * @param $user
     * @param $version
     * @return array that has id , email , name , phone , user_type , token
     */
    public function user_info_arr($user, $version): array
    {
        return [
            "id" => $user->id,
            "email" => $user->email,
            "name" => $user->name,
            "phone" => $user->phone,
            "version" => $version,
            "user_type" => $user->user_type_text,//Coach or Athlete
            "due_date" => $user->due_date??"",
            "token" => $user->createToken('appToken')->accessToken,
        ];
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

    public function update_version($request)
    {
        $this->validationServices->update_version($request);
        $version = $request->version;
        $this->DB_Settings->update_version($version);
        return sendResponse(['message' => "Version Updated Successfully"]);
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
