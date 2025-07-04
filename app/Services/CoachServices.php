<?php

namespace App\Services;

use App\Models\RequestInfoLog;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_Notifications;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_OtoExerciseComments;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachServices
{
    public function __construct(protected ValidationServices          $validationServices,
                                protected DB_Clients                  $DB_Clients,
                                protected DB_Coaches                  $DB_Coaches,
                                protected DB_Users                    $DB_Users,
                                protected DB_ExerciseLog              $DB_ExerciseLog,
                                protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises,
                                protected DB_Notifications            $DB_Notifications,
                                protected DB_Packages                 $DB_Packages,
                                protected DB_UserPayment              $DB_UserPayment,
                                protected PaymentServices             $paymentServices,
                                protected DB_PendingClients           $DB_PendingClients,
                                protected NotificationServices        $notificationServices,
                                protected DB_OtoExerciseComments $DB_OtoExerciseComments,
                                protected DB_OneToOneProgram     $DB_OneToOneProgram
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
            'coach_package_id' => $coach_info->coach->package->id,
            'coach_package_name' => $coach_info->coach->package->name,
            'coach_package_amount' => $coach_info->coach->package->amount,
            'coach_country_id' => $coach_info->country_id ?? "",
            'coach_country_name' => $coach_info->country ? $coach_info->country->name : "",
            'coach_gender_id' => $coach_info->gender_id ?? "",
            'coach_gender_name' => $coach_info->gender ? $coach_info->gender->name : "",
            'coach_package_clients_limit' => $coach_info->coach->package->clients_limit,
            'total_clients' => $number_of_clients,
            'active_clients' => $number_of_active_clients,
            'done_workouts' => $done_workout,
            'clients_activity' => $clients_activity,
            'today_logs' => $list_logs_arr,
            'unread_notifications' => $unread_notifications ? "1" : "0",
            'is_owner' => strval($coach_info->isGymOwner),
            'is_admin' => strval($coach_info->isGymAdmin),
            'is_gym_coach' => strval($coach_info->withGym),
            'subscription' => [
                'coach_package_id' => $coach_info->coach->package->id,
                'coach_package_name' => $coach_info->coach->package->name,
                'coach_package_amount' => $coach_info->coach->package->amount,
                'coach_package_clients_limit' => $coach_info->coach->package->clients_limit,
                'coach_due_date' => $coach_info->due_date,
                'in_trial' => $coach_info->coach->in_trial,
            ]
        ]);
    }

    /**
     * List client activities (exercises + comments) for a given coach and date.
     *
     * This method collects all relevant exercise logs and comments on a specified date,
     * groups them by program and client, and returns a structured response.
     *
     * @param Request $request Must contain `date`, and authenticated `coach`
     * @return JsonResponse
     */
    public function list_client_activity_in_date($request)
    {
        // ✅ Step 1: Validate the request input (e.g., date must be in format Y-m-d)
        $this->validationServices->list_client_activity_in_date($request);

        $date = $request->date;
        $coachId = $request->user()->id;

        // 📌 Step 2: Fetch exercises related to the coach and that have logs/updates on the given date
        $exercises = $this->DB_OneToOneProgramExercises->getExercisesWithUpdatesInDate($coachId, $date);

        // 📌 Step 3: Fetch comments made on the same date by the coach
        $comments = $this->DB_OtoExerciseComments->getProgramsWithDatesThatHasCommentsInDate($coachId, $date);

        // 🔄 Step 4: Merge both exercises and comments into a single collection of [programId => [dates]]
        $programsCollection = $this->mergeProgramActivityDates($exercises, $comments);


        // 📦 Step 5: Fetch programs with client and exercises data for the collected dates
        $programs = $this->DB_OneToOneProgram->getProgramsWithClientAndExercisesForCollectedDated($programsCollection);

        // 🧮 Step 6: Group exercises by date within each program
        $this->groupExercisesByDateWithinEachProgram($programs);

        // 📝 Step 7: Fetch all comments again for the same program/date pairs
        $programsComments = $this->DB_OtoExerciseComments->getCommentsForProgramDatePairs($programsCollection);

        // 🎯 Step 8: Structure the final response grouped by client
        $response = $this->buildClientProgramActivityStructure($programs, $programsComments);

        // 🚀 Step 9: Return structured response
        return sendResponse($response);
    }

    /**
     * Merge exercise and comment dates by program into one collection.
     *
     * @param \Illuminate\Support\Collection $exercises [program_id => [dates]]
     * @param \Illuminate\Support\Collection $comments [program_id => [dates]]
     * @return \Illuminate\Support\Collection  [program_id => [merged unique dates]]
     */
    private function mergeProgramActivityDates(\Illuminate\Support\Collection $exercises, \Illuminate\Support\Collection $comments): \Illuminate\Support\Collection
    {
        $programsCollection = collect();

        foreach ($exercises as $programId => $dates) {
            $programsCollection[$programId] = $dates;
        }

        foreach ($comments as $programId => $dates) {
            if ($programsCollection->has($programId)) {
                $programsCollection[$programId] = collect($programsCollection[$programId])
                    ->merge($dates)
                    ->unique()
                    ->values()
                    ->toArray();
            } else {
                $programsCollection[$programId] = $dates;
            }
        }

        return $programsCollection;
    }

    /**
     * Build a structured array of client programs with exercises and comments grouped by date.
     *
     * @param \Illuminate\Support\Collection $programs Programs with grouped_exercises
     * @param \Illuminate\Support\Collection $programsComments Grouped comments (keyed by "programId_date")
     * @return array
     */
    private function buildClientProgramActivityStructure($programs, $programsComments)
    {
        $result = [];

        foreach ($programs as $program) {
            $client = $program->client;

            // Unique key to check if this client already exists in result
            $clientIndex = collect($result)->search(fn($entry) => $entry['client_id'] === $client->id);

            if ($clientIndex === false) {
                $result[] = [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'programs' => []
                ];
                $clientIndex = array_key_last($result);
            }

            $programData = [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'dates' => []
            ];

            foreach ($program->grouped_exercises as $date => $exercises) {
                $dateData = [
                    'date' => $date,
                    'exercises' => [],
                    'comments' => []
                ];

                foreach ($exercises as $exercise) {
                    $log = $exercise->log;

                    $dateData['exercises'][] = [
                        'exercise_id' => $exercise->id,
                        'arrangement' => $exercise->arrangement,
                        'exercise_name' => $exercise->name,
                        'exercise_description' => $exercise->description,
                        'is_done' => $exercise->is_done,
                        'log_id' => $log->id ?? "",
                        'log_sets' => $log->sets ?? "",
                        'log_details' => $log->details ?? "",
                        'log_date' => $log?->created_at?->format('Y-m-d') ?? "",
                        'log_time' => $log?->created_at?->format('H:i:s') ?? "",
                    ];
                }

                $commentKey = $program->id . '_' . $date;
                $commentsForDate = $programsComments[$commentKey] ?? [];

                foreach ($commentsForDate as $comment) {
                    $dateData['comments'][] = [
                        'comment_id' => $comment['id'],
                        'comment_content' => $comment['comment'],
                        'sender' => $comment['sender'] == 1 ? 'Coach' : 'Client',
                        'coach_id' => $program->coach_id,
                        'coach_name' => optional($program->coach)->name ?? 'Unknown',
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                    ];
                }

                $programData['dates'][] = $dateData;
            }

            $result[$clientIndex]['programs'][] = $programData;
        }

        return $result;
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
        $country_id = $request->country_id;
        $gender_id = $request->gender_id;

        $this->DB_Users->update_user($coach_id, $name, $email, $phone, $country_id, $gender_id);
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
        $payment_link = $request->payment_link;
        $this->DB_Users->update_user_due_date($client_id, $due_date);
        if ($payment_link) $this->update_user_payment_link($client_id, $payment_link);
        return sendResponse(['message' => "Updated successfully"]);
    }

    public function update_user_payment_link(mixed $client_id, mixed $payment_link)
    {
        $client_info = $this->DB_Clients->get_client_info($client_id);
        if ($client_info) {
            $this->DB_Clients->update_client_payment_link($client_info, $payment_link);
        } else {
            $this->DB_Clients->create_client_payment_link($client_id, $payment_link);
        }
    }
    public function create_payment_link($request)
    {
        $this->validationServices->create_payment_link($request);
        RequestInfoLog::query()->create([
            "user_id" => $request->user()->id,
            "ip" => $request->ip(),
            "user_agent" => $request->header('User-Agent'),
            "route" => $request->getPathInfo(),
            "body" => $request->getContent(),
        ]);
        $coach_id = $request->user()->id;
        $upgrade = $request->upgrade;
        $user = $this->DB_Users->get_user_info($coach_id);

        if ($user->user_type != "0") {
            return sendError("This user is not a coach");
        }

        $coach_package_id = $user->coach->package_id;
        $old_package = $this->DB_Packages->find_package($coach_package_id);

        $coach_package = $this->getCoachCurrentPackage($coach_id);

        $package_id = $coach_package->id;
        $amount = $coach_package->amount;
        $package_name = $coach_package->name;
        $package_clients_limit = $coach_package->clients_limit;

        if ($upgrade == "1") {
            list($upgraded_package) = $this->get_coach_package($coach_id);
            $package_id = $upgraded_package->id;
            $amount = $upgraded_package->amount - $old_package->amount;
            $package_name = $upgraded_package->name;
            $package_clients_limit = $upgraded_package->clients_limit;
        }

        $payment_description = $package_name . " payment with " . $package_clients_limit . " clients limit.";

        try {
            $payment = $this->paymentServices->pay(amount: $amount, full_name: $user->name, email: $user->email, description: $payment_description);
            $payment_url = $payment->client_url;
            $order_id = $payment->order;
            $payment_amount = $payment->amount_cents / 100;
            $this->DB_UserPayment->create_user_payment(coach_id: $user->id, order_id: $order_id, amount: $payment_amount, package_id: $package_id, upgrade: $upgrade);
            RequestInfoLog::query()->create([
                "user_id" => $request->user()->id,
                "ip" => $request->ip(),
                "user_agent" => $request->header('User-Agent'),
                "route" => $request->getPathInfo(),
                "body" => "Payment link created successfully with link-->".$payment_url,
            ]);
            return sendResponse(["payment_url" => $payment_url]);
        } catch (\Exception $exception) {
            RequestInfoLog::query()->create([
                "user_id" => $request->user()->id,
                "ip" => $request->ip(),
                "user_agent" => $request->header('User-Agent'),
                "route" => $request->getPathInfo(),
                "body" => "Payment failed-->".$exception->getMessage(),
            ]);
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
                $single_log_arr['videos'] = [];

                if ($log->log_videos()->exists()) {
                    foreach ($log->log_videos as $log_video) {
                        $single_log_arr['videos'][] = $log_video->path;
                    }
                }
                $logs_arr[] = $single_log_arr;
            }
        }
        return $logs_arr;
    }

    public function list_logs_arr_updated(Collection|array $logs)
    {
        $logs_arr = [];
        if ($logs) {
            foreach ($logs as $log) {
                $single_log_arr = [];
                $single_log_arr['client_id'] = $log->exercise->one_to_one_program->client_id;
                $single_log_arr['client_name'] = $log->exercise->one_to_one_program->client->name;
                $single_log_arr['programs']['program_id'] = $log->exercise->one_to_one_program->id;
                $single_log_arr['programs']['program_name'] = $log->exercise->one_to_one_program->name;
                $single_log_arr['programs']['exercises']['exercise_id'] = $log->exercise->id;
                $single_log_arr['programs']['exercises']['exercise_name'] = $log->exercise->name;
                $single_log_arr['programs']['exercises']['arrangement'] = $log->exercise->arrangement;
                $single_log_arr['programs']['exercises']['exercise_description'] = $log->exercise->description;
                $single_log_arr['programs']['exercises']['is_done'] = $log->exercise->is_done;
                $single_log_arr['programs']['exercises']['log_id'] = $log->id;
                $single_log_arr['programs']['exercises']['log_sets'] = $log->sets;
                $single_log_arr['programs']['exercises']['log_details'] = $log->details;
                $single_log_arr['programs']['exercises']['log_date'] = $log->created_at->format("Y-m-d");
                $single_log_arr['programs']['exercises']['log_time'] = $log->created_at->format("H:i:s");
//                $single_log_arr['videos'] = [];
//
//                if ($log->log_videos()->exists()) {
//                    foreach ($log->log_videos as $log_video) {
//                        $single_log_arr['videos'][] = $log_video->path;
//                    }
//                }
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
        $pending_clients = $this->DB_PendingClients->get_pending_clients($coach_id);
        $total_coach_clients = $active_clients + $pending_clients;
        $get_coach_info = $this->DB_Coaches->get_coach_info($coach_id);
        $coach_package = $get_coach_info->package;
        $upgrade = false;

        if ($total_coach_clients + 1 > $coach_package->clients_limit) {
//          the coach now will exceed the client limit
            $upgrade = true;
//          get the higher package

            $coach_package = $this->DB_Packages->get_appropriate_package($total_coach_clients);
        }
        return array($coach_package, $upgrade);
    }

    public function getCoachCurrentPackage(mixed $coach_id)
    {
        $active_clients = $this->DB_Clients->get_active_clients($coach_id);
        $pending_clients = $this->DB_PendingClients->get_pending_clients($coach_id);
        $total_coach_clients = $active_clients + $pending_clients;
        return $this->DB_Packages->get_appropriate_package($total_coach_clients, ">=");
    }

    public function list_packages()
    {
        $packages = $this->DB_Packages->list_packages()->toArray();
        $packages_arr = array_map(function ($package) {
            return [
                'id' => $package['id'],
                'name' => $package['name'],
                'amount' => $package['amount'],
                'clients_limit' => $package['clients_limit'],
            ];
        }, $packages);

        return sendResponse($packages_arr);
    }

    public function list_payments($request)
    {
        $coach_id = $request->user()->id;
        $coach_payments = $this->DB_UserPayment->get_coach_payment_orders($coach_id)->toArray();
        $payments_arr = array_map(function ($payment) {
            return [
                'id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'amount' => $payment['amount'],
                'status' => $payment['status_text'],
                'package_name' => $payment['package']?$payment['package']['name']:"",
                'order_date' => Carbon::parse($payment['created_at'])->toDateString(),
                'order_time' => Carbon::parse($payment['created_at'])->toTimeString(),
            ];
        }, $coach_payments);

        return sendResponse($payments_arr);
    }

    /**
     * Group exercises by date within each program
     * @param mixed $programs
     * @return void
     */
    private function groupExercisesByDateWithinEachProgram(mixed $programs): void
    {
        $programs->map(function ($program) {
            $program->grouped_exercises = $program->exercises->groupBy('date');
            unset($program->exercises);
            return $program;
        });
    }
}
