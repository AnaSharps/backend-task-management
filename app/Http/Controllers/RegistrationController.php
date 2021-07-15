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
        $user = User::where('Email', $email)->first();
        // dd($user->is_deleted);
        if ($user && !($user->is_deleted)) {
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
        ]);

        $token = $request->token;
        $payload = (new GenerateJWT)->decodejwt($token);

        if (gettype($payload) === "array") {
            $user = new User();
            $email = $payload['sub'];
            $user->Name = strtoupper($request->username);
            $user->Email = strtoupper($email);
            $user->Role = strtoupper('Normal');
            $user->Created_by = strtoupper($payload['createdBy']);
            $user->Password = app('hash')->make($request->password);

            if ($user->save()) {
                Mail::to($email)->send(new Email("", "Successfully Registered!", "emails.registered"));
                return response()->json(['status' => 'success', 'message' => 'Registered Successfully']);
            }
        } else {
            return response()->json(['status' => 'failure', 'message' => 'token expired']);
        }
    }
}
