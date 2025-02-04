<?php

namespace App\Services\PaymentServices;

use App\Models\UsersPayment;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_PendingClients;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\ValidationServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayMob\Facades\PayMob;
use Yajra\DataTables\Facades\DataTables;

class PaymentServices
{
    public function __construct(protected DB_UserPayment    $DB_UserPayment,
                                protected DB_Users          $DB_Users,
                                protected DB_Clients        $DB_Clients,
                                protected DB_Packages       $DB_Packages,
                                protected DB_Coaches        $DB_Coaches,
                                protected DB_PendingClients $DB_PendingClients,
                                protected ValidationServices $validationServices,
    )
    {
    }

    public function index($request)
    {
        if ($request->ajax()) {
            $data = UsersPayment::with(['user'])->select('*')->orderBy('created_at','desc');
            $result = Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('status_tab', function ($row) {
                    $status = $row->status;
                    if ($status == "1") {
                        $class = "warning";
                        $payment_status = "UnPaid";
                    } elseif ($status == "2") {
                        $class = "success";
                        $payment_status = "Paid";
                    } else {
                        $class = "danger";
                        $payment_status = "Cancelled";
                    }
                    $btn = '<div class="badge bg-' . $class . '" >
                              ' . __('translate.' . $payment_status) . '
                            </div>';
                    return $btn;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user->name;
                })
                ->addColumn('user_phone', function ($row) {
                    return $row->user->phone;
                })
                ->addColumn('creation_date', function ($row) {
                    return Carbon::parse($row->created_at)->toDateString();
                })
                ->addColumn('action', function ($row) {
                    return '
                            <button type="button" class="btn btn-sm btn-primary updateOrderStatus mb-2" data-id=' . $row->id . ' data-status=' . $row->status . ' data-toggle="modal" data-target="#updateOrderStatus">
                              ' . __('translate.UpdateOrderStatus') . '
                            </button>
';
                })
                ->filterColumn('creation_date', function ($query, $keyword) {
                    $query->where('created_at', 'like', "%$keyword%");
                })
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($query) use ($keyword) { // Optional: Search related table
                        $query->where('name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('user_phone', function ($query, $keyword) {
                    $query->whereHas('user', function ($query) use ($keyword) { // Optional: Search related table
                        $query->where('phone', 'like', "%$keyword%");
                    });
                })
                ->rawColumns(['user_name', 'user_phone', 'creation_date', 'status_tab', 'action'])
                ->make();
            return $result;
        }
        return view('dashboard.payments.index');
    }


    public function pay($amount, $full_name, $email, $description)
    {
        $auth = PayMob::AuthenticationRequest();
        $payment_link_image = asset('images/logo.png');
        return PayMob::createPaymentLink($auth->token, $payment_link_image, $amount * 100, $full_name, $email, $description);
    }

    public function checkout_response($request)
    {
        if ($request->success == "true") {
            $order_id = $request->order;
            $amount = $request->amount_cents / 100;
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "2");
            if ($get_the_order) {
                $coach_id = $get_the_order->coach_id;
                $get_the_coach = $this->DB_Users->get_user_info($coach_id);
                $coach_due_date = $get_the_coach->due_date;

                $success_msg = __('translate.PaymentSuccessMsg') . $coach_due_date;
                return view('payment.payment_done', compact('success_msg', 'order_id'));
            } else {
                return view('payment.payment_failed');
            }
        }
        return view('payment.payment_failed');
    }

    /**
     * Check if user need to downgrade the package
     * @param $coach_id
     * @return void
     */
    public function checkIfUserNeedToDowngradeThePackage($coach_id): void
    {
        $coach_active_clients = $this->DB_Clients->get_active_clients($coach_id);
        $pending_clients = $this->DB_PendingClients->get_pending_clients($coach_id);
        $total_coach_clients = $coach_active_clients + $pending_clients;
        $appropriate_package = $this->DB_Packages->get_appropriate_package($total_coach_clients, ">=");
        $this->DB_Coaches->update_coach_package($coach_id, $appropriate_package->id);
    }

    public function update_order_status($order_id, $request)
    {
        $this->validationServices->update_order_status($request);
        $status = $request->order_status;
        $payment = $this->DB_UserPayment->find_user_payment_with_id($order_id);
        $this->DB_UserPayment->update_user_payment_status($payment, $status);
        return redirect()->back()->with(['msg' => "Updated successfully"]);
    }

    public function checkout_processed($request)
    {
        // DB::beginTransaction();
        if ($request['obj']['success'] == "true") {
            $order_id = $request['obj']['order'];
            $amount = $request['obj']['amount_cents'] / 100;
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "1");
            if ($get_the_order) {
                $coach_id = $get_the_order->coach_id;
                $get_the_coach = $this->DB_Users->get_user_info($coach_id);
                $coach_due_date = Carbon::parse($get_the_coach->due_date);

//                get the coach due date
                if ($coach_due_date->lt(Carbon::today()) || $get_the_order->upgrade == "1") {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                } else {
                    $new_due_date = $coach_due_date->addMonth()->toDateString();
                }

                $this->DB_Users->update_user_due_date($coach_id, $new_due_date);
                $this->DB_UserPayment->update_user_payment_status($get_the_order, "2");
                if ($get_the_order->upgrade == "0") {
                    //check if user need to downgrade the package
                    if ($get_the_order->first_pay == "0") $this->checkIfUserNeedToDowngradeThePackage($coach_id);
                } else {
                    $this->DB_Coaches->update_coach_package(coach_id: $coach_id, package_id: $get_the_order->package_id);
                }

                $success_msg = __('translate.PaymentSuccessMsg') . $new_due_date;
                return sendResponse(["msg" => $success_msg]);
            } else {
                $success_msg = "Payment Failed";
                return sendResponse(["msg" => $success_msg]);
            }
        }
        // DB::commit();
        $success_msg = "Payment Failed";
        Log::info($success_msg . "-----" . json_encode($request->all()));
        return sendResponse(["msg" => $success_msg]);
    }


}
