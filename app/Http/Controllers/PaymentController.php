<?php

namespace App\Http\Controllers;

use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentServices $paymentServices,
                                protected DB_UserPayment  $DB_UserPayment, protected DB_Users $DB_Users)
    {

    }

    public function checkout_processed(Request $request)
    {
        return $this->paymentServices->checkout_processed($request);
    }

    public function checkout_response(Request $request)
    {
//        id, pending,amount_cents,success,is_auth,is_capture,is_standalone_payment,is_voided,is_refunded,is_3d_secure,integration_id,profile_id,has_parent_transaction,order,created_at,currency,merchant_commission,discount_details,is_void,is_refund,error_occured,refunded_amount_cents,captured_amount,updated_at,is_settled,bill_balanced,is_bill,owner,data_message,source_data_type,source_data_pan,source_data_sub_type,acq_response_code,txn_response_code,hmac
        if ($request->success == "true") {
            $order_id = $request->order;
            $amount = $request->amount_cents / 100;
            $get_the_order = $this->DB_UserPayment->find_user_payment($order_id, $amount, "1");
            if ($get_the_order) {
                $get_the_coach = $this->DB_Users->get_user_info($get_the_order->coach_id);
                $coach_due_date = Carbon::parse($get_the_coach->due_date);
                if ($coach_due_date->lt(Carbon::today())) {
                    $new_due_date = Carbon::today()->addMonth()->toDateString();
                } else {
                    $new_due_date = $coach_due_date->addMonth()->toDateString();
                }
                $this->DB_Users->update_user_due_date($get_the_coach->id, $new_due_date);
                $this->DB_UserPayment->update_user_payment_status($get_the_order, "2");
                $success_msg = __('translate.PaymentSuccessMsg') . $new_due_date;
                return view('payment.payment_done', compact('success_msg', 'order_id'));
            } else {
                return view('payment.payment_failed');
            }
        }
        return view('payment.payment_failed');
    }


}
