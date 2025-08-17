<?php

namespace App\Http\Controllers;

use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymobServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected PaymobServices $paymentServices,
                                protected DB_UserPayment $DB_UserPayment, protected DB_Users $DB_Users)
    {

    }

    public function index(Request $request)
    {
        return $this->paymentServices->index($request);
    }

    public function update_order_status($lang,$order_id,Request $request)
    {
        return $this->paymentServices->update_order_status($order_id,$request);
    }

    public function checkout_response(Request $request)
    {
        return $this->paymentServices->checkout_response($request);
    }

    public function checkout_processed(Request $request)
    {
        return $this->paymentServices->checkout_processed($request);
    }


}
