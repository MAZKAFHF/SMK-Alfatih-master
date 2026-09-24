<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot-password')->with('title', 'Lupa Password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:150']]);

        // Do not reveal if email exists — always return same response
        $status = Password::sendResetLink($request->only('email'));

        return back()->with(['success' => __($status) === Password::RESET_LINK_SENT ? 'Jika email terdaftar, tautan reset telah dikirim (cek log/email).' : 'Jika email terdaftar, tautan reset telah dikirim.']);
    }

    public function showResetForm(Request $request, ?string $token = null)
    {
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->email])->with('title', 'Reset Password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
