<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationContollers extends Controller
{
    //
    public function sendVerificationEmail(Request $request)
    {
        if($request->user()->hasVerifiedEmail())
        {
            return [
                'message' => 'Already Verified'
            ];
        }

        $request->user()->sendEmailVerificationNotification();

            return [ 
                    'status' => 'verification-email-sent'
                ];
    }

    public function verify(EmailVerificationRequest $request)
    {
        if($request->user()->hasVerifiedEmail())
        {
            return [ 
                    'message' => 'Email Already Verified'
                ];
        }

        if($request->user()->markEmailAsVerified())
        {
            event(new Verified($request->user()));
        }

            return [
                'message' => 'Email has been verified'
            ];
    }
}
