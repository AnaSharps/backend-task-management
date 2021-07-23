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
    protected $recaptcha;

    public function __construct()
    {
        $this->passPattern = env("PASSWORD_FORMAT");
        $this->emailPattern = env("EMAIL_FORMAT");
        $this->recaptcha = new \ReCaptcha\ReCaptcha(env('RECAPTCHA_SECRET_KEY'));
    }

    public function getUsers(Request $request)
    {
        $this->validate($request, [
            'search' => 'string|max: 255',
            'display' => 'required|int',
            'ofset' => 'required|int',
            'deleted' => 'required|boolean'
        ]);

        $token = $request->cookie('token');

        $payload = (new GenerateJWT)->decodejwt($token);
        $user = User::where('email', $payload['sub'])->first();
        $admin = $user->role == "ADMIN";

        if (!empty($request->search)) {
            $term = "%" . $request->search . "%";
            $sql = "SELECT id, name, email, createdBy, deletedBy, isDeleted, created_at, updated_at FROM users WHERE isDeleted = :deleted AND (email like :term OR 'name' like :term2 OR createdBy like :term3 OR deletedBy like :term4) LIMIT :display OFFSET :ofset";
            $users = DB::select($sql, [
                'deleted' => $request->deleted,
                'term' => $term,
                'term2' => $term,
                'term3' => $term,
                'term4' => $term,
                'display' => $request->display,
                'ofset' => $request->ofset,

            ]);
            return response()->json(['users' => $users, 'admin' => $admin]);
        } else {
            $sql = "SELECT id, name, email, createdBy, deletedBy, isDeleted, created_at, updated_at FROM users WHERE isDeleted = :deleted LIMIT :display OFFSET :ofset";
            $users = DB::select($sql, [
                'deleted' => $request->deleted,
                'display' => $request->display,
                'ofset' => $request->ofset,
            ]);
            return response()->json(['users' => $users, 'admin' => $admin]);
        }
    }
}
