<?php

require_once __DIR__ . '/../config/database.php';

class PDCACycle {
    private ?int $id = null;
    private int $teamId;
    private int $cycleNumber;
    private ?string $startDate = null;
    private ?string $endDate = null;
    private string $status = 'active';
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    
    public function __construct(
        int $teamId,
        int $cycleNumber,
        string $status = 'active',
        ?int $id = null
    ) {
        $this->teamId = $teamId;
        $this->cycleNumber = $cycleNumber;
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
    
    public function getCycleNumber(): int {
        return $this->cycleNumber;
    }
    
    public function getStartDate(): ?string {
        return $this->startDate;
    }
    
    public function getEndDate(): ?string {
        return $this->endDate;
    }
    
    public function getStatus(): string {
        return $this->status;
    }
    
    public function setEndDate(string $endDate): void {
        $this->endDate = $endDate;
    }
    
    public function setStatus(string $status): void {
        $this->status = $status;
    }
    
    /**
     * Save cycle to database
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool {
        try {
            $db = Database::getConnection();
            
            if ($this->id === null) {
                // Insert new cycle
                $stmt = $db->prepare(
                    "INSERT INTO pdca_cycles (team_id, cycle_number, status) 
                     VALUES (:team_id, :cycle_number, :status)"
                );
                
                $stmt->execute([
                    ':team_id' => $this->teamId,
                    ':cycle_number' => $this->cycleNumber,
                    ':status' => $this->status
                ]);
                
                $this->id = (int)$db->lastInsertId();
                return true;
            } else {
                // Update existing cycle
                $stmt = $db->prepare(
                    "UPDATE pdca_cycles 
                     SET status = :status, end_date = :end_date 
                     WHERE id = :id"
                );
                
                return $stmt->execute([
                    ':status' => $this->status,
                    ':end_date' => $this->endDate,
                    ':id' => $this->id
                ]);
            }
        } catch (PDOException $e) {
            error_log("PDCACycle save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find cycle by ID
     * 
     * @param int $id Cycle ID
     * @return PDCACycle|null Cycle object or null if not found
     */
    public static function findById(int $id): ?PDCACycle {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM pdca_cycles WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $cycle = new PDCACycle(
                (int)$data['team_id'],
                (int)$data['cycle_number'],
                $data['status'],
                (int)$data['id']
            );
            $cycle->startDate = $data['start_date'];
            $cycle->endDate = $data['end_date'];
            $cycle->createdAt = $data['created_at'];
            $cycle->updatedAt = $data['updated_at'];
            
            return $cycle;
        } catch (PDOException $e) {
            error_log("PDCACycle findById failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current active cycle for team
     * 
     * @param int $teamId Team ID
     * @return PDCACycle|null Active cycle or null if not found
     */
    public static function getCurrentCycle(int $teamId): ?PDCACycle {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM pdca_cycles 
                 WHERE team_id = :team_id AND status = 'active' 
                 ORDER BY cycle_number DESC LIMIT 1"
            );
            $stmt->execute([':team_id' => $teamId]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $cycle = new PDCACycle(
                (int)$data['team_id'],
                (int)$data['cycle_number'],
                $data['status'],
                (int)$data['id']
            );
            $cycle->startDate = $data['start_date'];
            $cycle->endDate = $data['end_date'];
            $cycle->createdAt = $data['created_at'];
            $cycle->updatedAt = $data['updated_at'];
            
            return $cycle;
        } catch (PDOException $e) {
            error_log("PDCACycle getCurrentCycle failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Complete current cycle and create new one
     * 
     * @return bool True on success, false on failure
     */
    public function completeCycle(): bool {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            // Mark current cycle as completed
            $this->status = 'completed';
            $this->endDate = date('Y-m-d H:i:s');
            
            $stmt = $db->prepare(
                "UPDATE pdca_cycles 
                 SET status = 'completed', end_date = :end_date 
                 WHERE id = :id"
            );
            $stmt->execute([
                ':end_date' => $this->endDate,
                ':id' => $this->id
            ]);
            
            // Create new cycle
            $newCycleNumber = $this->cycleNumber + 1;
            $stmt = $db->prepare(
                "INSERT INTO pdca_cycles (team_id, cycle_number, status) 
                 VALUES (:team_id, :cycle_number, 'active')"
            );
            $stmt->execute([
                ':team_id' => $this->teamId,
                ':cycle_number' => $newCycleNumber
            ]);
            
            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("PDCACycle completeCycle failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all cycles for team
     * 
     * @param int $teamId Team ID
     * @return array Array of PDCACycle objects
     */
    public static function findByTeam(int $teamId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM pdca_cycles 
                 WHERE team_id = :team_id 
                 ORDER BY cycle_number DESC"
            );
            $stmt->execute([':team_id' => $teamId]);
            
            $cycles = [];
            while ($data = $stmt->fetch()) {
                $cycle = new PDCACycle(
                    (int)$data['team_id'],
                    (int)$data['cycle_number'],
                    $data['status'],
                    (int)$data['id']
                );
                $cycle->startDate = $data['start_date'];
                $cycle->endDate = $data['end_date'];
                $cycle->createdAt = $data['created_at'];
                $cycle->updatedAt = $data['updated_at'];
                
                $cycles[] = $cycle;
            }
            
            return $cycles;
        } catch (PDOException $e) {
            error_log("PDCACycle findByTeam failed: " . $e->getMessage());
            return [];
        }
    }
}
