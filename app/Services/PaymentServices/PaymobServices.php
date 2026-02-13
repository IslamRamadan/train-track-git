<?php

namespace App\Services\PaymentServices;

use App\Models\UsersPayment;
use App\Services\GymServices;
use App\Services\DatabaseServices\DB_Coach_Gyms;
use App\Services\DatabaseServices\DB_Clients;
use App\Services\DatabaseServices\DB_Coaches;
use App\Services\DatabaseServices\DB_GymPayments;
use App\Services\DatabaseServices\DB_Gyms;
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

class PaymobServices
{
    public function __construct(
        protected DB_UserPayment    $DB_UserPayment,
        protected DB_Users          $DB_Users,
        protected DB_Clients        $DB_Clients,
        protected DB_Packages       $DB_Packages,
        protected DB_Coaches        $DB_Coaches,
        protected DB_PendingClients $DB_PendingClients,
        protected ValidationServices $validationServices,
        protected DB_GymPayments    $DB_GymPayments,
        protected DB_Gyms          $DB_Gyms,
        protected DB_Coach_Gyms    $DB_Coach_Gyms,
    ) {}

    public function index($request)
    {
        if ($request->ajax()) {
            $data = UsersPayment::with(['user'])->select('*')->orderBy('created_at', 'desc');
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


    public function pay($amount, $full_name, $email, $description, $phone)
    {
        $auth = PayMob::AuthenticationRequest();
        $payment_link_image = asset('images/logo.png');
        return PayMob::createPaymentLink($auth->token, $payment_link_image, $amount * 100, $full_name, $email, $description, $phone);
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
        $order_id = $request['obj']['order'] ?? null;
        $amount = isset($request['obj']['amount_cents']) ? $request['obj']['amount_cents'] / 100 : null;
        $success = $request['obj']['success'] ?? null;

        Log::info('[PaymentWebhook] Received callback', [
            'success' => $success,
            'order_id' => $order_id,
            'amount' => $amount,
            'raw_obj' => $request['obj'] ?? [],
        ]);

        if ($request['obj']['success'] == "true") {
            $order_id = $request['obj']['order'];
            $amount = $request['obj']['amount_cents'] / 100;
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "1");

            if ($get_the_order) {
                Log::info('[PaymentWebhook] Matched COACH payment', [
                    'payment_id' => $get_the_order->id,
                    'coach_id' => $get_the_order->coach_id,
                    'package_id' => $get_the_order->package_id,
                    'upgrade' => $get_the_order->upgrade,
                    'first_pay' => $get_the_order->first_pay ?? '0',
                ]);

                $coach_id = $get_the_order->coach_id;
                $get_the_coach = $this->DB_Users->get_user_info($coach_id);
                $coach_due_date = Carbon::parse($get_the_coach->due_date);

                if ($coach_due_date->lt(Carbon::today()) || $get_the_order->upgrade == "1") {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                    Log::info('[PaymentWebhook] Coach due date: using today+1month', [
                        'reason' => $get_the_order->upgrade == "1" ? 'upgrade' : 'due_date_past',
                        'old_due_date' => $get_the_coach->due_date,
                        'new_due_date' => $new_due_date,
                    ]);
                } else {
                    $new_due_date = $coach_due_date->addMonth()->toDateString();
                    Log::info('[PaymentWebhook] Coach due date: extended by 1 month', [
                        'old_due_date' => $get_the_coach->due_date,
                        'new_due_date' => $new_due_date,
                    ]);
                }

                $this->DB_Users->update_user_due_date($coach_id, $new_due_date);
                $this->DB_UserPayment->update_user_payment_status($get_the_order, "2");

                if ($get_the_order->upgrade == "0") {
                    if (($get_the_order->first_pay ?? "0") == "0") {
                        Log::info('[PaymentWebhook] Coach: checking downgrade after payment');
                        $this->checkIfUserNeedToDowngradeThePackage($coach_id);
                    }
                } else {
                    Log::info('[PaymentWebhook] Coach: upgrading package', [
                        'new_package_id' => $get_the_order->package_id,
                    ]);
                    $this->DB_Coaches->update_coach_package(coach_id: $coach_id, package_id: $get_the_order->package_id);
                }

                Log::info('[PaymentWebhook] Coach payment completed successfully', ['coach_id' => $coach_id]);
                $success_msg = __('translate.PaymentSuccessMsg') . $new_due_date;
                return sendResponse(["msg" => $success_msg]);
            }

            $get_the_gym_order = $this->DB_GymPayments->find_gym_payment($order_id, $amount, "1");
            if ($get_the_gym_order) {
                Log::info('[PaymentWebhook] Matched GYM payment', [
                    'gym_payment_id' => $get_the_gym_order->id,
                    'gym_id' => $get_the_gym_order->gym_id,
                    'package_id' => $get_the_gym_order->package_id,
                    'upgrade' => $get_the_gym_order->upgrade,
                ]);

                $gym_id = $get_the_gym_order->gym_id;
                $package_id = $get_the_gym_order->package_id;
                $is_upgrade = $get_the_gym_order->upgrade == "1";

                $this->DB_GymPayments->update_gym_payment_status($get_the_gym_order, "2");
                $this->DB_Gyms->update_gym_package($gym_id, $package_id);
                Log::info('[PaymentWebhook] Gym: payment status set to Paid, gym package updated', [
                    'gym_id' => $gym_id,
                    'package_id' => $package_id,
                ]);

                if (!$is_upgrade) {
                    $gymServices = app(GymServices::class);
                    $gym_package = $gymServices->getGymCurrentPackage($gym_id);
                    $this->DB_Gyms->update_gym_package($gym_id, $gym_package->id);
                    Log::info('[PaymentWebhook] Gym: applied downgrade check', [
                        'gym_id' => $gym_id,
                        'package_id_after_downgrade' => $gym_package->id,
                    ]);
                }

                $gym = $this->DB_Gyms->find_gym($gym_id);
                $owner_due_date = $gym->owner->due_date ?? null;
                $owner_due_date_carbon = $owner_due_date ? Carbon::parse($owner_due_date) : null;

                if (!$owner_due_date_carbon || $owner_due_date_carbon->lt(Carbon::today()) || $is_upgrade) {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                    Log::info('[PaymentWebhook] Gym coaches due date: today+1month', [
                        'reason' => !$owner_due_date_carbon ? 'no_owner_due_date' : ($is_upgrade ? 'upgrade' : 'owner_due_date_past'),
                        'owner_due_date' => $owner_due_date,
                        'new_due_date' => $new_due_date,
                    ]);
                } else {
                    $new_due_date = $owner_due_date_carbon->copy()->addMonth()->toDateString();
                    Log::info('[PaymentWebhook] Gym coaches due date: owner due + 1 month', [
                        'owner_due_date' => $owner_due_date,
                        'new_due_date' => $new_due_date,
                    ]);
                }

                $coach_ids = $this->DB_Coach_Gyms->get_all_gym_coach_ids($gym_id);
                $updated = $this->DB_Users->update_users_due_date_by_ids($coach_ids, $new_due_date);
                Log::info('[PaymentWebhook] Gym: extended due date for all coaches', [
                    'gym_id' => $gym_id,
                    'coach_count' => count($coach_ids),
                    'coach_ids' => $coach_ids,
                    'rows_updated' => $updated,
                    'new_due_date' => $new_due_date,
                ]);

                Log::info('[PaymentWebhook] Gym payment completed successfully', ['gym_id' => $gym_id]);
                $success_msg = __('translate.PaymentSuccessMsg') . Carbon::today()->addMonth()->toDateString();
                return sendResponse(["msg" => $success_msg]);
            }

            Log::warning('[PaymentWebhook] No matching order found (neither coach nor gym)', [
                'order_id' => $order_id,
                'amount' => $amount,
            ]);
            $success_msg = "Payment Failed";
            return sendResponse(["msg" => $success_msg]);
        }

        Log::warning('[PaymentWebhook] Callback success != true', [
            'success' => $success ?? 'missing',
            'full_request' => $request->all(),
        ]);
        $success_msg = "Payment Failed";
        return sendResponse(["msg" => $success_msg]);
    }
}
