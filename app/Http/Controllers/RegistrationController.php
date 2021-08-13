<?php

namespace App\Http\Controllers;

use App\Events\NotificationsEvent;
use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Helper\RegisterUser;
use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use Symfony\Component\HttpFoundation\Cookie;

class RegistrationController extends AuthController
{
    public function registerSelf(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
        ]);
        $email = $request->email;
        $email = strtolower($email);

        $user = User::where([['email', $email], ['isDeleted', false]])->first();
        if ($user && !($user->isDeleted)) {
            return response('This email has already been registered!', 400);
        } else {
            $createdBy = $request->email;

            return (new RegisterUser)->register($email, $createdBy);
        }
    }

    public function signup(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'username' => 'required|string|max: 50',
            'password' => 'required|string|min:8|max: 255|regex: ' . $this->passPattern,
            // 'g-recaptcha-response' => 'required',
        ]);

        // $response = $this->recaptcha->verify($request->input('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);

        // if ($response->isSuccess()) {
        $token = $request->token;
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) !== "array") {
            event(new NotificationsEvent("Token expired", false));
            return response("Expired token", 403);
        }
        $email = $payload['sub'];

        $currUser = User::where([['email', $email], ['isDeleted', false]])->first();
        if ($currUser && !($currUser->isDeleted)) {
            event(new NotificationsEvent("User already exists", false));
            return response('User already exists!', 400);
        } else {
            $user = new User();
            $user->name = strtoupper($request->username);
            $user->email = strtoupper($email);
            $user->role = strtoupper('Normal');
            $user->createdBy = strtoupper($payload['createdBy']);
            $user->password = app('hash')->make($request->password);

            if ($user->save()) {
                $nowTime = time();
                $payload = array(
                    'iss' => $user->name,
                    'sub' => $user->email,
                    'createdBy' => $user->createdBy,
                    'role' => $user->role,
                    'iat' => $nowTime,
                    'exp' => $nowTime + (60 * 60 * 24),
                );
                $jwt = (new GenerateJWT)->genjwt($payload);
                dispatch(new SendEmail($email, "Successfully Registered!", "emails.registered"));
                event(new NotificationsEvent("Successfully Registered!"));
                return response()->json(['status' => 'success', 'message' => 'Registered Successfully', 'user' => $$user])->cookie(new Cookie('token', $jwt));
            }
        }
        // } else {
        //     return $response->getErrorCodes();
        // }
    }
}
