<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helper\GenerateJWT;

class LoginController extends AuthController
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
            'password' => 'required|max: 255|string',
        ]);

        $user = User::where([['email', strtoupper($request->email)], ['isDeleted', false]])->first();

        if ($user && !($user->isDeleted) && app('hash')->check($request->password, $user->password)) {
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

            return response()->json(['status' => 'success', 'message' => 'Successfully Logged in!', 'token' => $jwt]);
        } else {
            return response()->json(['status' => 'failure', 'message' => 'Invalid credentials']);
        }
    }
}
