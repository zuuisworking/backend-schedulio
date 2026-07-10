<?php

namespace App;

class AuthMiddleware {
    
    public static function checkToken() {
        $headers = apache_request_headers();
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'message' => 'Akses ditolak. Token tidak ditemukan.']);
            exit();
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = JwtHelper::validateToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'message' => 'Sesi berakhir atau token tidak valid. Silakan login kembali.']);
            exit();
        }

        return $decoded->data;
    }
}