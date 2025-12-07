<?php

/**
 * Initialize secure session
 */
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session configuration
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '0'); // Set to 1 in production with HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
    }
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken(): string {
    initSession();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCsrfToken(string $token): bool {
    initSession();
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require authentication
 * Redirects to login if not authenticated
 */
function requireAuth(): void {
    initSession();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null
 */
function getCurrentUserId(): ?int {
    initSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current team ID
 * 
 * @return int|null Team ID or null
 */
function getCurrentTeamId(): ?int {
    initSession();
    return $_SESSION['team_id'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null Username or null
 */
function getCurrentUsername(): ?string {
    initSession();
    return $_SESSION['username'] ?? null;
}

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, info)
 * @param string $message Message text
 */
function setFlashMessage(string $type, string $message): void {
    initSession();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message array or null
 */
function getFlashMessage(): ?array {
    initSession();
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Save form data to session (for recovery after session expiry)
 * 
 * @param string $formName Form identifier
 * @param array $data Form data
 */
function saveFormData(string $formName, array $data): void {
    initSession();
    $_SESSION['form_data'][$formName] = $data;
}

/**
 * Get and clear saved form data
 * 
 * @param string $formName Form identifier
 * @return array|null Form data or null
 */
function getSavedFormData(string $formName): ?array {
    initSession();
    
    if (isset($_SESSION['form_data'][$formName])) {
        $data = $_SESSION['form_data'][$formName];
        unset($_SESSION['form_data'][$formName]);
        return $data;
    }
    
    return null;
}
