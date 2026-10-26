<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/VerificationController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('perspectives.index');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('perspectives.index')->with('success', 'Email verified!');
    }

    public function notice(Request $request)
    {
        return inertia('@tf-perspectives::auth/verify-email');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('perspectives.index');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    }
}
