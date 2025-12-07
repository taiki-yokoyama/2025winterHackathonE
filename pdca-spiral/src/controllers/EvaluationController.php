<?php

require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/PDCACycle.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

class EvaluationController {
    /**
     * Create new evaluation
     * 
     * @param array $data Evaluation data
     * @param int $userId User ID
     * @param int $teamId Team ID
     * @return array Result with success status and data/errors
     */
    public static function create(array $data, int $userId, int $teamId): array {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !validateCsrfToken($data['csrf_token'])) {
            return [
                'success' => false,
                'errors' => ['general' => 'Invalid request. Please try again.']
            ];
        }
        
        // Get current cycle
        $cycle = PDCACycle::getCurrentCycle($teamId);
        if ($cycle === null) {
            return [
                'success' => false,
                'errors' => ['general' => 'アクティブなPDCAサイクルが見つかりません。']
            ];
        }
        
        // Extract and sanitize data
        $score = isset($data['score']) ? (int)$data['score'] : null;
        $reflection = sanitizeInput($data['reflection'] ?? '');
        
        // Validation
        $errors = [];
        
        if ($score === null) {
            $errors['score'] = 'スコアを選択してください。';
        } elseif (!validateScore($score)) {
            $errors['score'] = 'スコアは0から10の範囲で入力してください。';
        }
        
        if (!validateRequired($reflection)) {
            $errors['reflection'] = '振り返りを入力してください。';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Create evaluation
        $evaluation = new Evaluation(
            $userId,
            $teamId,
            $cycle->getId(),
            $score,
            $reflection
        );
        
        // Validate evaluation
        $validationErrors = $evaluation->validate();
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors
            ];
        }
        
        // Save evaluation
        if ($evaluation->save()) {
            setFlashMessage('success', '評価を記録しました。');
            return [
                'success' => true,
                'evaluation' => $evaluation
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['general' => '評価の保存に失敗しました。']
            ];
        }
    }
    
    /**
     * Get evaluations list for team
     * 
     * @param int $teamId Team ID
     * @param int|null $cycleId Optional cycle ID for filtering
     * @return array Array of evaluations
     */
    public static function list(int $teamId, ?int $cycleId = null): array {
        if ($cycleId !== null) {
            return Evaluation::findByCycle($cycleId);
        }
        
        return Evaluation::findByTeam($teamId);
    }
    
    /**
     * Get evaluations by team
     * 
     * @param int $teamId Team ID
     * @return array Array of evaluations
     */
    public static function getByTeam(int $teamId): array {
        return Evaluation::findByTeam($teamId);
    }
    
    /**
     * Get evaluations by cycle
     * 
     * @param int $cycleId Cycle ID
     * @return array Array of evaluations
     */
    public static function getByCycle(int $cycleId): array {
        return Evaluation::findByCycle($cycleId);
    }
    
    /**
     * Get average score for cycle
     * 
     * @param int $cycleId Cycle ID
     * @return float Average score
     */
    public static function getAverageScore(int $cycleId): float {
        return Evaluation::getAverageScore($cycleId);
    }
}
