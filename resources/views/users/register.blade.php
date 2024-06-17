@extends('partials.layout')
@section('title')
    {{__('translate.Register')}}
@endsection
@section('style')
    <link rel="stylesheet" href="{{asset('front/style.css')}}">
    <style>

    </style>
@endsection
@section('content')
    <div class="form-register">
        <form action="{{route('coach.register',app()->getLocale())}}" method="post">
            @csrf
            <div class="form-group">
                <label for="InputName">{{__('translate.Name')}}</label>
                <input type="text" id="InputName" name="name" class="form-control" data-validation="required"
                       placeholder="{{__('translate.Name')}}">
            </div>
            <div class="form-group">
                <label for="InputEmail">{{__('translate.Email')}}</label>
                <input type="email" name="email" class="form-control" id="InputEmail" data-validation="email"
                       aria-describedby="emailHelp"
                       placeholder="{{__('translate.Email')}}">
            </div>
            <div class="form-group">
                <label for="InputPhone">{{__('translate.Phone')}}</label>
                <input type="text" id="InputPhone" name="phone" class="form-control" data-validation="required"
                       placeholder="{{__('translate.Phone')}}">
            </div>
            <div class="form-group">
                <label for="InputGym">{{__('translate.Gym')}}</label>
                <input type="text" id="InputGym" name="gym" class="form-control" data-validation="required"
                       placeholder="{{__('translate.Gym')}}">
            </div>
            <div class="form-group">
                <label for="InputSpeciality">{{__('translate.Speciality')}}</label>
                <input type="text" id="InputSpeciality" name="speciality" class="form-control"
                       data-validation="required"
                       placeholder="{{__('translate.Speciality')}}">
            </div>
            <div class="form-group">
                <label for="InputCertificates">{{__('translate.Certificates')}}</label>
                <input type="text" id="InputCertificates" name="certificates" class="form-control"
                       data-validation="required"
                       placeholder="{{__('translate.Certificates')}}">
            </div>
            <div class="form-group">
                <label for="InputPassword">{{__('translate.Password')}}</label>
                <input type="password" class="form-control" id="InputPassword" name="password"
                       data-validation="required"
                       placeholder="{{__('translate.Password')}}">
            </div>
            <div class="form-group">
                <label for="InputConfirmPassword">{{__('translate.ConfirmPassword')}}</label>
                <input type="password" class="form-control" id="InputConfirmPassword" name="password_confirmation"
                       data-validation="required"
                       placeholder="{{__('translate.ConfirmPassword')}}">
            </div>

            <div class="custom-control custom-radio custom-control-inline mb-3">
                <input type="radio" id="pay_now" name="pay_now" value="1" class="custom-control-input">
                <label class="custom-control-label" for="pay_now">{{__('translate.PayNow')}}</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-3">
                <input type="radio" id="free_trial" name="pay_now" value="0" class="custom-control-input">
                <label class="custom-control-label" for="free_trial">{{__('translate.30DaysFree')}}</label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-dark w-100">{{__('translate.SignUp')}}</button>
            </div>
        </form>
    </div>

@endsection
