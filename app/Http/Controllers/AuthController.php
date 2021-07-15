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

        if (!empty($request->search)) {
            $term = "%" . $request->search . "%";
            $sql = "SELECT * FROM users WHERE is_deleted = :deleted AND (Email like :term OR 'Name' like :term2 OR Created_by like :term3 OR Deleted_by like :term4) LIMIT :display OFFSET :ofset";
            $users = DB::select($sql, [
                'deleted' => $request->deleted,
                'term' => $term,
                'term2' => $term,
                'term3' => $term,
                'term4' => $term,
                'display' => $request->display,
                'ofset' => $request->ofset,

            ]);
            return $users;
        } else {
            $sql = 'SELECT * FROM users WHERE is_deleted = :deleted LIMIT :display OFFSET :ofset';
            $users = DB::select($sql, [
                'deleted' => $request->deleted,
                'display' => $request->display,
                'ofset' => $request->ofset,
            ]);
            return $users;
        }
    }
}
