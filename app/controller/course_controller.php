<?php
namespace App;

use PDO;

class CourseController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getCourses($userId) {
        $query = "SELECT * FROM class_schedules 
                  WHERE user_id = :user_id 
                  ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['status' => 200, 'data' => $courses];
    }

    public function createCourse($userId, $data) {
        if (empty($data['course_name']) || empty($data['day_of_week']) || empty($data['start_time']) || empty($data['end_time'])) {
            return ['status' => 400, 'message' => 'Data jadwal kuliah tidak lengkap.'];
        }

        $allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        if (!in_array($data['day_of_week'], $allowedDays)) {
            return ['status' => 400, 'message' => 'Hari yang dipilih tidak valid.'];
        }

        $query = "INSERT INTO class_schedules (user_id, course_name, day_of_week, start_time, end_time) 
                  VALUES (:user_id, :course_name, :day_of_week, :start_time, :end_time)";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([
                ':user_id' => $userId,
                ':course_name' => $data['course_name'],
                ':day_of_week' => $data['day_of_week'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time']
            ]);
            return ['status' => 201, 'message' => 'Jadwal kuliah berhasil ditambahkan.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menyimpan jadwal kuliah: ' . $e->getMessage()];
        }
    }

    public function updateCourse($userId, $courseId, $data) {
        $checkQuery = "SELECT id FROM class_schedules WHERE id = :id AND user_id = :user_id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([':id' => $courseId, ':user_id' => $userId]);
        
        if ($checkStmt->rowCount() === 0) {
            return ['status' => 404, 'message' => 'Jadwal kuliah tidak ditemukan atau akses ditolak.'];
        }

        $fields = [];
        $params = [':id' => $courseId, ':user_id' => $userId];
        $allowedFields = ['course_name', 'day_of_week', 'start_time', 'end_time'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'day_of_week') {
                    $allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    if (!in_array($data[$field], $allowedDays)) {
                        return ['status' => 400, 'message' => 'Hari yang dipilih tidak valid.'];
                    }
                }
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['status' => 400, 'message' => 'Tidak ada data yang diubah.'];
        }

        $query = "UPDATE class_schedules SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute($params);
            return ['status' => 200, 'message' => 'Jadwal kuliah berhasil diperbarui.'];
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal memperbarui jadwal kuliah: ' . $e->getMessage()];
        }
    }

    public function deleteCourse($userId, $courseId) {
        $query = "DELETE FROM class_schedules WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        
        try {
            $stmt->execute([':id' => $courseId, ':user_id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                return ['status' => 200, 'message' => 'Jadwal kuliah berhasil dihapus.'];
            } else {
                return ['status' => 404, 'message' => 'Jadwal kuliah tidak ditemukan atau sudah dihapus.'];
            }
        } catch (\PDOException $e) {
            return ['status' => 500, 'message' => 'Gagal menghapus jadwal kuliah: ' . $e->getMessage()];
        }
    }
}
