<?php

// require_once('vendor/autoload.php');

namespace App\Http\Controllers;

use App\Events\NotificationsEvent;
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
    protected $taskStatusPattern;
    protected $recaptcha;
    // protected $token;
    // protected $user;

    public function __construct(Request $request)
    {
        $this->passPattern = env("PASSWORD_FORMAT");
        $this->emailPattern = env("EMAIL_FORMAT");
        $this->taskStatusPattern = env("TASK_STATUS_FORMAT");
        $this->recaptcha = new \ReCaptcha\ReCaptcha(env('RECAPTCHA_SECRET_KEY'));

    }
    
    public function redirect(Request $request) {
        $token = $request->cookie("token");
        $payload = (new GenerateJWT)->decodejwt($token);
    
        if (gettype($payload) !== "array") {
            event(new NotificationsEvent("Token Expied", 403));
            return response("Expired token", 403);
        }
        $user = User::where('email', $payload['sub'])->where('isDeleted', false)->first();
        return response()->json(['status' => 'success', 'user' => $user]);
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
            $sql2 = "SELECT id, name, email, createdBy, deletedBy, isDeleted, created_at, updated_at FROM users WHERE isDeleted = :deleted AND (email like :term OR 'name' like :term2 OR createdBy like :term3 OR deletedBy like :term4)";
            $count = DB::select($sql2, [
                'deleted' => $request->deleted,
                'term' => $term,
                'term2' => $term,
                'term3' => $term,
                'term4' => $term,
            ]);
            $count = count($count);
            return response()->json(['users' => $users, 'admin' => $admin, 'totalCount' => $count]);
        } else {
            $sql = "SELECT id, name, email, createdBy, deletedBy, isDeleted, created_at, updated_at FROM users WHERE isDeleted = :deleted LIMIT :display OFFSET :ofset";
            $users = DB::select($sql, [
                'deleted' => $request->deleted,
                'display' => $request->display,
                'ofset' => $request->ofset,
            ]);
            $count = User::where('isDeleted', false)->get();
            $count = count($count);
            return response()->json(['users' => $users, 'admin' => $admin, 'totalCount' => $count]);
        }
    }
}
