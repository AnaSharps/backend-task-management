<?php

// require_once('vendor/autoload.php');

namespace App\Http\Controllers;

use App\Mail\Registered;
use App\Mail\EmailVerification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private $priv_key;
    private $pub_key;
    
    public function __construct(){
        $this->priv_key = file_get_contents(dirname(dirname(__FILE__)).'../../../key.pem');
        $this->pub_key = file_get_contents(dirname(dirname(__FILE__)).'../../../public.pem');
    }
    
    public function getUsers() {
        return User::all();
    }

    public function verifyEmail(Request $request) {
        $this -> validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request -> token;

        return redirect('http://localhost:8000/register/signup/?token='.$token);
    }

    public function signup(Request $request) {
        $this -> validate($request, [
            'token' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check for password strength
        $validPassword = $this -> checkPassword($request -> password);
        if ($validPassword !== 'Success') {
            return response() -> json(['status' => 'failure', 'message' => $validPassword]);
        }

        $token = $request -> token;
        $payload = $this -> decodejwt($token);

        try {
            if (gettype($payload) === "array") {
                $user = new User();
                $user -> Name = $payload['iss'];
                $user -> Email = $payload['sub'];
                $user -> Role = 'Normal';
                $user -> Created_by = $payload['createdBy'];
                $user -> Password = app('hash') -> make($request->password);

                if ($user -> save()) {
                    return response() -> json(['status' => 'success', 'message' => 'Registered Successfully']);
                }
            } else {
                return response() -> json(['status' => 'failure', 'message' => 'token expired']);
            }
            
        } catch (\Exception $e) {
            return response() -> json(['status' => 'failure', 'message' => $e -> getMessage()]);
        }
    }

    public function login(Request $request) {
        $this -> validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        
        try {
            // $hashedPass = app('hash') -> make($request -> password);
            $user = User::where('email', $request -> email) -> first();

            if ($user && app('hash') -> check($request->password, $user['password'])) {
                $nowTime = time();
                $payload = array(
                    'sub' => $request -> email,
                    'pass' => $request -> password,
                    'iat' => $nowTime,
                    'exp' => $nowTime + (60*2),
                );
                $jwt = JWT::encode($payload, $this->priv_key, 'RS256');
                
                return response() -> json(['status' => 'success', 'message' => $jwt]);
            } else {
                return response() -> json(['status' => 'failure', 'message' => 'Invalid credentials']);
            }
            
        } catch (\Exception $e) {
            return response() -> json(['status' => 'error', 'message' => $e -> getMessage()]);
        }

    }
    
    private function register(String $username, String $email, String $createdBy) {
        try {
            $nowSeconds = time();
            $payload = array(
                'iss' => $username,
                'sub' => $email,
                'createdBy' => $createdBy,
                'iat' => $nowSeconds,
                'exp' => $nowSeconds + (60*5),
            );
        
            $newjwt = $this -> genjwt($payload);
            $url = "http://localhost:8000/verifyEmail/?token=". $newjwt;
            Mail::to($email) -> send(new EmailVerification($url));
            
        } catch (Exception $e) {
            return response() -> json(['status' => 'failure', 'message' => $e -> getMessage()]);
        }

        
    }

    public function registerSelf(Request $request) {
        $this->validate($request, [
            'username' => 'required|string',
            'email' => 'required|email|unique:users',
        ]);

        $username = $request -> username;
        $email = $request -> email;
        $createdBy = $request -> email;

        return $this -> register($username, $email, $createdBy);
    }

    public function genjwt(Array $payload) {
        
        if (!empty($payload)) {
            try {
                $jwt = JWT::encode($payload, $this->priv_key, 'RS256');
                
                return $jwt;
                
            } catch (\Exception $e) {
                return response() -> json(['status' => 'error', 'message' => $e -> getMessage()]);
            }
        }
        
    }
    
    public function decodejwt(String $jwt) {
        if (!empty($jwt)) {
            try {
                $decoded = JWT::decode($jwt, $this -> pub_key, array('RS256'));
                
                $decoded_array = (array) $decoded;
                
                return $decoded_array;
                
            } catch (\Exception $e) {
                return $e -> getMessage();
            }
        }
    }

    public function checkPassword($pwd) {
    
        if (strlen($pwd) < 8) {
            return 'Password must be atleast 8 characters long!';
        } else if (!preg_match("#[0-9]+#", $pwd)) {
            return 'Password must include at least one digit!';
        } else if (!preg_match("#[a-z]+#", $pwd)) {
            return 'Password must include at least one lowercase letter!';
        } else if (!preg_match("#[A-Z]+#", $pwd)) {
            return 'Password must include at least one uppercase letter!';
        } else if (!preg_match("#[!@\#$%^&*]+#", $pwd)) {
            return 'Password must include at least one special character!';
        }

        return 'Success';
    }
}
