<?php

// require_once('vendor/autoload.php');

namespace App\Http\Controllers;

use App\Mail\Registered;
use App\Mail\Email;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Helper\GenerateJWT;
use App\Helper\RegisterUser;

use Firebase\JWT\JWT;
// use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    protected $passPattern;
    protected $emailPattern;

    public function __construct()
    {
        $this->passPattern = env("PASSWORD_FORMAT");
        $this->emailPattern = env("EMAIL_FORMAT");
    }

    public function getUsers()
    { //pagination
        return User::all();
    }


    public function deRegister(Request $request)
    {
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = (new GenerateJWT)->decodejwt($token);
            $email = $payload['sub'];

            $user = User::where('Email', $email)->first();

            if ($user->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Successfully dereggistered!']);
            }
        }
    }
}
