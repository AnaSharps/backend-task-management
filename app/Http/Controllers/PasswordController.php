<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use App\Events\NotificationsEvent;
use Exception;

class PasswordController extends AuthController
{

    public function forgotPass(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
        ]);

        $email = $request->email;
        $email = strtolower($email);

        $nowSeconds = time();
        $payload = array([
            'sub' => $email,
            'iat' => $nowSeconds,
            'exp' => $nowSeconds + (60 * 60),
        ]);

        $newjwt = (new GenerateJWT)->genjwt($payload);
        if (User::where('email', $email)->first()) {
            event(new NotificationsEvent('Password Reset Email sent!'));
            dispatch(new SendEmail($email, "Reset Password", "emails.resetPass"));
        }

        return response()->json(['status' => 'success', 'message' => 'If this email is registered, a reset password link has been sent to you.', 'token' => $newjwt]);
    }


    public function resetPass(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'password' => 'required|string|min:8|max: 255|regex: ' . $this->passPattern,
        ]);

        $token = $request->token;
        $payload = (new GenerateJWT)->decodejwt($token);
            if (gettype($payload) === "array") {
                $email = $payload[0]->sub;
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->password = app('hash')->make($request->password);
                
                    if ($user->save()) {
                        $email = strtolower($email);

                        event(new NotificationsEvent('Password Changed!'));
                        dispatch(new SendEmail($email, "Password Changed", "emails.passChanged"));
                        return response()->json(['status' => 'success', 'message' => 'Successfully changed password!']);
                    }
                }
            } else {
                event(new NotificationsEvent('Token Expired', 403));
                return response("Token Expired", 403);
            }
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string|min: 8|max: 255|regex: ' . $this->passPattern,
            'newPassword' => 'required|string|min: 8|max: 255|regex: ' . $this->passPattern,
        ]);
        $token = $request->cookie('token');

        $payload = (new GenerateJWT)->decodejwt($token);
        $user = User::where('email', $payload['sub'])->first();

        if ($user && app('hash')->check($request->password, $user->password)) {
            $user->password = app('hash')->make($request->newPassword);

            $user->save();
            $email = strtolower($user->email);
            event(new NotificationsEvent('Password Changed!'));
            dispatch(new SendEmail($email, "Password Changed", "emails.passChanged"));

            return response()->json(['status' => 'success', 'message' => 'Successfully changed password!']);
        } else {
            return response('Wrong Password!', 401);
        }
    }
}
