<?php

namespace App\Helper;

use App\Helper\GenerateJWT;
use App\Events\NotificationsEvent;
use App\Jobs\SendEmail;
use App\Mail\Email;
use Illuminate\Support\Facades\Mail;

class RegisterUser
{
    public function __construct()
    {
    }


    public function register(String $email, String $createdBy)
    {
        $nowSeconds = time();
        $payload = array(
            'sub' => $email,
            'createdBy' => $createdBy,
            'iat' => $nowSeconds,
            'exp' => $nowSeconds + (60 * 60),
        );

        $newjwt = (new GenerateJWT)->genjwt($payload);
        $subject = "Email Verification";
        $view = "emails.verificationEmail";
        dispatch(new SendEmail($email, $subject, $view));
        event(new NotificationsEvent('Sent Verification Email!'));
        return response()->json(['status' => "success", "message" => "Email Verification link has been sent to your email address. Please Click the link to complete your registration!"]);
    }
}
