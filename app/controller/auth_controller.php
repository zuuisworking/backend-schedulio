<?php

namespace App;

use PDO;

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return ['status' => 400, 'message' => 'Semua field harus diisi.'];
        }

        $hashPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $hashPassword
            ]);
            return ['status' => 201, 'message' => 'Registrasi berhasil.'];
        } catch (\PDOException $e) {
            return ['status' => 400, 'message' => 'Email mungkin sudah terdaftar.'];
        }
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['status' => 400, 'message' => 'Email dan password harus diisi.'];
        }

        $query = "SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data['password'], $user['password'])) {
            $token = JwtHelper::generateToken($user['id'], $user['email']);
            return [
                'status' => 200,
                'message' => 'Login berhasil.',
                'token' => $token,
                'user' => ['id' => $user['id'], 'name' => $user['name']]
            ];
        }

        return ['status' => 401, 'message' => 'Email atau password salah.'];
    }
}