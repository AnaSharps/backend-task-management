<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\User;
use App\Events\NotificationsEvent;

class DeRegisterController extends AuthController
{
    public function deRegister(Request $request)
    {
        if ($request->cookie('token')) {
            $token = $request->cookie('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $email = $payload['sub'];

            $user = User::where([['email', $email], ['isDeleted', false]])->first();

            if ($user && !($user->isDeleted)) {
                $user->isDeleted = true;
                $user->deletedBy = $email;
                if ($user->save()) {
                    event(new NotificationsEvent('Successfully dereggistered!'));
                    return response()->json(['status' => 'success', 'message' => 'Successfully dereggistered!']);
                }
            } else {
                event(new NotificationsEvent('No such user exists!', false));
                return response()->json(['status' => 'failure', 'message' => 'No such user exists']);
            }
        }
    }
}
