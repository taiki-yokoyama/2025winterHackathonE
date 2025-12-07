<?php

require_once __DIR__ . '/../models/PDCACycle.php';
require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/NextAction.php';

class PDCACycleService {
    /**
     * Get current active cycle for team
     * 
     * @param int $teamId Team ID
     * @return PDCACycle|null Current cycle or null
     */
    public static function getCurrentCycle(int $teamId): ?PDCACycle {
        return PDCACycle::getCurrentCycle($teamId);
    }
    
    /**
     * Complete current cycle and create new one
     * 
     * @param int $cycleId Cycle ID to complete
     * @return bool True on success
     */
    public static function completeCycle(int $cycleId): bool {
        $cycle = PDCACycle::findById($cycleId);
        
        if ($cycle === null) {
            return false;
        }
        
        return $cycle->completeCycle();
    }
    
    /**
     * Create new cycle for team
     * 
     * @param int $teamId Team ID
     * @param int $cycleNumber Cycle number
     * @return PDCACycle|null Created cycle or null on failure
     */
    public static function createNewCycle(int $teamId, int $cycleNumber): ?PDCACycle {
        $cycle = new PDCACycle($teamId, $cycleNumber, 'active');
        
        if ($cycle->save()) {
            return $cycle;
        }
        
        return null;
    }
    
    /**
     * Get cycle statistics
     * 
     * @param int $cycleId Cycle ID
     * @return array Statistics array
     */
    public static function getCycleStatistics(int $cycleId): array {
        $evaluations = Evaluation::findByCycle($cycleId);
        $actions = NextAction::findByCycle($cycleId);
        $averageScore = Evaluation::getAverageScore($cycleId);
        
        // Count actions by status
        $actionStats = [
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        foreach ($actions as $action) {
            $actionStats[$action->getStatus()]++;
        }
        
        return [
            'evaluation_count' => count($evaluations),
            'average_score' => $averageScore,
            'action_count' => count($actions),
            'action_stats' => $actionStats
        ];
    }
    
    /**
     * Get all cycles for team
     * 
     * @param int $teamId Team ID
     * @return array Array of cycles
     */
    public static function getAllCycles(int $teamId): array {
        return PDCACycle::findByTeam($teamId);
    }
}
