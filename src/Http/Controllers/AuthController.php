<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/AuthController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Truvoicer\TfPerspectives\Mail\WelcomeEmail;
use Truvoicer\TfPerspectives\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name'  => ['required', 'string', 'max:60'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
        ]);

        // Fire event to trigger Email Verification
        event(new Registered($user));

        // Send the Welcome Email
        Mail::to($user->email)->send(new WelcomeEmail($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('perspectives.index'))
            ->with('success', 'Welcome! Please check your email to verify your account.');
    }

    // ... login and logout methods stay the same ...

    /**
     * Send a reset link to the given user.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['success' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('perspectives.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Reset password from account settings (while logged in).
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match anyone here.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('perspectives.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('perspectives.index');
    }

    private function uniqueHandle(string $firstName, string $lastName): string
    {
        $base = Str::slug($firstName.' '.$lastName) ?: 'user';
        $displayName = $base;
        $i = 1;

        while (User::where('display_name', $displayName)->exists()) {
            $displayName = $base.'-'.(++$i);
        }

        return $displayName;
    }
}
