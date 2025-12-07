<?php

/**
 * Sanitize user input
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * 
 * @param string $email Email address
 * @return bool True if valid
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate score (0-10 range)
 * 
 * @param mixed $score Score value
 * @return bool True if valid
 */
function validateScore($score): bool {
    if (!is_numeric($score)) {
        return false;
    }
    
    $score = (int)$score;
    return $score >= 0 && $score <= 10;
}

/**
 * Validate required field
 * 
 * @param string $value Field value
 * @return bool True if not empty
 */
function validateRequired(string $value): bool {
    return !empty(trim($value));
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date Date string
 * @return bool True if valid
 */
function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Escape output for HTML
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
