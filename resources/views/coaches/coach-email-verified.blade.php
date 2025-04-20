@extends('partials.layout')
@section('title')
    Email Verified
@endsection
@section('style')
    <link rel="stylesheet" href="{{asset('front/style.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
    <style>
        ._failed {
            border-bottom: solid 4px red !important;
        }

        ._failed i {
            color: red !important;
        }

        ._success {
            box-shadow: 0 15px 25px #00000019;
            padding: 45px;
            width: 100%;
            text-align: center;
            margin: 40px auto;
            border-bottom: solid 4px #28a745;
        }

        ._success i {
            font-size: 100px;
            color: #28a745;
        }

        ._success h2 {
            margin-bottom: 12px;
            font-size: 35px;
            font-weight: 500;
            line-height: 1.2;
            margin-top: 10px;
        }

        ._success p {
            margin-bottom: 0px;
            font-size: 18px;
            color: #495057;
            font-weight: 500;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="message-box _success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                    <h2>Email Verified</h2>
                    <h4>Welcome {{$name}} to Train Track application</h4>
                    <br>
                    <a class="btn btn-dark text-light w-100"
                       href="{{env('PORTAL_URL',"https://app.traintrackcoach.com")}}">{{__('translate.GoToLoginPage')}}</a>
                </div>
            </div>
        </div>
        <hr>
    </div>
@endsection
