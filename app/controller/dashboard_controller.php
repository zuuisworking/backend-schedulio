<?php
namespace App;

use PDO;

class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getNearestSchedules($userId) {
        // 1. Fetch upcoming tasks
        $taskQuery = "SELECT id, name, deadline as datetime, 'task' as type 
                      FROM tasks 
                      WHERE user_id = :user_id 
                      AND status != 'completed' 
                      AND deadline >= NOW() 
                      ORDER BY deadline ASC";
        $taskStmt = $this->db->prepare($taskQuery);
        $taskStmt->execute([':user_id' => $userId]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch upcoming activities
        // Combine activity_date and activity_time for sorting
        $activityQuery = "SELECT id, name, CONCAT(activity_date, ' ', activity_time) as datetime, 'activity' as type 
                          FROM activities 
                          WHERE user_id = :user_id 
                          AND (activity_date > CURRENT_DATE OR (activity_date = CURRENT_DATE AND activity_time >= CURTIME())) 
                          ORDER BY activity_date ASC, activity_time ASC";
        $activityStmt = $this->db->prepare($activityQuery);
        $activityStmt->execute([':user_id' => $userId]);
        $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Merge and sort
        $combined = array_merge($tasks, $activities);
        
        usort($combined, function($a, $b) {
            return strtotime($a['datetime']) <=> strtotime($b['datetime']);
        });

        // 4. Take top 5
        $nearest = array_slice($combined, 0, 5);

        return ['status' => 200, 'data' => $nearest];
    }
}
