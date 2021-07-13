<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class PasswordController extends AuthController
{

    public function forgotPass(Request $request)
    {
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $user = User::where('Email', $payload['sub'])->first();

            if ($user) {
                $nowSeconds = time();
                $payload['iat'] = $nowSeconds;
                $payload['exp'] = $nowSeconds + (60 * 60);

                $newjwt = (new GenerateJWT)->genjwt($payload);
                $url = "http://localhost:8000/api/resetPass/?token=" . $newjwt;
                $email = strtolower($user->Email);
                Mail::to($email)->send(new Email($newjwt, "Reset Password", "emails.resetPass"));

                return response()->json(['status' => 'success', 'message' => 'Successfully sent Reset Password link to your email address.', 'token' => $newjwt]);
            }
        } else {
        }
    }

    public function resetPass(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|min:8|max: 255|max: 255|regex: ' . $this->passPattern,
        ]);

        $token = $request->token;
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) === "array") {
            $email = $payload['sub'];
            $user = User::where('Email', $email)->first();
            $user->Password = app('hash')->make($request->password);

            if ($user->save()) {
                $email = strtolower($email);
                Mail::to($email)->send(new Email("", "Password Changed", "emails.passChanged"));
                return response()->json(['status' => 'success', 'message' => 'Successfully changed password!']);
            }
        } else {
            return response()->json(['status' => 'failure', 'message' => 'Token expired!']);
        }
    }
}
