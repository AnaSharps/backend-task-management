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
            'email' => 'required|max: 255|unique:users|regex: ' . $this->emailPattern,
        ]);
        $token = $request->bearerToken('token');
        $payload = (new GenerateJWT)->decodejwt($token);

        $email = $request->email;
        $createdBy = $payload['sub'];

        return (new RegisterUser)->register($email, $createdBy);
    }
}
