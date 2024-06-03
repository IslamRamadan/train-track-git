<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login_form()
    {

        return view('dashboard.auth.login');

    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ];
            $messages = [
                'email.required' => 'Email Address is required',
                'email.email' => 'Valid Email is required',
                'password.required' => 'Password is required',
            ];
            $this->validate($request, $rules, $messages);
            if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->route('coaches.index', app()->getLocale());
            } else {
                return redirect()->back()->withErrors(['msg' => 'Invalid Email or Password']);
            }
        }
        return view('dashboard.auth.login');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
