<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Team.php';

class AuthService {
    /**
     * Authenticate user with username and password
     * 
     * @param string $username Username
     * @param string $password Plain text password
     * @return User|null User object if authenticated, null otherwise
     */
    public static function authenticate(string $username, string $password): ?User {
        $user = User::findByUsername($username);
        
        if ($user === null) {
            return null;
        }
        
        if (User::verifyPassword($password, $user->getPasswordHash())) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Register new user and join/create team
     * 
     * @param string $username Username
     * @param string $email Email address
     * @param string $password Plain text password
     * @param string $teamCode Team code to join or create
     * @param string $teamName Team name (only for new teams)
     * @param bool $createNew Whether to create a new team
     * @return array Result array with 'success' boolean and 'user' or 'errors'
     */
    public static function register(
        string $username,
        string $email,
        string $password,
        string $teamCode,
        string $teamName = '',
        bool $createNew = false
    ): array {
        // Check if username already exists
        if (User::findByUsername($username) !== null) {
            return [
                'success' => false,
                'errors' => ['username' => 'このユーザー名は既に使用されています。']
            ];
        }
        
        // Check if email already exists
        if (User::findByEmail($email) !== null) {
            return [
                'success' => false,
                'errors' => ['email' => 'このメールアドレスは既に使用されています。']
            ];
        }
        
        // Validate team code format
        if (!preg_match('/^[A-Z0-9]{3,20}$/i', $teamCode)) {
            return [
                'success' => false,
                'errors' => ['team_code' => 'チームコードは3〜20文字の英数字で入力してください。']
            ];
        }
        
        try {
            Database::beginTransaction();
            
            // Check if team exists or create new
            $team = Team::findByCode($teamCode);
            
            if ($createNew) {
                // Creating new team
                if ($team !== null) {
                    Database::rollback();
                    return [
                        'success' => false,
                        'errors' => ['team_code' => 'このチームコードは既に使用されています。']
                    ];
                }
                
                if (empty($teamName)) {
                    Database::rollback();
                    return [
                        'success' => false,
                        'errors' => ['team_name' => 'チーム名を入力してください。']
                    ];
                }
                
                $team = new Team($teamName, $teamCode);
                if (!$team->save()) {
                    Database::rollback();
                    return [
                        'success' => false,
                        'errors' => ['general' => 'チームの作成に失敗しました。']
                    ];
                }
            } else {
                // Joining existing team
                if ($team === null) {
                    Database::rollback();
                    return [
                        'success' => false,
                        'errors' => ['team_code' => 'このチームコードは存在しません。']
                    ];
                }
            }
            
            // Create user
            $passwordHash = User::hashPassword($password);
            $user = new User($username, $email, $passwordHash, $team->getId());
            
            // Validate user
            $errors = $user->validate();
            if (!empty($errors)) {
                Database::rollback();
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
            if (!$user->save()) {
                Database::rollback();
                return [
                    'success' => false,
                    'errors' => ['general' => 'ユーザーの作成に失敗しました。']
                ];
            }
            
            Database::commit();
            
            return [
                'success' => true,
                'user' => $user
            ];
        } catch (Exception $e) {
            Database::rollback();
            error_log("Registration failed: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['general' => '登録処理中にエラーが発生しました。']
            ];
        }
    }
    
    /**
     * Hash password
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword(string $password): string {
        return User::hashPassword($password);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return User::verifyPassword($password, $hash);
    }
    
    /**
     * Create session for user
     * 
     * @param User $user User object
     * @return bool True on success
     */
    public static function createSession(User $user): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['team_id'] = $user->getTeamId();
        $_SESSION['logged_in'] = true;
        
        return true;
    }
    
    /**
     * Destroy user session
     * 
     * @return bool True on success
     */
    public static function destroySession(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        return session_destroy();
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated
     */
    public static function isAuthenticated(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user ID from session
     * 
     * @return int|null User ID or null if not authenticated
     */
    public static function getCurrentUserId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current team ID from session
     * 
     * @return int|null Team ID or null if not authenticated
     */
    public static function getCurrentTeamId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['team_id'] ?? null;
    }
}
