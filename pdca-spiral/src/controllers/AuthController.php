<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

class AuthController {
    /**
     * Handle user registration
     * 
     * @param array $data Registration data
     * @return array Result with success status and data/errors
     */
    public static function register(array $data): array {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !validateCsrfToken($data['csrf_token'])) {
            return [
                'success' => false,
                'errors' => ['general' => 'Invalid request. Please try again.']
            ];
        }
        
        // Extract and sanitize data
        $username = sanitizeInput($data['username'] ?? '');
        $email = sanitizeInput($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';
        $teamCode = sanitizeInput($data['team_code'] ?? '');
        $teamName = sanitizeInput($data['team_name'] ?? '');
        $teamAction = $data['team_action'] ?? 'join';
        $createNew = ($teamAction === 'create');
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'ユーザー名を入力してください。';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'ユーザー名は3文字以上50文字以内で入力してください。';
        }
        
        if (empty($email)) {
            $errors['email'] = 'メールアドレスを入力してください。';
        } elseif (!validateEmail($email)) {
            $errors['email'] = '有効なメールアドレスを入力してください。';
        }
        
        if (empty($password)) {
            $errors['password'] = 'パスワードを入力してください。';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'パスワードは8文字以上で入力してください。';
        }
        
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'パスワードが一致しません。';
        }
        
        if (empty($teamCode)) {
            $errors['team_code'] = 'チームコードを入力してください。';
        }
        
        if ($createNew && empty($teamName)) {
            $errors['team_name'] = 'チーム名を入力してください。';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Register user
        $result = AuthService::register($username, $email, $password, $teamCode, $teamName, $createNew);
        
        if ($result['success']) {
            // Create session
            AuthService::createSession($result['user']);
            setFlashMessage('success', '登録が完了しました。');
        }
        
        return $result;
    }
    
    /**
     * Handle user login
     * 
     * @param array $data Login data
     * @return array Result with success status and data/errors
     */
    public static function login(array $data): array {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !validateCsrfToken($data['csrf_token'])) {
            return [
                'success' => false,
                'errors' => ['general' => 'Invalid request. Please try again.']
            ];
        }
        
        // Extract and sanitize data
        $username = sanitizeInput($data['username'] ?? '');
        $password = $data['password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'ユーザー名を入力してください。';
        }
        
        if (empty($password)) {
            $errors['password'] = 'パスワードを入力してください。';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Authenticate user
        $user = AuthService::authenticate($username, $password);
        
        if ($user === null) {
            return [
                'success' => false,
                'errors' => ['general' => 'ユーザー名またはパスワードが正しくありません。']
            ];
        }
        
        // Create session
        AuthService::createSession($user);
        setFlashMessage('success', 'ログインしました。');
        
        return [
            'success' => true,
            'user' => $user
        ];
    }
    
    /**
     * Handle user logout
     * 
     * @return bool True on success
     */
    public static function logout(): bool {
        $result = AuthService::destroySession();
        setFlashMessage('success', 'ログアウトしました。');
        return $result;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated
     */
    public static function checkAuth(): bool {
        return AuthService::isAuthenticated();
    }
}
