<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Helper\RegisterUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class RegistrationController extends AuthController
{
    public function registerSelf(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
        ]);
        $email = $request->email;
        $email = strtolower($email);

        // try {
        $user = User::where([['email', $email], ['isDeleted', false]])->first();
        // dd($user->is_deleted);
        if ($user && !($user->isDeleted)) {
            return response('This email has already been registered!', 422);
        } else {
            $createdBy = $request->email;

            return (new RegisterUser)->register($email, $createdBy);
        }
        // } catch (\Exception $e) {
        //     return response($e->getMessage(), 500);
        // }
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

        if (gettype($payload) === "array") {
            $email = $payload['sub'];

            $currUser = User::where([['email', $email], ['isDeleted', false]])->first();
            if ($currUser && !($currUser->isDeleted)) {
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
                    Mail::to($email)->send(new Email("", "Successfully Registered!", "emails.registered"));
                    return response()->json(['status' => 'success', 'message' => 'Registered Successfully', 'token' => $jwt]);
                }
            }
        } else {
            return response()->json(['status' => 'failure', 'message' => 'token expired']);
        }
        // } else {
        //     return $response->getErrorCodes();
        // }
    }
}
