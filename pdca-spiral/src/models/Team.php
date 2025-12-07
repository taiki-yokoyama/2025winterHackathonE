<?php

require_once __DIR__ . '/../config/database.php';

class Team {
    private ?int $id = null;
    private string $teamName;
    private string $teamCode;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    
    public function __construct(string $teamName, string $teamCode, ?int $id = null) {
        $this->teamName = $teamName;
        $this->teamCode = strtoupper($teamCode);
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getTeamName(): string {
        return $this->teamName;
    }
    
    public function getTeamCode(): string {
        return $this->teamCode;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    
    /**
     * Save team to database
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool {
        try {
            $db = Database::getConnection();
            
            if ($this->id === null) {
                // Insert new team
                $stmt = $db->prepare(
                    "INSERT INTO teams (team_name, team_code) VALUES (:team_name, :team_code)"
                );
                
                $stmt->execute([
                    ':team_name' => $this->teamName,
                    ':team_code' => $this->teamCode
                ]);
                $this->id = (int)$db->lastInsertId();
                
                // Initialize first PDCA cycle for new team
                $this->initializeFirstCycle();
                
                return true;
            } else {
                // Update existing team
                $stmt = $db->prepare(
                    "UPDATE teams SET team_name = :team_name, team_code = :team_code WHERE id = :id"
                );
                
                return $stmt->execute([
                    ':team_name' => $this->teamName,
                    ':team_code' => $this->teamCode,
                    ':id' => $this->id
                ]);
            }
        } catch (PDOException $e) {
            error_log("Team save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Initialize first PDCA cycle for team
     * 
     * @return bool True on success
     */
    private function initializeFirstCycle(): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO pdca_cycles (team_id, cycle_number, status) 
                 VALUES (:team_id, 1, 'active')"
            );
            
            return $stmt->execute([':team_id' => $this->id]);
        } catch (PDOException $e) {
            error_log("Initialize first cycle failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find team by ID
     * 
     * @param int|null $id Team ID
     * @return Team|null Team object or null if not found
     */
    public static function findById(?int $id): ?Team {
        if ($id === null) {
            return null;
        }
        
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM teams WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $team = new Team($data['team_name'], $data['team_code'], (int)$data['id']);
            $team->createdAt = $data['created_at'];
            $team->updatedAt = $data['updated_at'];
            
            return $team;
        } catch (PDOException $e) {
            error_log("Team findById failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find team by team code
     * 
     * @param string $teamCode Team code
     * @return Team|null Team object or null if not found
     */
    public static function findByCode(string $teamCode): ?Team {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM teams WHERE team_code = :team_code");
            $stmt->execute([':team_code' => strtoupper($teamCode)]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $team = new Team($data['team_name'], $data['team_code'], (int)$data['id']);
            $team->createdAt = $data['created_at'];
            $team->updatedAt = $data['updated_at'];
            
            return $team;
        } catch (PDOException $e) {
            error_log("Team findByCode failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all team members
     * 
     * @return array Array of User objects
     */
    public function getMembers(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE team_id = :team_id ORDER BY username");
            $stmt->execute([':team_id' => $this->id]);
            
            $members = [];
            while ($data = $stmt->fetch()) {
                require_once __DIR__ . '/User.php';
                $user = new User(
                    $data['username'],
                    $data['email'],
                    $data['password_hash'],
                    (int)$data['team_id'],
                    (int)$data['id']
                );
                $members[] = $user;
            }
            
            return $members;
        } catch (PDOException $e) {
            error_log("Team getMembers failed: " . $e->getMessage());
            return [];
        }
    }
}
