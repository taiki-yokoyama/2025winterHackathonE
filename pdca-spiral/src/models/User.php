<?php

require_once __DIR__ . '/../config/database.php';

class User {
    private ?int $id = null;
    private string $username;
    private string $email;
    private string $passwordHash;
    private int $teamId;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    
    public function __construct(
        string $username,
        string $email,
        string $passwordHash,
        int $teamId,
        ?int $id = null
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->teamId = $teamId;
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getUsername(): string {
        return $this->username;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function getPasswordHash(): string {
        return $this->passwordHash;
    }
    
    public function getTeamId(): int {
        return $this->teamId;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    
    /**
     * Validate user data
     * 
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array {
        $errors = [];
        
        // Username validation
        if (empty($this->username)) {
            $errors['username'] = 'ユーザー名は必須です。';
        } elseif (strlen($this->username) < 3 || strlen($this->username) > 50) {
            $errors['username'] = 'ユーザー名は3文字以上50文字以内で入力してください。';
        }
        
        // Email validation
        if (empty($this->email)) {
            $errors['email'] = 'メールアドレスは必須です。';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = '有効なメールアドレスを入力してください。';
        }
        
        // Password hash validation
        if (empty($this->passwordHash)) {
            $errors['password'] = 'パスワードは必須です。';
        }
        
        // Team ID validation
        if ($this->teamId <= 0) {
            $errors['team_id'] = '有効なチームIDが必要です。';
        }
        
        return $errors;
    }
    
    /**
     * Save user to database
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool {
        try {
            $db = Database::getConnection();
            
            if ($this->id === null) {
                // Insert new user
                $stmt = $db->prepare(
                    "INSERT INTO users (username, email, password_hash, team_id) 
                     VALUES (:username, :email, :password_hash, :team_id)"
                );
                
                $stmt->execute([
                    ':username' => $this->username,
                    ':email' => $this->email,
                    ':password_hash' => $this->passwordHash,
                    ':team_id' => $this->teamId
                ]);
                
                $this->id = (int)$db->lastInsertId();
                return true;
            } else {
                // Update existing user
                $stmt = $db->prepare(
                    "UPDATE users 
                     SET username = :username, email = :email, 
                         password_hash = :password_hash, team_id = :team_id 
                     WHERE id = :id"
                );
                
                return $stmt->execute([
                    ':username' => $this->username,
                    ':email' => $this->email,
                    ':password_hash' => $this->passwordHash,
                    ':team_id' => $this->teamId,
                    ':id' => $this->id
                ]);
            }
        } catch (PDOException $e) {
            error_log("User save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return User|null User object or null if not found
     */
    public static function findById(int $id): ?User {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $user = new User(
                $data['username'],
                $data['email'],
                $data['password_hash'],
                (int)$data['team_id'],
                (int)$data['id']
            );
            $user->createdAt = $data['created_at'];
            $user->updatedAt = $data['updated_at'];
            
            return $user;
        } catch (PDOException $e) {
            error_log("User findById failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return User|null User object or null if not found
     */
    public static function findByUsername(string $username): ?User {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $user = new User(
                $data['username'],
                $data['email'],
                $data['password_hash'],
                (int)$data['team_id'],
                (int)$data['id']
            );
            $user->createdAt = $data['created_at'];
            $user->updatedAt = $data['updated_at'];
            
            return $user;
        } catch (PDOException $e) {
            error_log("User findByUsername failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return User|null User object or null if not found
     */
    public static function findByEmail(string $email): ?User {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            $data = $stmt->fetch();
            if (!$data) {
                return null;
            }
            
            $user = new User(
                $data['username'],
                $data['email'],
                $data['password_hash'],
                (int)$data['team_id'],
                (int)$data['id']
            );
            $user->createdAt = $data['created_at'];
            $user->updatedAt = $data['updated_at'];
            
            return $user;
        } catch (PDOException $e) {
            error_log("User findByEmail failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Hash password
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
