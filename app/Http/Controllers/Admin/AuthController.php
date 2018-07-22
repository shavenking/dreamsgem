<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function getLogin()
    {
        return view('admin.login');
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::guard('admins')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ], true)) {
            return redirect()->route('admin.auth.get-login');
        }

        return redirect()->intended(route('admin.home'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('admin.auth.get-login');
    }
}
