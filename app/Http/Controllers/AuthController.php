<?php

// require_once('vendor/autoload.php');

namespace App\Http\Controllers;

use App\Mail\Registered;
use App\Mail\Email;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;
// use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private $key;
    public $passPattern;

    public function __construct()
    {
        $this->key = env("KEY_JWT");
        $this->passPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.,!@#$%^&]).+$/";
    }

    public function genjwt(array $payload)
    {
        if (!empty($payload)) {
            try {
                $jwt = JWT::encode($payload, $this->key);

                return $jwt;
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function decodejwt(String $jwt)
    {
        if (!empty($jwt)) {
            try {
                $decoded = JWT::decode($jwt, $this->key, array('HS256'));

                $decoded_array = (array) $decoded;

                return $decoded_array;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
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
            'username' => 'required|string',
            'password' => 'required|string|min:8|regex: ' . $this->passPattern,
        ]);

        // Check for password strength
        // $validPassword = $this -> checkPassword($request -> password);
        // if ($validPassword !== 'Success') {
        //     return response() -> json(['status' => 'failure', 'message' => $validPassword]);
        // }

        $token = $request->token;
        $payload = $this->decodejwt($token);

        if (gettype($payload) === "array") {
            $user = new User();
            $email = $payload['sub'];
            $user->Name = strtoupper($request->username);
            $user->Email = strtoupper($email);
            $user->Role = strtoupper('Normal');
            $user->Created_by = strtoupper($payload['createdBy']);
            $user->Password = app('hash')->make($request->password);

            if ($user->save()) {
                Mail::to($email)->send(new Registered());
                return response()->json(['status' => 'success', 'message' => 'Registered Successfully']);
            }
        } else {
            return response()->json(['status' => 'failure', 'message' => 'token expired']);
        }
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
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
            $jwt = $this->genjwt($payload);

            return response()->json(['status' => 'success', 'message' => 'Successfully Logged in!', 'token' => $jwt]);
        } else {
            return response()->json(['status' => 'failure', 'message' => 'Invalid credentials']);
        }
    }

    private function register(String $email, String $createdBy)
    {
        $nowSeconds = time();
        $payload = array(
            'sub' => $email,
            'createdBy' => $createdBy,
            'iat' => $nowSeconds,
            'exp' => $nowSeconds + (60 * 60),
        );

        $newjwt = $this->genjwt($payload);
        $subject = "Email Verification";
        $view = "emails.verificationEmail";
        Mail::to($email)->send(new Email($newjwt, $subject, $view));
        return response()->json(['status' => "success", "message" => "Email Verification link has been sent to your email address. Please Click the link to complete your registration!", 'token' => $newjwt]);
    }

    public function registerSelf(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
        ]);

        $email = $request->email;
        $createdBy = $request->email;

        return $this->register($email, $createdBy);
    }

    public function addUser(Request $request)
    {
        //after admin validation through provider
        $this->validate($request, [
            'email' => 'required|email|unique:users',
        ]);
        $token = $request->bearerToken('token');
        $payload = $this->decodejwt($token);

        $email = $request->email;
        $createdBy = $payload['sub'];

        return $this->register($email, $createdBy);
    }

    public function forgotPass(Request $request)
    {
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = $this->decodejwt($token);
            $user = User::where('Email', $payload['sub'])->first();

            if ($user) {
                $nowSeconds = time();
                $payload['iat'] = $nowSeconds;
                $payload['exp'] = $nowSeconds + (60 * 60);

                $newjwt = $this->genjwt($payload);
                $url = "http://localhost:8000/api/resetPass/?token=" . $newjwt;
                $email = strtolower($user->Email);
                Mail::to($email)->send(new Email($newjwt, "Reset Password", "emails.resetPass"));

                return response()->json(['status' => 'success', 'message' => 'Successfully sent Reset Password link to your email address.', 'token' => $newjwt]);
            }
        } else {
        }
    }

    public function resetPass(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|min:8|regex: ' . $this->passPattern,
        ]);

        $token = $request->token;
        $payload = $this->decodejwt($token);

        if (gettype($payload) === "array") {
            $email = $payload['sub'];
            $user = User::where('Email', $email)->first();
            $user->Password = app('hash')->make($request->password);

            if ($user->save()) {
                $email = strtolower($email);
                Mail::to($email)->send(new Email("", "Password Changed", "emails.passChanged"));
                return response()->json(['status' => 'success', 'message' => 'Successfully changed password!']);
            }
        } else {
            return response()->json(['status' => 'failure', 'message' => 'Token expired!']);
        }
    }

    public function deRegister(Request $request)
    {
        if ($request->bearerToken('token')) {
            $token = $request->bearerToken('token');

            $payload = $this->decodejwt($token);
            $email = $payload['sub'];

            $user = User::where('Email', $email)->first();

            if ($user->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Successfully dereggistered!']);
            }
        }
    } //soft delete
}
