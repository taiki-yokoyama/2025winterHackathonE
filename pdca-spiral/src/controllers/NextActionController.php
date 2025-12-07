<?php

require_once __DIR__ . '/../models/NextAction.php';
require_once __DIR__ . '/../models/PDCACycle.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

class NextActionController {
    /**
     * Create new next action
     * 
     * @param array $data Action data
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
        $description = sanitizeInput($data['description'] ?? '');
        $targetDate = $data['target_date'] ?? '';
        
        // Validation
        $errors = [];
        
        if (!validateRequired($description)) {
            $errors['description'] = 'アクションの説明を入力してください。';
        }
        
        if (empty($targetDate)) {
            $errors['target_date'] = '目標日を入力してください。';
        } elseif (!validateDate($targetDate)) {
            $errors['target_date'] = '有効な日付を入力してください。';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Create next action
        $action = new NextAction(
            $teamId,
            $cycle->getId(),
            $userId,
            $description,
            $targetDate,
            'pending'
        );
        
        // Validate action
        $validationErrors = $action->validate();
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors
            ];
        }
        
        // Save action
        if ($action->save()) {
            setFlashMessage('success', 'ネクストアクションを追加しました。');
            return [
                'success' => true,
                'action' => $action
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['general' => 'アクションの保存に失敗しました。']
            ];
        }
    }
    
    /**
     * Get actions list for team
     * 
     * @param int $teamId Team ID
     * @param int|null $cycleId Optional cycle ID for filtering
     * @return array Array of actions
     */
    public static function list(int $teamId, ?int $cycleId = null): array {
        if ($cycleId !== null) {
            return NextAction::findByCycle($cycleId);
        }
        
        return NextAction::findByTeam($teamId);
    }
    
    /**
     * Update action status
     * 
     * @param int $actionId Action ID
     * @param string $status New status
     * @return bool True on success
     */
    public static function updateStatus(int $actionId, string $status): bool {
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        if (NextAction::updateStatus($actionId, $status)) {
            setFlashMessage('success', 'ステータスを更新しました。');
            return true;
        }
        
        return false;
    }
    
    /**
     * Get actions by team
     * 
     * @param int $teamId Team ID
     * @return array Array of actions
     */
    public static function getByTeam(int $teamId): array {
        return NextAction::findByTeam($teamId);
    }
}
