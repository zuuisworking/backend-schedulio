<?php
namespace App;

use PDO;

class ActivityController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getActivities($userId) {
        $query = "SELECT * FROM activities WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['status' => 200, 'data' => $activities];
    }

    public function createActivity($userId, $data) {
        if (empty($data['name']) || empty($data['activity_date']) || empty($data['activity_time'])) {
            return ['status' => 400, 'message' => 'Data aktivitas tidak lengkap.'];
        }

        $query = "INSERT INTO activities (user_id, name, description, activity_date, activity_time) 
                  VALUES (:user_id, :name, :description, :activity_date, :activity_time)";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':activity_date' => $data['activity_date'],
                ':activity_time' => $data['activity_time']
            ]);
            return ['status' => 201, 'message' => 'Aktivitas berhasil ditambahkan.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menyimpan aktivitas: ' . $e->getMessage()];
        }
    }

    public function updateActivity($userId, $activityId, $data) {
        $checkQuery = "SELECT id FROM activities WHERE id = :id AND user_id = :user_id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([':id' => $activityId, ':user_id' => $userId]);
        
        if ($checkStmt->rowCount() === 0) {
            return ['status' => 404, 'message' => 'Aktivitas tidak ditemukan atau akses ditolak.'];
        }

        $fields = [];
        $params = [':id' => $activityId, ':user_id' => $userId];
        $allowedFields = ['name', 'description', 'activity_date', 'activity_time'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['status' => 400, 'message' => 'Tidak ada data yang diubah.'];
        }

        $query = "UPDATE activities SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute($params);
            return ['status' => 200, 'message' => 'Aktivitas berhasil diperbarui.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal memperbarui aktivitas: ' . $e->getMessage()];
        }
    }

    public function deleteActivity($userId, $activityId) {
        $query = "DELETE FROM activities WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([':id' => $activityId, ':user_id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                return ['status' => 200, 'message' => 'Aktivitas berhasil dihapus.'];
            } else {
                return ['status' => 404, 'message' => 'Aktivitas tidak ditemukan atau sudah dihapus.'];
            }
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menghapus aktivitas: ' . $e->getMessage()];
        }
    }
}