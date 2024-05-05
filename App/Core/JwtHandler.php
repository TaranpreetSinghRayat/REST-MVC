<?php

namespace App\Core;

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    private $secretKey;
    private $issuer;
    private $audience;

    public function __construct($secretKey, $issuer, $audience)
    {
        $this->secretKey = $secretKey;
        $this->issuer = $issuer;
        $this->audience = $audience;
    }

    public function generateToken($data = array())
    {
        $token = array(
            "iss" => $this->issuer,
            "aud" => $this->audience,
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + (60 * 60), // Token expires after 1 hour
            "data" => $data
        );

        return JWT::encode($token, $this->secretKey, 'HS256');
    }

    public function validateToken($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid token');
        }
    }

    public function refreshToken($jwt)
    {
        $decoded = $this->validateToken($jwt);
        if ($decoded) {
            return $this->generateToken($decoded->data->userId);
        }
        return null;
    }
}
