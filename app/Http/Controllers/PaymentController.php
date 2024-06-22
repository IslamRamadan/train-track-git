<?php

namespace App\Http\Controllers;

use App\Services\PaymentServices;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentServices $paymentServices)
    {

    }

    public function checkout_processed(Request $request)
    {
        return $this->paymentServices->checkout_processed($request);
    }

    public function checkout_response(Request $request)
    {
        dd($request->all());
    }


}
