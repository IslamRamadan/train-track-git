<?php

namespace App\Http\Controllers;

use App\Services\DatabaseServices\DB_UserPayment;
use App\Services\DatabaseServices\DB_Users;
use App\Services\PaymentServices\PaymentServices;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentServices $paymentServices,
                                protected DB_UserPayment  $DB_UserPayment, protected DB_Users $DB_Users)
    {

    }

    public function index(Request $request)
    {
        return $this->paymentServices->index($request);
    }

    public function checkout_response(Request $request)
    {
        return $this->paymentServices->checkout_response($request);
    }


}
