<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\User;

class DeRegisterController extends AuthController
{
    public function deRegister(Request $request)
    {
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $email = $payload['sub'];

            $user = User::where([['email', $email], ['isDeleted', false]])->first();

            if ($user && !($user->isDeleted)) {
                $user->isDeleted = true;
                $user->deletedBy = $email;
                if ($user->save()) {
                    return response()->json(['status' => 'success', 'message' => 'Successfully dereggistered!']);
                }
            } else {
                return response()->json(['status' => 'failure', 'message' => 'No such user exists']);
            }
        }
    }
}
