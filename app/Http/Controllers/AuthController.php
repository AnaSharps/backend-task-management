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
use Illuminate\Support\Facades\DB;

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

    public function getUsers(Request $request)
    { //pagination
        $this->validate($request, [
            'search' => 'string|max: 255',
        ]);

        if (!empty($request->search)) {
            $term = "%" . $request->search . "%";
            $users = DB::select(DB::raw("SELECT * FROM users WHERE is_deleted = false AND (Email like '$term' OR 'Name' like '$term' OR Created_by like '$term' OR Deleted_by like '$term')"));
            return $users;
        } else {
            $users = DB::select('SELECT * FROM users WHERE is_deleted = false');
            return $users;
        }

        // $users = (array) $users;
    }
}
