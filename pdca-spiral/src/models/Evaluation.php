<?php

require_once __DIR__ . '/../config/database.php';

class Evaluation {
    private ?int $id = null;
    private int $userId;
    private int $teamId;
    private int $cycleId;
    private int $score;
    private string $reflection;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    
    // For joined data
    private ?string $username = null;
    
    public function __construct(
        int $userId,
        int $teamId,
        int $cycleId,
        int $score,
        string $reflection,
        ?int $id = null
    ) {
        $this->userId = $userId;
        $this->teamId = $teamId;
        $this->cycleId = $cycleId;
        $this->score = $score;
        $this->reflection = $reflection;
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getUserId(): int {
        return $this->userId;
    }
    
    public function getTeamId(): int {
        return $this->teamId;
    }
    
    public function getCycleId(): int {
        return $this->cycleId;
    }
    
    public function getScore(): int {
        return $this->score;
    }
    
    public function getReflection(): string {
        return $this->reflection;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    
    public function getUsername(): ?string {
        return $this->username;
    }
    
    public function setUsername(string $username): void {
        $this->username = $username;
    }
    
    /**
     * Validate evaluation data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array {
        $errors = [];
        
        // Score validation
        if ($this->score < 0 || $this->score > 10) {
            $errors['score'] = 'スコアは0から10の範囲で入力してください。';
        }
        
        // Reflection validation
        if (empty(trim($this->reflection))) {
            $errors['reflection'] = '振り返りを入力してください。';
        }
        
        return $errors;
    }
    
    /**
     * Save evaluation to database
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool {
        try {
            $db = Database::getConnection();
            
            if ($this->id === null) {
                // Insert new evaluation
                $stmt = $db->prepare(
                    "INSERT INTO evaluations (user_id, team_id, cycle_id, score, reflection) 
                     VALUES (:user_id, :team_id, :cycle_id, :score, :reflection)"
                );
                
                $stmt->execute([
                    ':user_id' => $this->userId,
                    ':team_id' => $this->teamId,
                    ':cycle_id' => $this->cycleId,
                    ':score' => $this->score,
                    ':reflection' => $this->reflection
                ]);
                
                $this->id = (int)$db->lastInsertId();
                return true;
            } else {
                // Update existing evaluation
                $stmt = $db->prepare(
                    "UPDATE evaluations 
                     SET score = :score, reflection = :reflection 
                     WHERE id = :id"
                );
                
                return $stmt->execute([
                    ':score' => $this->score,
                    ':reflection' => $this->reflection,
                    ':id' => $this->id
                ]);
            }
        } catch (PDOException $e) {
            error_log("Evaluation save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find evaluations by team
     * 
     * @param int $teamId Team ID
     * @return array Array of Evaluation objects
     */
    public static function findByTeam(int $teamId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT e.*, u.username 
                 FROM evaluations e 
                 JOIN users u ON e.user_id = u.id 
                 WHERE e.team_id = :team_id 
                 ORDER BY e.created_at ASC"
            );
            $stmt->execute([':team_id' => $teamId]);
            
            return self::hydrateEvaluations($stmt);
        } catch (PDOException $e) {
            error_log("Evaluation findByTeam failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find evaluations by cycle
     * 
     * @param int $cycleId Cycle ID
     * @return array Array of Evaluation objects
     */
    public static function findByCycle(int $cycleId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT e.*, u.username 
                 FROM evaluations e 
                 JOIN users u ON e.user_id = u.id 
                 WHERE e.cycle_id = :cycle_id 
                 ORDER BY e.created_at ASC"
            );
            $stmt->execute([':cycle_id' => $cycleId]);
            
            return self::hydrateEvaluations($stmt);
        } catch (PDOException $e) {
            error_log("Evaluation findByCycle failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get average score for cycle
     * 
     * @param int $cycleId Cycle ID
     * @return float Average score
     */
    public static function getAverageScore(int $cycleId): float {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT AVG(score) as avg_score 
                 FROM evaluations 
                 WHERE cycle_id = :cycle_id"
            );
            $stmt->execute([':cycle_id' => $cycleId]);
            
            $result = $stmt->fetch();
            return $result['avg_score'] ? (float)$result['avg_score'] : 0.0;
        } catch (PDOException $e) {
            error_log("Evaluation getAverageScore failed: " . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Find evaluations by team grouped by week with averages
     * 
     * Groups evaluations by week (Monday-Sunday) and calculates weekly average scores.
     * 
     * @param int $teamId Team ID
     * @return array Array of weekly data with structure:
     *               [
     *                   'week_start' => '2025-12-02',  // Monday of the week
     *                   'week_end' => '2025-12-08',    // Sunday of the week
     *                   'average_score' => 7.5,
     *                   'evaluation_count' => 4
     *               ]
     */
    public static function findByTeamGroupedByWeek(int $teamId): array {
        try {
            $db = Database::getConnection();
            
            // Group by cycle_number instead of actual date
            $stmt = $db->prepare(
                "SELECT 
                    c.cycle_number,
                    c.start_date as week_start,
                    c.end_date as week_end,
                    AVG(e.score) as average_score,
                    COUNT(e.id) as evaluation_count
                 FROM pdca_cycles c
                 LEFT JOIN evaluations e ON c.id = e.cycle_id
                 WHERE c.team_id = :team_id
                 GROUP BY c.cycle_number, c.start_date, c.end_date
                 ORDER BY c.cycle_number ASC"
            );
            
            $stmt->execute([':team_id' => $teamId]);
            
            $weeklyData = [];
            while ($row = $stmt->fetch()) {
                // Only include cycles that have evaluations
                if ($row['evaluation_count'] > 0) {
                    // Calculate week_end as 6 days after week_start if not set
                    $weekStart = $row['week_start'];
                    $weekEnd = $row['week_end'];
                    
                    if (empty($weekEnd) || $weekEnd === $weekStart) {
                        $startDate = new DateTime($weekStart);
                        $endDate = clone $startDate;
                        $endDate->modify('+6 days');
                        $weekEnd = $endDate->format('Y-m-d');
                    }
                    
                    $weeklyData[] = [
                        'cycle_number' => (int)$row['cycle_number'],
                        'week_start' => $weekStart,
                        'week_end' => $weekEnd,
                        'average_score' => (float)$row['average_score'],
                        'evaluation_count' => (int)$row['evaluation_count']
                    ];
                }
            }
            
            return $weeklyData;
        } catch (PDOException $e) {
            error_log("Evaluation findByTeamGroupedByWeek failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Hydrate evaluation objects from database result
     * 
     * @param PDOStatement $stmt Executed statement
     * @return array Array of Evaluation objects
     */
    private static function hydrateEvaluations($stmt): array {
        $evaluations = [];
        
        while ($data = $stmt->fetch()) {
            $evaluation = new Evaluation(
                (int)$data['user_id'],
                (int)$data['team_id'],
                (int)$data['cycle_id'],
                (int)$data['score'],
                $data['reflection'],
                (int)$data['id']
            );
            $evaluation->createdAt = $data['created_at'];
            $evaluation->updatedAt = $data['updated_at'];
            
            if (isset($data['username'])) {
                $evaluation->username = $data['username'];
            }
            
            $evaluations[] = $evaluation;
        }
        
        return $evaluations;
    }
}
