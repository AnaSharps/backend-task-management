<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\User;
use App\Helper\RegisterUser;

class AdminController extends AuthController
{

    // public function AdminRedirect() {
    //     return response()->json(['status' => 'success']);
    // }
    
    public function addUser(Request $request)
    {
        //after admin validation through provider
        $this->validate($request, [
            'email' => 'required|max: 255|regex: ' . $this->emailPattern,
        ]);

        $email = $request->email;
        $user = User::where([['email', $email], ['isDeleted', false]])->first();

        if ($user && !($user->isDeleted)) {
            return response('This email has already been registered!', 422);
        }
        $token = $request->cookie('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        $createdBy = $payload['sub'];

        return (new RegisterUser)->register($email, $createdBy);
    }

    public function deleteUser(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
        ]);
        if ($request->cookie('token')) {
            $token = $request->cookie('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $email = $request->email;
            $adminEmail = $payload['sub'];

            $user = User::where([['email', $email], ['isDeleted', false]])->first();

            if ($user && !($user->isDeleted)) {
                $user->isDeleted = true;
                $user->deletedBy = $adminEmail;
                if ($user->save()) {
                    return response()->json(['status' => 'success', 'message' => 'Successfully deletd user!']);
                }
            } else {
                return response('No such user exists', 400);
            }
        } else {
            return response('Unauthorized Request', 403);
        }
    }
}
