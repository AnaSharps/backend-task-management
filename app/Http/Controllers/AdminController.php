<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Models\User;
use App\Helper\RegisterUser;

class AdminController extends AuthController
{

    public function addUser(Request $request)
    {
        //after admin validation through provider
        $this->validate($request, [
            'email' => 'required|max: 255|regex: ' . $this->emailPattern,
        ]);

        $email = $request->email;
        $user = User::where('Email', $email)->first();
        if ($user && !($user->is_deleted)) {
            return response('This email has already been registered!', 422);
        }
        $token = $request->bearerToken('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        $email = $request->email;
        $createdBy = $payload['sub'];

        return (new RegisterUser)->register($email, $createdBy);
    }

    public function deleteUser(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
        ]);
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $email = $request->email;
            $adminEmail = $payload['sub'];

            $user = User::where('Email', $email)->first();

            if ($user && !($user->is_deleted)) {
                $user->is_deleted = true;
                $user->Deleted_by = $adminEmail;
                if ($user->save()) {
                    return response()->json(['status' => 'success', 'message' => 'Successfully deletd user!']);
                }
            } else {
                return response('No such user exists', 400);
            }
        } else {
            return response('Unauthorized Request', 401);
        }
    }
}
