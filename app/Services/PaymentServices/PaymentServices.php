<?php

namespace App\Services\PaymentServices;

use App\Models\UsersPayment;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_Packages;
use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use Carbon\Carbon;
use PayMob\Facades\PayMob;
use Yajra\DataTables\Facades\DataTables;

class PaymentServices
{
    public function __construct(protected DB_UserPayment $DB_UserPayment,
                                protected DB_Users       $DB_Users,
                                protected DB_Clients     $DB_Clients,
                                protected DB_Packages    $DB_Packages,
                                protected DB_Coaches     $DB_Coaches,
    )
    {
    }

    public function index($request)
    {
        if ($request->ajax()) {
            $data = UsersPayment::with(['user'])->select('*');
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
                ->rawColumns(['user_name', 'user_phone', 'creation_date', 'status_tab'])
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
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "1");
            if ($get_the_order) {
                $coach_id = $get_the_order->coach_id;
                $get_the_coach = $this->DB_Users->get_user_info($coach_id);
                $coach_due_date = Carbon::parse($get_the_coach->due_date);

//                get the coach due date
                if ($coach_due_date->lt(Carbon::today()) || $get_the_order->package->amount != $amount) {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                } else {
                    $new_due_date = $coach_due_date->addMonth()->toDateString();
                }

                $this->DB_Users->update_user_due_date($coach_id, $new_due_date);
                $this->DB_UserPayment->update_user_payment_status($get_the_order, "2");
                if ($get_the_order->package->amount == $amount) {
                    //check if user need to downgrade the package
                    if ($get_the_order->first_pay == "0") $this->checkIfUserNeedToDowngradeThePackage($coach_id);
                } else {
                    $this->DB_Coaches->update_coach_package(coach_id: $coach_id, package_id: $get_the_order->package_id);
                }

                $success_msg = __('translate.PaymentSuccessMsg') . $new_due_date;
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
        $appropriate_package = $this->DB_Packages->get_appropriate_package($coach_active_clients);
        $this->DB_Coaches->update_coach_package($coach_id, $appropriate_package->id);
    }


}
