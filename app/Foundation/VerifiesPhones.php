<?php

namespace App\Foundation;

use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

trait VerifiesPhones
{
    use RedirectsUsers;

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedPhone()
            ? redirect($this->redirectPath())
            : view('auth.verify.phone');
    }

    /**
     * Mark the authenticated user's phone number as verified.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function verify(Request $request)
    {
        $key = sprintf('phone_verification_%s', md5($request->user()->id));
        if (password_verify($request->code, cache()->driver('redis')->get($key)) && $request->user()->markPhoneAsVerified()) {
            event(new Verified($request->user()));
            cache()->driver('redis')->forget($key);
        } else {
            return back()->withErrors(['code' => ['Invalid verification code']]);
        }

        return redirect($this->redirectPath());
    }

    /**
     * Resend the phone verification notification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $request->user()->sendPhoneVerificationNotification();

        return view('auth.verify.phone')->with('resent', true);
    }
}
