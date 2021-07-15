<?php

namespace App\Helper;

use App\Helper\GenerateJWT;
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
        Mail::to($email)->send(new Email($newjwt, $subject, $view));
        return response()->json(['status' => "success", "message" => "Email Verification link has been sent to your email address. Please Click the link to complete your registration!", 'token' => $newjwt]);
    }
}
