<?php
namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    public static function generateToken($userId, $email) {
        $secretKey = $_ENV['JWT_SECRET'];
        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60 * 24);
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'id' => $userId,
                'email' => $email
            ]
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function validateToken($token) {
        try {
            $secretKey = $_ENV['JWT_SECRET'];
            return JWT::decode($token, new Key($secretKey, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}