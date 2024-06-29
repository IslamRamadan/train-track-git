<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_Programs;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use App\Services\ValidationServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CoachService
{

    public function __construct(protected ValidationServices $validationServices, protected DB_Coaches $DB_Coaches, protected DB_Programs $DB_Programs,
                                protected DB_Users           $DB_Users, protected DB_Packages $DB_Packages, protected DB_UserPayment $DB_UserPayment)
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
            $data = User::where('user_type', "0")->with(['coach', 'coach_client_coach'])->select('*');
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
                ->rawColumns(['active_clients', 'action', 'due_date_tab', 'programs_number'])
                ->make();
            return $result;
        }
        return view('dashboard.coaches.index');
    }

    public function block($id, $request)
    {
        $status = $request->status;
        $this->DB_Coaches->change_coach_status($id, $status);
        return redirect()->back()->with(['msg' => "Done successfully"]);
    }

    public function register_form($package)
    {
            return view('users.register', compact('package'));
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
        $pay_now = $request->pay_now;
        $package_id = $request->package_id;
        $due_date = Carbon::today()->addMonth()->toDateString();
        DB::beginTransaction();
        $user = $this->DB_Users->create_user($name, $email, $phone, $password, $due_date);
        $this->DB_Coaches->create_coach($gym, $speciality, $certificates, $user->id, $package_id);
        DB::commit();

        if ($pay_now == "0") {
            return view('payment.free_trial');
        }
        $package = $this->DB_Packages->find_package($package_id);
        $payment_description = $package->name . " payment with " . $package->clients_limit . " clients limit.";
        $pay = new PaymentServices();
        try {
            $payment = $pay->pay(amount: $package->amount, full_name: $name, email: $email, description: $payment_description);
            $payment_url = $payment->client_url;
            $order_id = $payment->order;
            $payment_amount = $payment->amount_cents / 100;
            $this->DB_UserPayment->create_user_payment(coach_id: $user->id, order_id: $order_id, amount: $payment_amount);
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
}
