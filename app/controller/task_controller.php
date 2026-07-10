<?php
namespace App;

use PDO;

class TaskController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTasks($userId) {
        $query = "SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['status' => 200, 'data' => $tasks];
    }

    public function createTask($userId, $data) {
        if (empty($data['name']) || empty($data['difficulty_level']) || empty($data['importance_level']) || empty($data['deadline'])) {
            return ['status' => 400, 'message' => 'Data tugas tidak lengkap.'];
        }

        $query = "INSERT INTO tasks (user_id, name, description, difficulty_level, importance_level, deadline) 
                  VALUES (:user_id, :name, :description, :difficulty_level, :importance_level, :deadline)";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':difficulty_level' => $data['difficulty_level'],
                ':importance_level' => $data['importance_level'],
                ':deadline' => $data['deadline']
            ]);
            return ['status' => 201, 'message' => 'Tugas berhasil ditambahkan.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menyimpan tugas: ' . $e->getMessage()];
        }
    }

    public function updateTask($userId, $taskId, $data) {
        $checkQuery = "SELECT id FROM tasks WHERE id = :id AND user_id = :user_id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([':id' => $taskId, ':user_id' => $userId]);
        
        if ($checkStmt->rowCount() === 0) {
            return ['status' => 404, 'message' => 'Tugas tidak ditemukan atau akses ditolak.'];
        }

        $fields = [];
        $params = [':id' => $taskId, ':user_id' => $userId];
        $allowedFields = ['name', 'description', 'difficulty_level', 'importance_level', 'deadline', 'status', 'progress'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['status' => 400, 'message' => 'Tidak ada data yang diubah.'];
        }

        $query = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute($params);
            return ['status' => 200, 'message' => 'Tugas berhasil diperbarui.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal memperbarui tugas: ' . $e->getMessage()];
        }
    }

    public function deleteTask($userId, $taskId) {
        $query = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([':id' => $taskId, ':user_id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                return ['status' => 200, 'message' => 'Tugas berhasil dihapus.'];
            } else {
                return ['status' => 404, 'message' => 'Tugas tidak ditemukan atau sudah dihapus.'];
            }
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menghapus tugas: ' . $e->getMessage()];
        }
    }
}