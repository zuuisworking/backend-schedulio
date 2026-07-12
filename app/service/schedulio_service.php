<?php

namespace App;

use PDO;

class SchedulioService {
    private $db;
    private $weights = [4, 4, 2];
    private $isBenefit = [true, true, false]; 

    public function __construct($db) {
        $this->db = $db;
    }

    public function calculateTaskPriority($userId) {
        $query = "SELECT id, name, importance_level, difficulty_level, deadline 
                  FROM tasks 
                  WHERE user_id = :user_id AND status != 'completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($tasks) < 2) {
            return ['status' => 400, 'message' => 'Minimal butuh 2 tugas untuk melakukan perangkingan.'];
        }

        $matrix = [];
        foreach ($tasks as $task) {
            $deadlineTime = strtotime($task['deadline']);
            $now = time();
            $hoursRemaining = max(0, ($deadlineTime - $now) / 3600);
            $urgency = 100 / ($hoursRemaining + 1);

            $matrix[] = [
                'id' => $task['id'],
                'c1' => (float) $task['importance_level'],
                'c2' => $urgency,
                'c3' => (float) $task['difficulty_level']
            ];
        }

        $pembagi = [0, 0, 0];
        foreach ($matrix as $row) {
            $pembagi[0] += pow($row['c1'], 2);
            $pembagi[1] += pow($row['c2'], 2);
            $pembagi[2] += pow($row['c3'], 2);
        }
        $pembagi = [sqrt($pembagi[0]), sqrt($pembagi[1]), sqrt($pembagi[2])];
        $normalizedMatrix = [];
        $idealPositif = [-9999, -9999, 9999];
        $idealNegatif = [9999, 9999, -9999];
        
        foreach ($matrix as $row) {
            $v1 = ($pembagi[0] == 0) ? 0 : ($row['c1'] / $pembagi[0]) * $this->weights[0];
            $v2 = ($pembagi[1] == 0) ? 0 : ($row['c2'] / $pembagi[1]) * $this->weights[1];
            $v3 = ($pembagi[2] == 0) ? 0 : ($row['c3'] / $pembagi[2]) * $this->weights[2];

            $normalizedMatrix[] = [
                'id' => $row['id'],
                'v' => [$v1, $v2, $v3]
            ];

            for ($i = 0; $i < 3; $i++) {
                if ($this->isBenefit[$i]) {
                    $idealPositif[$i] = max($idealPositif[$i], $normalizedMatrix[count($normalizedMatrix)-1]['v'][$i]);
                    $idealNegatif[$i] = min($idealNegatif[$i], $normalizedMatrix[count($normalizedMatrix)-1]['v'][$i]);
                } else {
                    $idealPositif[$i] = min($idealPositif[$i], $normalizedMatrix[count($normalizedMatrix)-1]['v'][$i]);
                    $idealNegatif[$i] = max($idealNegatif[$i], $normalizedMatrix[count($normalizedMatrix)-1]['v'][$i]);
                }
            }
        }

        $results = [];
        foreach ($normalizedMatrix as $row) {
            $dPlus = 0;
            $dMinus = 0;

            for ($i = 0; $i < 3; $i++) {
                $dPlus += pow($row['v'][$i] - $idealPositif[$i], 2);
                $dMinus += pow($row['v'][$i] - $idealNegatif[$i], 2);
            }

            $dPlus = sqrt($dPlus);
            $dMinus = sqrt($dMinus);
            $score = ($dPlus + $dMinus == 0) ? 0 : $dMinus / ($dPlus + $dMinus);
            $results[] = [
                'target_id' => $row['id'],
                'score' => $score
            ];
        }

        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $this->saveResultsToDb($userId, 'task', $results);

        return ['status' => 200, 'message' => 'Perhitungan TOPSIS berhasil', 'data' => $results];
    }

    private function saveResultsToDb($userId, $targetType, $results) {
        $deleteQuery = "DELETE FROM schedulio_results WHERE user_id = :user_id AND target_type = :target_type";
        $delStmt = $this->db->prepare($deleteQuery);
        $delStmt->execute([':user_id' => $userId, ':target_type' => $targetType]);
        $insertQuery = "INSERT INTO schedulio_results (user_id, target_type, target_id, score, ranking) 
                        VALUES (:user_id, :target_type, :target_id, :score, :ranking)";
        $insStmt = $this->db->prepare($insertQuery);

        $rank = 1;
        foreach ($results as $res) {
            $insStmt->execute([
                ':user_id' => $userId,
                ':target_type' => $targetType,
                ':target_id' => $res['target_id'],
                ':score' => $res['score'],
                ':ranking' => $rank
            ]);
            $rank++;
        }
    }
}