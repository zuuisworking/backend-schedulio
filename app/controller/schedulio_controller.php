<?php

namespace App;

class SchedulioController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function calculate($userId) {
        $schedulioService = new SchedulioService($this->db);
        return $schedulioService->calculateTaskPriority($userId);
    }

    public function getRecommendations($userId) {
        $query = "SELECT tr.ranking, tr.score, t.id, t.name, t.deadline, t.importance_level, t.difficulty_level 
                  FROM topsis_results tr
                  JOIN tasks t ON tr.target_id = t.id
                  WHERE tr.user_id = :user_id AND tr.target_type = 'task'
                  ORDER BY tr.ranking ASC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $recommendations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return ['status' => 200, 'data' => $recommendations];
    }
}