<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/AuthController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Truvoicer\TfPerspectives\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'     => ['required', 'string', 'max:60'],
            'last_name'      => ['required', 'string', 'max:60'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'email'    => $data['email'],
            'password' => $data['password'], // hashed cast on model
            'display_name'   => $this->uniqueHandle($data['first_name'], $data['last_name']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('perspectives.index'));
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
