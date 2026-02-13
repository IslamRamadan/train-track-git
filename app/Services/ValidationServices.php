<?php

namespace App\Services;

use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_CoachExerciseTemplates;
use App\Services\DatabaseServices\DB_CoachVideos;
use App\Services\DatabaseServices\DB_ExerciseLog;
use App\Services\DatabaseServices\DB_Exercises;
use App\Services\DatabaseServices\DB_OneToOneProgram;
use App\Services\DatabaseServices\DB_OneToOneProgramExercises;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationServices
{
    public function __construct(
        protected DB_OneToOneProgramExercises $DB_OneToOneProgramExercises,
        protected DB_ExerciseLog              $DB_ExerciseLog,
        protected DB_Clients                  $DB_Clients,
        protected DB_Programs                 $DB_Programs,
        protected DB_OneToOneProgram          $DB_OneToOneProgram,
        protected DB_CoachVideos              $DB_CoachVideos,
        protected DB_Users                    $DB_Users,
        protected DB_CoachExerciseTemplates $DB_ExerciseTemplates,
        protected DB_Exercises              $DB_Exercises,
    ) {}

    public function login($request)
    {
        $request->validate([
            'phone' => 'required_without:email',
            'email' => 'required_without:phone',
            'password' => 'required',
            'notification_token' => 'nullable',
        ]);
    }

    public function list_programs($request)
    {
        $request->validate([
            'search' => 'nullable',
        ]);
    }

    public function add_program($request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'description' => 'required|max:1000',
            'type' => 'required|in:0,1,2,3',
            'starting_date' => 'nullable|required_if:type,1|date|date_format:Y-m-d',
            'sync' => 'required_if:type,1|required_if:type,3|in:0,1',
            'image' => 'nullable'
        ]);
    }

    public function edit_program($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|max:50',
            'description' => 'required|max:1000',
            'type' => 'required|in:0,1,2,3',
            'starting_date' => 'nullable|required_if:type,1|date|date_format:Y-m-d',
            'image' => 'nullable'
        ]);
    }

    public function edit_program_sync($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'sync' => 'required|in:0,1'
        ]);
    }

    public function list_program_exercises($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'week' => 'nullable|numeric',
        ]);
    }

    public function list_program_exercises_by_day($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'day' => 'required',
        ]);
    }

    public function add_program_exercise($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required',
            'day' => 'required',
            'description' => 'nullable|max:1000',
            'extra_description' => 'nullable|max:1000',
            'videos' => 'nullable'
        ]);
    }

    public function copy_program_exercise($request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:program_exercises,id',
            'to_program_id' => 'required|exists:programs,id',
            'day' => 'required',
        ]);
    }

    public function copy_vv_program_exercise($request)
    {
        $validator = Validator::make($request->all(), [
            'from_type' => 'required',
            'to_type' => 'required',
            'exercise_id' => 'required',
            'to_program_id' => 'required',
            'to_day' => [
                Rule::requiredIf($request->input('to_type') === 'template'),
                'numeric'
            ],
            'to_date' => [
                Rule::requiredIf($request->input('to_type') === 'oto'),
                'date_format:Y-m-d'
            ],
        ]);
        $validator->after(function ($validator) use ($request) {
            if (
                $request->input('from_type') === 'template' &&
                !$this->DB_Exercises->verify_exercise_id($request->exercise_id)
            ) {
                $validator->errors()->add('exercise_id', 'The selected exercise_id does not exist in exercises.');
            }

            if (
                $request->input('from_type') === 'oto' &&
                !$this->DB_OneToOneProgramExercises->verify_exercise_id($request->exercise_id)
            ) {
                $validator->errors()->add('exercise_id', 'The selected exercise_id does not exist in exercises.');
            }

            if (
                $request->input('to_type') === 'template' &&
                !$this->DB_Programs->verify_program_id($request->to_program_id)
            ) {
                $validator->errors()->add('to_program_id', 'The selected to_program_id does not exist in template_programs.');
            }

            if (
                $request->input('to_type') === 'oto' &&
                !$this->DB_OneToOneProgram->verify_program_id($request->to_program_id)
            ) {
                $validator->errors()->add('to_program_id', 'The selected to_program_id does not exist in oto_programs.');
            }
        });
        $validator->validate();
    }
    public function copy_program_exercise_days($request)
    {
        $request->validate([
            'from_program_id' => 'required|exists:programs,id',
            'to_program_id' => 'required|exists:programs,id',
            'copied_days' => 'required|array',
            'start_day' => 'required',
        ]);
    }

    public function copy_vv_program_exercise_days($request)
    {
        $validator = Validator::make($request->all(), [
            'from_type' => 'required',
            'to_type' => 'required',
            'from_program_id' => 'required',
            'to_program_id' => 'required',
            'copied_days' => [
                Rule::requiredIf($request->input('from_type') === 'template'),
                'array'
            ],
            'copied_days.*' => 'nullable|numeric',
            'start_day' => [
                Rule::requiredIf($request->input('from_program_id') === 'template'),
                'numeric'
            ],
            'copied_dates' => [
                Rule::requiredIf($request->input('from_type') === 'oto'),
                'array'
            ],
            'copied_dates.*' => 'date_format:Y-m-d',
            'start_date' => [
                Rule::requiredIf($request->input('to_type') === 'oto'),
                'date_format:Y-m-d'
            ],
        ]);
        $this->validateProgramExist($validator, $request);

        $validator->validate();
    }
    public function cut_program_exercise_days($request)
    {
        $request->validate([
            'from_program_id' => 'required|exists:programs,id',
            'to_program_id' => 'required|exists:programs,id',
            'cut_days' => 'required|array',
            'start_day' => 'required',
        ]);
    }

    public function delete_program_exercise_days($request)
    {
        $request->validate([
            'deleted_days' => 'required|array',
            'program_id' => [
                'required',
                'exists:programs,id',
                function ($attribute, $value, $fail) use ($request) {
                    $verify_coach_id = $this->DB_Programs->verify_coach_id($request->user()->id, $value);
                    if (!$verify_coach_id) {
                        $fail('The program must be the coach program');
                    }
                },
            ]
        ]);
    }

    public function edit_program_exercise($request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:program_exercises,id',
            'name' => 'required',
            'description' => 'nullable|max:1000',
            'extra_description' => 'nullable|max:1000',
            'order' => 'required',
            'videos' => 'nullable',
        ]);
    }

    public function list_clients($request)
    {
        $request->validate([
            'search' => 'nullable',
            'status' => 'required|in:all,active,archived,pending',
        ]);
    }

    public function client_details($request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);
    }

    public function list_active_clients($request)
    {
        $request->validate([
            'search' => 'nullable',
        ]);
    }

    public function assign_program_to_client($request)
    {
        $request->validate([
            'clients_id' => 'required|array',
            'clients_id.*' => 'exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'start_day' => 'nullable|numeric',
            'end_day' => 'nullable|after_or_equal:start_day',
            'notify_client' => 'required|in:0,1',
        ]);
    }

    public function list_program_days($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);
    }

    public function list_client_ono_programs($request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);
    }

    public function assign_client_to_coach($request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:pending_clients,email|email:rfc,dns',
        ]);
    }

    public function remove_client_invitation($request)
    {
        $request->validate([
            'email' => 'required|email|exists:pending_clients,email',
        ]);
    }

    public function client_register($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email|exists:pending_clients,email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone',
            'password' => 'required',
            'country_id' => 'nullable|exists:countries,id',
            'gender_id' => 'nullable|exists:genders,id',

        ], [
            'email.unique' => 'This email already exists in the system',
            'email.exists' => 'This email isn\'t invited by any coach to register',
        ]);
    }

    public function coach_register($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email|email:rfc,dns',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone',
            'password' => 'required',
            'gym' => 'required',
            'speciality' => 'required',
            'certificates' => 'required',
            'country_id' => 'nullable|exists:countries,id',
            'gender_id' => 'nullable|exists:genders,id',

        ], [
            'email.unique' => 'This email already exists in the system',
        ]);
    }

    public function coach_web_register($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone',
            'password' => 'required|confirmed',
            'gym' => 'required',
            'speciality' => 'required',
            'certificates' => 'required',
            'pay_now' => 'required|in:0,1',
            'package_id' => 'required|exists:packages,id',
            'country_id' => 'nullable|exists:countries,id',
            'gender_id' => 'nullable|exists:genders,id',

        ], [
            'email.unique' => 'This email already exists in the system',
        ]);
    }

    public function coach_update_info($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|email|email:rfc,dns|unique:users,email,' . $request->user()->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone,' . $request->user()->id,
            'gym' => 'required',
            'speciality' => 'required',
            'certificates' => 'required',

        ], [
            'email.unique' => 'This email already exists in the system',
        ]);
    }

    public function list_client_programs($request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);
    }

    public function list_client_exercises($request)
    {
        $request->validate([
            'client_program_id' => 'required_without:client_id|exists:one_to_one_programs,id',
            'client_id' => ['required_without:client_program_id', 'exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
            'start_week_date' => 'nullable|date_format:Y-m-d'
        ]);
    }

    public function list_client_program_exercises_by_date($request)
    {
        $request->validate([
            'client_program_id' => 'required|exists:one_to_one_programs,id',
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function list_client_exercises_in_date($request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function add_client_program_exercise($request)
    {
        $request->validate([
            'client_program_id' => 'required|exists:one_to_one_programs,id',
            'name' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'description' => 'nullable|max:1000',
            'extra_description' => 'nullable|max:1000',
        ]);
    }

    public function copy_client_program_exercise($request)
    {
        $request->validate([
            'client_exercise_id' => 'required|exists:one_to_one_program_exercises,id',
            'to_client_program_id' => 'required|exists:one_to_one_programs,id',
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function copy_client_program_exercise_days($request)
    {
        $request->validate([
            'from_client_program_id' => 'required|exists:one_to_one_programs,id',
            'to_client_program_id' => 'required|exists:one_to_one_programs,id',
            'copied_dates' => 'required|array',
            'copied_dates.*' => 'date_format:Y-m-d',
            'start_date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function cut_client_program_exercise_days($request)
    {
        $request->validate([
            'from_client_program_id' => 'required|exists:one_to_one_programs,id',
            'to_client_program_id' => 'required|exists:one_to_one_programs,id',
            'cut_dates' => 'required|array',
            'start_date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function delete_client_program_exercise_days($request)
    {
        $request->validate([
            'deleted_dates' => 'required|array',
            'client_program_id' => [
                'required',
                'exists:one_to_one_programs,id',
                function ($attribute, $value, $fail) use ($request) {
                    $verify_coach_id = $this->DB_OneToOneProgram->verify_coach_id($request->user()->id, $value);
                    if (!$verify_coach_id) {
                        $fail('The program must be the coach program');
                    }
                },
            ]
        ]);
    }

    public function edit_client_program_exercise($request)
    {
        $request->validate([
            'client_exercise_id' => 'required|exists:one_to_one_program_exercises,id',
            'name' => 'required',
            'description' => 'nullable|max:1000',
            'extra_description' => 'nullable|max:1000',
            'order' => 'required',
        ]);
    }

    public function delete_program($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);
    }

    public function delete_program_exercise($request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:program_exercises,id',
        ]);
    }

    public function delete_client_ono_programs($request)
    {
        $request->validate([
            'client_program_id' => 'required|exists:one_to_one_programs,id',
        ]);
    }

    public function delete_client_exercise($request)
    {
        $request->validate([
            'client_exercise_id' => 'required|exists:one_to_one_program_exercises,id',
        ]);
    }

    public function log_client_exercise($request)
    {
        $request->validate([
            'client_exercise_id' => [
                'required',
                'exists:one_to_one_program_exercises,id',
                //                function ($attribute, $value, $fail) use ($request) {
                //                    $verify_client_id = $this->DB_OneToOneProgramExercises->verify_client_id($request->user()->id, $request->client_exercise_id);
                //                    if (!$verify_client_id) {
                //                        $fail('The exercise must be the client exercise');
                //                    }
                //                }
            ],
            'sets' => 'nullable|numeric',
            'videos_paths' => 'nullable|array',
            'details' => 'nullable'

        ]);
    }


    public function log_client_exercise_update($request)
    {
        $request->validate([
            'log_id' => [
                'required',
                'exists:exercise_logs,id',
                //                function ($attribute, $value, $fail) use ($request) {
                //                    $verify_client_id = $this->DB_ExerciseLog->verify_client_id($request->user()->id, $request->log_id);
                //                    if (!$verify_client_id) {
                //                        $fail('The exercise must be the client exercise');
                //                    }
                //                }
            ],
            'sets' => 'nullable|numeric',
            'details' => 'nullable'

        ]);
    }

    public function update_exercise_status($request)
    {
        $request->validate([
            'client_exercise_id' => [
                'required',
                'exists:one_to_one_program_exercises,id',
                //                function ($attribute, $value, $fail) use ($request) {
                //                    $verify_client_id = $this->DB_OneToOneProgramExercises->verify_client_id($request->user()->id, $request->client_exercise_id);
                //                    if (!$verify_client_id) {
                //                        $fail('The exercise must be the client exercise');
                //                    }
                //                },
            ],
            'status' => 'required|in:0,1,2',
        ]);
    }

    public function update_info($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|email|email:rfc,dns|unique:users,email,' . $request->user()->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone,' . $request->user()->id,
            'weight' => 'nullable|numeric|min:20|max:500',
            'height' => 'nullable|numeric|min:50|max:300',
            'fitness_goal' => 'nullable',
            'label' => 'nullable',
            'notes' => 'nullable',
        ], [
            'email.unique' => 'This email already exists in the system',
            'phone.unique' => 'This phone already exists in the system',
        ]);
    }

    public function change_password($request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required',
        ]);
    }

    public function client_programs_logs_list($request)
    {
        $request->validate([
            'client_program_id' => 'required|exists:one_to_one_programs,id',
            'client_id' => 'nullable|exists:users,id'
        ]);
    }

    public function archive_client($request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
            'status' => 'required'
        ]);
    }

    public function list_client_logs($request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
        ]);
    }

    public function forget_password($request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
        ]);
    }

    public function add_comment($request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'comment' => 'required',
            'client_program_id' => 'required|exists:one_to_one_programs,id',
        ]);
    }

    public function delete_comment($request)
    {
        $request->validate([
            'comment_id' => 'required|exists:oto_exercise_comments,id',
        ]);
    }

    public function delete_client($request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
        ]);
    }

    public function get_clients_activities($request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function update_due_date($request)
    {
        $request->validate([
            'client_id' => ['exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
            'due_date' => 'required|date_format:Y-m-d',
            'payment_link' => 'nullable|max:900'
        ]);
    }

    public function update_coach_due_date($request)
    {
        $request->validate([
            'coach_id' => ['exists:users,id'],
            'due_date' => 'required|date_format:Y-m-d'
            //            'due_date' => 'required|date_format:Y-m-d|after_or_equal:tomorrow'
        ]);
    }

    public function create_payment_link($request)
    {
        $request->validate([
            'coach_id' => ['required', 'exists:users,id'],
            "upgrade" => "required|in:0,1"
        ]);
    }

    public function add_coach_video($request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'link' => 'required|max:300'
        ]);
    }

    public function edit_coach_video($request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'link' => 'required|max:300',
            'video_id' => ['required', 'exists:coach_videos,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_CoachVideos->verify_coach_id(coach_id: $request->user()->id, video_id: $value);
                if (!$verify_client_id) {
                    $fail('The video must be assigned to this coach');
                }
            }]
        ]);
    }

    public function delete_coach_video($request)
    {
        $request->validate([
            'video_id' => ['required', 'exists:coach_videos,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_CoachVideos->verify_coach_id(coach_id: $request->user()->id, video_id: $value);
                if (!$verify_client_id) {
                    $fail('The video must be assigned to this coach');
                }
            }]
        ]);
    }

    public function add_exercise_template($request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'description' => 'nullable|max:1000',
            'videos' => ['nullable', 'array'],
            'videos.*.title' => ['required', 'string', 'max:255'], // Validate title
            'videos.*.link' => ['required', 'max:1000'], // Validate link as a valid URL
        ]);
    }

    public function edit_exercise_template($request)
    {
        $request->validate([
            'title' => 'required|max:50',
            'description' => 'nullable|max:1000',
            'videos' => ['nullable', 'array'],
            'videos.*.title' => ['required', 'string', 'max:255'], // Validate title
            'videos.*.link' => ['required', 'max:1000'], // Validate link as a valid URL
            'exercise_template_id' => ['required', 'exists:coach_exercise_templates,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_ExerciseTemplates->verify_coach_id(coach_id: $request->user()->id, exercise_template_id: $value);
                if (!$verify_client_id) {
                    $fail('The exercise template must be assigned to this coach');
                }
            }]
        ]);
    }

    public function delete_exercise_template($request)
    {
        $request->validate([
            'exercise_template_id' => ['required', 'exists:coach_exercise_templates,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_ExerciseTemplates->verify_coach_id(coach_id: $request->user()->id, exercise_template_id: $value);
                if (!$verify_client_id) {
                    $fail('The exercise template must be assigned to this coach');
                }
            }]
        ]);
    }

    public function update_version($request)
    {
        $request->validate([
            'version' => 'required'
        ]);
    }

    public function send_coaches_notification($request)
    {
        $request->validate([
            'title' => "required",
            'message' => "required",
            'user_type' => 'required|in:coach,client,all'
        ]);
    }

    public function update_coach_package($request)
    {
        $request->validate([
            'coach_id' => ['exists:users,id'],
            'package' => 'required|exists:packages,id'
        ]);
    }

    public function update_order_status($request)
    {
        $request->validate([
            'order_id' => ['exists:users_payments,id'],
            'order_status' => 'required|in:0,1,2'
        ]);
    }

    public function add_gym($request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
            'description' => 'required|max:1000',
            'logo' => "nullable",
            'package_id' => ['nullable', 'exists:packages,id'],
        ]);
    }

    public function gym_create_payment_link($request)
    {
        $request->validate([
            'upgrade' => 'required|in:0,1',
            'package_id' => ['nullable', 'exists:packages,id'],
        ]);
    }



    public function invite_coach_to_gym($request, $check_email_belongs_to_client)
    {
        $request->validate([
            'email' => ['required', 'email', function ($attribute, $value, $fail) use ($request, $check_email_belongs_to_client) {
                if ($check_email_belongs_to_client && $check_email_belongs_to_client->user_type == "1") {
                    $fail('The email belongs to a client');
                }
            }],
        ]);
    }

    public function list_gym_coaches($request)
    {
        $request->validate([
            'search' => 'nullable|max:50',
            'status' => 'nullable|in:1,2,3',
        ]);
    }

    public function change_join_request_status($request)
    {
        $request->validate([
            'join_request_id' => 'required|exists:gym_join_requests,id',
            'status' => 'required|in:0,2',
        ]);
    }

    public function list_leave_requests($request)
    {
        $request->validate([
            'search' => 'nullable|max:50',
            'status' => 'nullable|in:0,1,2',
        ]);
    }

    public function change_leave_request_status($request)
    {
        $request->validate([
            'leave_request_id' => 'required|exists:gym_leave_requests,id',
            'status' => 'required|in:0,2',
        ]);
    }

    public function edit_coach_privilege($request)
    {
        $request->validate([
            'coach_id' => 'required|exists:gym_coaches,coach_id',
            'privilege' => 'required|in:2,3',
        ]);
    }

    public function remove_coach_from_gym($request)
    {
        $request->validate([
            'coach_id' => 'required|exists:gym_coaches,coach_id',
        ]);
    }

    public function send_join_request($request)
    {
        $request->validate([
            'gym_id' => 'required|exists:gyms,id',
        ]);
    }

    public function list_gyms($request)
    {
        $request->validate([
            'search' => 'nullable|max:50',
        ]);
    }

    public function edit_gym($request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'description' => 'required|max:1000',
            'logo' => "nullable"
        ]);
    }

    public function update_client_info($request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) use ($request) {
                $verify_client_id = $this->DB_Clients->verify_client_id(coach_id: $request->user()->id, client_id: $value);
                if (!$verify_client_id) {
                    $fail('The client must be assigned to this coach');
                }
            }],
            'tag' => "nullable|max:50",
            'weight' => 'nullable|numeric|min:20|max:500',
            'height' => 'nullable|numeric|min:50|max:300',
            'fitness_goal' => 'nullable',
            'label' => 'nullable',
            'notes' => 'nullable',
            'country_id' => 'nullable|exists:countries,id',
            'gender_id' => 'nullable|exists:genders,id',
        ]);
    }

    public function exportUsersToExcel($request)
    {
        $request->validate([
            'export' => 'required|array|in:0,1',
        ]);
    }

    public function getClientsHaveNotExercisesInDate($request)
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
        ]);
    }

    public function getClientsAssignedToProgram($request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);
    }

    public function searchValidation($request)
    {
        $request->validate([
            'search' => 'nullable|max:50',
        ]);
    }

    public function list_client_activity_in_date($request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
    }

    public function update_coach_info(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|email|email:rfc,dns|unique:users,email,' . $id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone,' . $id,
            'merchant_id' => 'nullable|min:1|max:20|unique:coaches,merchant_id,' . $id . ',user_id',
        ], [
            'email.unique' => 'This email already exists in the system',
            'phone.unique' => 'This phone already exists in the system',
        ]);
    }

    /**
     * @param \Illuminate\Validation\Validator $validator
     * @param $request
     * @return void
     */
    private function validateProgramExist(\Illuminate\Validation\Validator $validator, $request): void
    {
        // Additional validation for checking program existence
        $validator->after(function ($validator) use ($request) {
            if (
                $request->input('from_type') === 'template' &&
                !$this->DB_Programs->verify_program_id($request->from_program_id)
            ) {
                $validator->errors()->add('from_program_id', 'The selected from_program_id does not exist in template_programs.');
            }

            if (
                $request->input('from_type') === 'oto' &&
                !$this->DB_OneToOneProgram->verify_program_id($request->from_program_id)
            ) {
                $validator->errors()->add('from_program_id', 'The selected from_program_id does not exist in oto_programs.');
            }

            if (
                $request->input('to_type') === 'template' &&
                !$this->DB_Programs->verify_program_id($request->to_program_id)
            ) {
                $validator->errors()->add('to_program_id', 'The selected to_program_id does not exist in template_programs.');
            }

            if (
                $request->input('to_type') === 'oto' &&
                !$this->DB_OneToOneProgram->verify_program_id($request->to_program_id)
            ) {
                $validator->errors()->add('to_program_id', 'The selected to_program_id does not exist in oto_programs.');
            }
        });
    }

    public function createPaymentLinkValidation(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,user_id',
            'amount' => "required|numeric|min:10|max:10000",
            'no_of_days' => "required|numeric|min:1|max:365",
            'due_date' => "nullable|date|date_format:Y-m-d"
        ], []);
    }
    public function coach_id_validation($request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
        ]);
    }
}
