<?php

require_once __DIR__ . '/../config/database.php';

class NextAction {
    private ?int $id = null;
    private int $teamId;
    private int $cycleId;
    private int $userId;
    private string $description;
    private string $targetDate;
    private string $status = 'pending';
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    
    // For joined data
    private ?string $username = null;
    
    public function __construct(
        int $teamId,
        int $cycleId,
        int $userId,
        string $description,
        string $targetDate,
        string $status = 'pending',
        ?int $id = null
    ) {
        $this->teamId = $teamId;
        $this->cycleId = $cycleId;
        $this->userId = $userId;
        $this->description = $description;
        $this->targetDate = $targetDate;
        $this->status = $status;
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getTeamId(): int {
        return $this->teamId;
    }
    
    public function getCycleId(): int {
        return $this->cycleId;
    }
    
    public function getUserId(): int {
        return $this->userId;
    }
    
    public function getDescription(): string {
        return $this->description;
    }
    
    public function getTargetDate(): string {
        return $this->targetDate;
    }
    
    public function getStatus(): string {
        return $this->status;
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
    
    public function setStatus(string $status): void {
        $this->status = $status;
    }
    
    /**
     * Validate next action data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array {
        $errors = [];
        
        // Description validation
        if (empty(trim($this->description))) {
            $errors['description'] = 'アクションの説明を入力してください。';
        }
        
        // Target date validation
        if (empty($this->targetDate)) {
            $errors['target_date'] = '目標日を入力してください。';
        }
        
        // Status validation
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($this->status, $validStatuses)) {
            $errors['status'] = '無効なステータスです。';
        }
        
        return $errors;
    }
    
    /**
     * Save next action to database
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool {
        try {
            $db = Database::getConnection();
            
            if ($this->id === null) {
                // Insert new next action
                $stmt = $db->prepare(
                    "INSERT INTO next_actions 
                     (team_id, cycle_id, user_id, description, target_date, status) 
                     VALUES (:team_id, :cycle_id, :user_id, :description, :target_date, :status)"
                );
                
                $stmt->execute([
                    ':team_id' => $this->teamId,
                    ':cycle_id' => $this->cycleId,
                    ':user_id' => $this->userId,
                    ':description' => $this->description,
                    ':target_date' => $this->targetDate,
                    ':status' => $this->status
                ]);
                
                $this->id = (int)$db->lastInsertId();
                return true;
            } else {
                // Update existing next action
                $stmt = $db->prepare(
                    "UPDATE next_actions 
                     SET description = :description, target_date = :target_date, status = :status 
                     WHERE id = :id"
                );
                
                return $stmt->execute([
                    ':description' => $this->description,
                    ':target_date' => $this->targetDate,
                    ':status' => $this->status,
                    ':id' => $this->id
                ]);
            }
        } catch (PDOException $e) {
            error_log("NextAction save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find next actions by team
     * 
     * @param int $teamId Team ID
     * @return array Array of NextAction objects
     */
    public static function findByTeam(int $teamId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT na.*, u.username 
                 FROM next_actions na 
                 JOIN users u ON na.user_id = u.id 
                 WHERE na.team_id = :team_id 
                 ORDER BY na.target_date ASC"
            );
            $stmt->execute([':team_id' => $teamId]);
            
            return self::hydrateNextActions($stmt);
        } catch (PDOException $e) {
            error_log("NextAction findByTeam failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find next actions by cycle
     * 
     * @param int $cycleId Cycle ID
     * @return array Array of NextAction objects
     */
    public static function findByCycle(int $cycleId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT na.*, u.username 
                 FROM next_actions na 
                 JOIN users u ON na.user_id = u.id 
                 WHERE na.cycle_id = :cycle_id 
                 ORDER BY na.target_date ASC"
            );
            $stmt->execute([':cycle_id' => $cycleId]);
            
            return self::hydrateNextActions($stmt);
        } catch (PDOException $e) {
            error_log("NextAction findByCycle failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update status of next action
     * 
     * @param int $id Action ID
     * @param string $status New status
     * @return bool True on success
     */
    public static function updateStatus(int $id, string $status): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "UPDATE next_actions SET status = :status WHERE id = :id"
            );
            
            return $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("NextAction updateStatus failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hydrate next action objects from database result
     * 
     * @param PDOStatement $stmt Executed statement
     * @return array Array of NextAction objects
     */
    private static function hydrateNextActions($stmt): array {
        $actions = [];
        
        while ($data = $stmt->fetch()) {
            $action = new NextAction(
                (int)$data['team_id'],
                (int)$data['cycle_id'],
                (int)$data['user_id'],
                $data['description'],
                $data['target_date'],
                $data['status'],
                (int)$data['id']
            );
            $action->createdAt = $data['created_at'];
            $action->updatedAt = $data['updated_at'];
            
            if (isset($data['username'])) {
                $action->username = $data['username'];
            }
            
            $actions[] = $action;
        }
        
        return $actions;
    }
}
