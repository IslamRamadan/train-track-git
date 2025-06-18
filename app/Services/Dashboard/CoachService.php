<?php

namespace App\Services\Dashboard;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use App\Services\ValidationServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class CoachService
{

    public function __construct(protected ValidationServices $validationServices, protected DB_Coaches $DB_Coaches, protected DB_Programs $DB_Programs,
                                protected DB_Users           $DB_Users, protected DB_Packages $DB_Packages, protected DB_UserPayment $DB_UserPayment
        , protected PaymentServices $paymentServices
    )
    {
    }
//
//    public function showUser($user, $request)
//    {
//        $user = $this->userRepository->display_user_info($user->id);
//        $rate = "";
//        if ($user->teacher) {
//            $rate = $this->rateRepository->teacher_rate($user->teacher->user_id);
//        }
////
//        if ($request->ajax()) {
//            $data = Request::where($user->teacher ? 'teacher_id' : 'user_id', $user->id)->select('*');
//            return Datatables::of($data)
//                ->addIndexColumn()
//                ->addColumn('student_name', function ($user) {
//                    return '<a href=' . route('users.show', $user->user_id) . ' class="">' . $user->user->name . '</a>';
//                })
//                ->addColumn('teacher_name', function ($user) {
//                    return '<a href=' . route('users.show', $user->user_id) . ' class="">' . $user->teacher->name . '</a>';
//                })
//                ->addColumn('req_status', function ($row) {
//                    if ($row->status == "pending") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-primary">
//                              Pending
//                            </button>
//';
//                    } elseif ($row->status == "in_progress") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-warning">
//                              In_progress
//                            </button>
//';
//                    } elseif ($row->status == "done") {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-success">
//                              Done
//                            </button>
//';
//                    } else {
//                        $btn = '
//                            <button type="button" class="btn btn-sm btn-danger">
//                              Rejected
//                            </button>
//';
//                    }
//                    return $btn;
//                })
//                ->rawColumns(['req_status', 'student_name', 'teacher_name'])
//                ->make(true);
//        }
////
//        return view('dashboard.users.show', compact(['user', 'rate']));
//    }
//
//    public function destroy($id)
//    {
//        $this->userRepository->delete_some_user($id);
//        return redirect()->back()->with(['msg' => "User deleted successfully"]);
//    }

