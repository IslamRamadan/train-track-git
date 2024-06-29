<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_Notifications;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class CoachServices
{
    public function __construct(protected ValidationServices          $validationServices,
                                protected DB_Clients                  $DB_Clients,
                                protected DB_Coaches                  $DB_Coaches,
                                protected DB_Users                    $DB_Users,
                                protected DB_ExerciseLog              $DB_ExerciseLog,
                                protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises,
                                protected DB_Notifications            $DB_Notifications,
                                protected DB_Packages    $DB_Packages,
                                protected DB_UserPayment $DB_UserPayment,
                                protected PaymentServices $paymentServices,
    )
    {
    }


    /**
     * Get coach some statistics like : number of clients,
     * number of workouts done
     *
     * @param $request
     * @return JsonResponse
     */
    public function coach_dashboard($request)
    {
        $coach_id = $request->user()->id;
//        number of clients
        $number_of_clients = $this->DB_Clients->get_coach_clients_count($coach_id);

//        number of clients active
        $number_of_active_clients = $this->DB_Clients->get_coach_clients_count($coach_id, "1");

        $coach_info = $this->DB_Users->get_user_info($coach_id);

//        Workouts done today
        $today = Carbon::now()->toDateString();
        list($clients_activity, $done_workout) = $this->clients_activities($today, $coach_id);

//        get the today's logs of the coach clients
        $clients_logs_today = $this->DB_ExerciseLog->list_coach_clients_logs_today($coach_id, $today);
        $list_logs_arr = $this->list_logs_arr($clients_logs_today);
        $unread_notifications = $this->DB_Notifications->user_has_unread_notifications($coach_id);
//        Daily Activity done exercises/total workouts
        return sendResponse([
            'coach_id' => $coach_info->id,
            'coach_name' => $coach_info->name,
            'coach_phone' => $coach_info->phone,
            'coach_email' => $coach_info->email,
            'coach_gym' => $coach_info->coach->gym,
            'coach_speciality' => $coach_info->coach->speciality,
            'coach_certificates' => $coach_info->coach->certificates,
            'total_clients' => $number_of_clients,
            'active_clients' => $number_of_active_clients,
            'done_workouts' => $done_workout,
            'clients_activity' => $clients_activity,
            'today_logs' => $list_logs_arr,
            'unread_notifications' => $unread_notifications ? "1" : "0"
        ]);
    }

    public function get_clients_activities($request)
    {
        $this->validationServices->get_clients_activities($request);

        $coach_id = $request->user()->id;
        $date = $request->date;
        list($clients_activity) = $this->clients_activities($date, $coach_id);
        return sendResponse($clients_activity);
    }
    public function update_info($request)
    {
        $this->validationServices->coach_update_info($request);

        $coach_id = $request->user()->id;
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $gym = $request->gym;
        $speciality = $request->speciality;
        $certificates = $request->certificates;

        $this->DB_Users->update_user($coach_id, $name
            , $email
            , $phone);
        $this->DB_Coaches->update_coach($coach_id, $gym, $speciality, $certificates);

        return sendResponse(['message' => "Coach information updated successfully"]);

    }

    public function list_client_logs($request)
    {
        $this->validationServices->list_client_logs($request);
        $client_id = $request->client_id;

        $logs = $this->DB_ExerciseLog->list_cient_logs($client_id);
        return sendResponse($this->list_logs_arr($logs));

    }

    public function update_due_date($request)
    {
        $this->validationServices->update_due_date($request);
        $client_id = $request->client_id;
        $due_date = $request->due_date;
        $this->DB_Users->update_user_due_date($client_id, $due_date);
        return sendResponse(['message' => "Client due date updated successfully"]);
    }

    public function create_payment_link($request)
    {
        $this->validationServices->create_payment_link($request);

        $coach_id = $request->coach_id;
        $user = $this->DB_Users->get_user_info($coach_id);

        if ($user->user_type != "0") {
            return sendError("This user is not a coach");
        }

        $package_id = $user->coach->package_id;

        $package = $this->DB_Packages->find_package($package_id);

        $payment_description = $package->name . " payment with " . $package->clients_limit . " clients limit.";

        try {
            $payment = $this->paymentServices->pay(amount: $package->amount, full_name: $user->name, email: $user->email, description: $payment_description);
            $payment_url = $payment->client_url;
            $order_id = $payment->order;
            $payment_amount = $payment->amount_cents / 100;
            $this->DB_UserPayment->create_user_payment(coach_id: $user->id, order_id: $order_id, amount: $payment_amount);
            return sendResponse(["payment_url" => $payment_url]);
        } catch (\Exception $exception) {
            return sendError("Payment failed,Please try again later.");
        }

    }

    public function check_package_limit($request)
    {
        $coach_id = $request->user()->id;

        if ($request->user()->user_type != "0") {
            return sendError("This user is not a coach");
        }
        list($coach_package, $upgrade) = $this->get_coach_package($coach_id);
        return sendResponse([
            "upgrade" => $upgrade,
            "package_id" => $coach_package->id,
            "package_name" => $coach_package->name,
            "package_amount" => $coach_package->amount,
            "package_clients_limit" => $coach_package->clients_limit,
        ]);
    }
    public function list_logs_arr(Collection|array $logs)
    {
        $logs_arr = [];
        if ($logs) {
            foreach ($logs as $log) {
                $single_log_arr = [];
                $single_log_arr['client_id'] = $log->exercise->one_to_one_program->client_id;
                $single_log_arr['client_name'] = $log->exercise->one_to_one_program->client->name;
                $single_log_arr['program_id'] = $log->exercise->one_to_one_program->id;
                $single_log_arr['program_name'] = $log->exercise->one_to_one_program->name;
                $single_log_arr['exercise_id'] = $log->exercise->id;
                $single_log_arr['exercise_name'] = $log->exercise->name;
                $single_log_arr['exercise_description'] = $log->exercise->description;
                $single_log_arr['log_id'] = $log->id;
                $single_log_arr['log_sets'] = $log->sets;
                $single_log_arr['log_details'] = $log->details;
                $single_log_arr['log_date'] = $log->created_at->format("Y-m-d");
                $single_log_arr['log_time'] = $log->created_at->format("H:i:s");
                $logs_arr[] = $single_log_arr;
            }
        }
        return $logs_arr;
    }

    /**
     * @param string $today
     * @param mixed $coach_id
     * @return array
     */
    public function clients_activities(string $today, mixed $coach_id): array
    {
        $get_workouts_done_today = $this->DB_OneToOneProgramExercises->get_workouts_done_today($today, $coach_id);
        $clients_activity = [];
        $done_workout = 0;
        if (count($get_workouts_done_today) > 0) {
            foreach ($get_workouts_done_today as $client_today_exercises) {
                $client_info = $this->DB_Users->get_user_info($client_today_exercises[0]->one_to_one_program->client_id);
                $done_exercises = 0;
                foreach ($client_today_exercises as $exercise) {
                    if ($exercise->is_done == "1") {
                        $done_exercises++;
                    }
                }
                if (count($client_today_exercises) == $done_exercises) {
                    $done_workout++;
                }
                $clients_activity[] = [
                    'client_id' => $client_info->id,
                    'client_name' => $client_info->name,
                    'today_exercises' => count($client_today_exercises),
                    'done_exercises' => $done_exercises,
                ];
            }
        }
        return array($clients_activity, $done_workout);
    }

    /**
     * @param mixed $coach_id
     * @return array
     */
    public function get_coach_package(mixed $coach_id): array
    {
        $active_clients = $this->DB_Clients->get_active_clients($coach_id);
        $get_coach_info = $this->DB_Coaches->get_coach_info($coach_id);
        $coach_package = $get_coach_info->package;
        $upgrade = false;

        if ($active_clients + 1 > $coach_package->clients_limit) {
//          the coach now will exceed the client limit
            $upgrade = true;
//          get the higher package
            $coach_package = $this->DB_Packages->get_appropriate_package($active_clients);
        }
        return array($coach_package, $upgrade);
    }
}
