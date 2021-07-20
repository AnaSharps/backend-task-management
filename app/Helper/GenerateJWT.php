<?php

namespace App\Helper;

use Firebase\JWT\JWT;

class GenerateJWT
{
    private $key;

    public function __construct()
    {
        $this->key = env("KEY_JWT");
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
            } //expire exception
        }
    }
}
