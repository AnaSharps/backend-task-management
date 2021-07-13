<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailController extends RegistrationController
{
    public function verifyEmail(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        return redirect('http://localhost:8000/register/signup/?token=' . $token);
    }
}
