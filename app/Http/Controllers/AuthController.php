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

    public function verifyEmail(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        return redirect('http://localhost:8000/register/signup/?token=' . $token);
    }

    public function signup(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'username' => 'required|string|max: 50',
            'password' => 'required|string|min:8|max: 255|regex: ' . $this->passPattern,
        ]);

        // Check for password strength
        // $validPassword = $this -> checkPassword($request -> password);
        // if ($validPassword !== 'Success') {
        //     return response() -> json(['status' => 'failure', 'message' => $validPassword]);
        // }

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

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|regex: ' . $this->emailPattern,
            'password' => 'required|min: 8|max: 255|string|regex: ' . $this->passPattern,
        ]);


        $user = User::where('Email', strtoupper($request->email))->first();

        if ($user && app('hash')->check($request->password, $user['Password'])) {
            $nowTime = time();
            $payload = array(
                'iss' => $user->Name,
                'sub' => $user->Email,
                'createdBy' => $user->Created_by,
                'role' => $user->Role,
                'iat' => $nowTime,
                'exp' => $nowTime + (60 * 60 * 24),
            );
            $jwt = (new GenerateJWT)->genjwt($payload);

            return response()->json(['status' => 'success', 'message' => 'Successfully Logged in!', 'token' => $jwt]);
        } else {
            return response()->json(['status' => 'failure', 'message' => 'Invalid credentials']);
        }
    }

    public function registerSelf(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max: 255|unique:users|regex: ' . $this->emailPattern,
        ]);

        $email = $request->email;
        $createdBy = $request->email;

        return (new RegisterUser)->register($email, $createdBy);
    }

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
    } //soft delete
}