    public function index($request)
    {
        if ($request->ajax()) {
            $data = User::where('user_type', "0")->with(['coach.package', 'coach_client_coach'])->select('*');
            $result = Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '
                            <button type="button" class="btn btn-sm btn-primary updateDueDate mb-2" data-id=' . $row->id . ' data-status="0" data-toggle="modal" data-target="#updateDueDate">
                              ' . __('translate.UpdateDueDate') . '
                            </button>
';
                    if ($row->coach->status == "1") {
                        $btn .= '
                            <button type="button" class="btn btn-sm btn-danger blockCoach mb-2" data-id=' . $row->id . ' data-status="0" data-toggle="modal" data-target="#blockCoach">
                              ' . __('translate.Block') . '
                            </button>
';
                    } else {
                        $btn .= '
                            <button type="button" class="btn btn-sm btn-warning blockCoach mb-2" data-id=' . $row->id . ' data-status="1" data-toggle="modal" data-target="#blockCoach">
                              ' . __('translate.UnBlock') . '
                            </button>
';
                    }
                    $btn .= '
                            <button type="button" class="btn btn-sm btn-success updatePackage mb-2" data-id=' . $row->id . ' data-package=' . $row->coach->package_id . ' data-toggle="modal" data-target="#updatePackage" >
                              ' . __('translate.UpdatePackage') . '
                            </button>';
                    return $btn;
                })
                ->addColumn('due_date_tab', function ($row) {
                    $due_date = Carbon::parse($row->due_date);
                    if ($due_date->lt(Carbon::today())) {
                        $class = "danger";
                    } elseif ($due_date->gt(Carbon::today()) && $due_date->lte(Carbon::today()->addWeek())) {
                        $class = "warning";
                    } else {
                        $class = "success";
                    }
                    $btn = '<div class="badge bg-' . $class . '" >
                              ' . $row->due_date . '
                            </div>';
                    return $btn;
                })
                ->addColumn('active_clients', function ($row) {
                    return $row->active_clients;
                })
                ->addColumn('programs_number', function ($row) {
                    return $this->DB_Programs->coach_programs_count($row->id);
                })->addColumn('package_name', function ($row) {
                    return $row->coach->package->name;
                })
                ->addColumn('creation_date', function ($row) {
                    return Carbon::parse($row->created_at)->toDateString();
                })
                ->filterColumn('creation_date', function ($query, $keyword) {
                    $query->where('created_at', 'like', "%$keyword%");
                })
                ->filterColumn('due_date_tab', function ($query, $keyword) {
                    $query->where('due_date', 'like', "%$keyword%");
                })
                ->orderColumn('due_date_tab', function ($query,$order) {
                    $query->orderBy('due_date', $order); // Sort in descending order
                })
                ->rawColumns(['active_clients', 'action', 'due_date_tab', 'programs_number'])
                ->make();
            return $result;
        }
        $packages = $this->DB_Packages->list_packages();
        return view('dashboard.coaches.index', compact('packages'));
    }

    public function block($id, $request)
    {
        $status = $request->status;
        $this->DB_Coaches->change_coach_status($id, $status);
        return redirect()->back()->with(['msg' => "Done successfully"]);
    }

    public function register_form($package)
    {
        $package_info = $this->DB_Packages->find_package($package);
        if (!$package_info) return abort(404);
        $package_amount = $package_info->amount;

        return view('users.register', compact('package', 'package_amount'));
    }

    public function register($request)
    {
        $this->validationServices->coach_web_register($request);
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $password = $request->password;
        $gym = $request->gym;
        $speciality = $request->speciality;
        $certificates = $request->certificates;
        $country_id = $request->country_id;
        $gender_id = $request->gender_id;
        $pay_now = $request->pay_now;
        $package_id = $request->package_id;
        $due_date = Carbon::today()->addMonth()->toDateString();
        DB::beginTransaction();
        $user = $this->DB_Users->create_user($name, $email, $phone, $password, $due_date, $country_id, $gender_id);
        $this->DB_Coaches->create_coach($gym, $speciality, $certificates, $user->id, $package_id);
        DB::commit();

        try {
            Mail::to($email)->send(new WelcomeMail(name: $name));
        } catch (\Exception $exception) {
        }

        if ($pay_now == "0") {
            return view('payment.free_trial');
        }
        $package = $this->DB_Packages->find_package($package_id);
        $payment_description = $package->name . " payment with " . $package->clients_limit . " clients limit.";
        try {
            $payment = $this->paymentServices->pay(amount: $package->amount, full_name: $name, email: $email, description: $payment_description);
            $payment_url = $payment->client_url;
            $order_id = $payment->order;
            $payment_amount = $payment->amount_cents / 100;
            $this->DB_UserPayment->create_user_payment(coach_id: $user->id, order_id: $order_id, amount: $payment_amount, package_id: $package_id, upgrade: "0", first_pay: "1");
            return redirect($payment_url);
        } catch (\Exception $exception) {
            return view('payment.payment_failed');
        }
    }

    public function update_coach_due_date($coach_id, $request)
    {
        $this->validationServices->update_coach_due_date($request);
        $due_date = $request->due_date;
        $this->DB_Users->update_user_due_date($coach_id, $due_date);
        return redirect()->back()->with(['msg' => "Updated successfully"]);
    }

    public function update_coach_package($coach_id, $request)
    {
        $this->validationServices->update_coach_package($request);
        $package = $request->package;
        $this->DB_Coaches->update_coach_package($coach_id, $package);
        return redirect()->back()->with(['msg' => "Updated successfully"]);
    }

    public function verifyCoachEmail($id)
    {
        $user_id = Crypt::decrypt($id);
        $coach = $this->DB_Users->get_user_info($user_id);
        if (!$coach) {
            abort(404);
        }
        $name = $coach->name;
        if (!$coach->email_verified_at) {
            $this->DB_Users->update_user_data($coach, ['email_verified_at' => Carbon::now()]);
            try {
                Mail::to($coach->email)->send(new WelcomeMail(name: $name));
            } catch (\Exception $exception) {
            }
        }

        return view('coaches.coach-email-verified', compact('name'));
    }
}
