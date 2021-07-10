<?php

// require_once('vendor/autoload.php');

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;


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

    public function registerUser(Request $request) {
        $this->validate($request, [
            'username' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
        ]);

        try {
            $user = new User();
            $user -> username = $request->input('username');
            $user -> email = $request->input('email');
            $user -> role = 'normal';
            $user -> password = app('hash') -> make($request->input('password'));

            if ($user -> save()) {
                return response() -> json(['status' => 'success', 'message' => 'User Created Successfully!', 'user' => $user]);
            }

        } catch (Exception $e) {
            return response() -> json(['status' => 'success', 'message' => 'User Registered']);
        }
    }

    public function genjwt(Request $request) {

        $this -> validate($request, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $payload = array(
            'sub'=> $request -> email,
            'pass' => $request -> password,
            'iat' => time(),
        );
        
        try {
            $jwt = JWT::encode($payload, $this->priv_key, 'RS256');
            print_r($jwt);
            // echo "Encode:\n" . print_r($jwt, true) . "\n";
            
            // echo "Decode:\n" . print_r($decoded_array, true) . "\n";
            
            return response() -> json(['status' => 'success', 'message' => 'done dana done done']);
            
        } catch (\Exception $e) {
            return response() -> json(['status' => 'error', 'message' => $e -> getMessage()]);
        }
        
    }
    
    public function decodejwt(Request $request) {
        $jwt = $request -> header('token');
        
        try {
            $decoded = JWT::decode($jwt, $this -> pub_key, array('RS256'));
            
            /*
            NOTE: This will now be an object instead of an associative array. To get
            an associative array, you will need to cast it as such:
            */
            
            $decoded_array = (array) $decoded;
            print_r($decoded_array);
            
            return User::where('email', $decoded_array['sub'])-> first();
            
        } catch (\Exception $e) {
            return response() -> json(['status' => 'error', 'message' => $e -> getMessage()]);
        }
    }
}
