<?php

require_once __DIR__ . '/../services/PDCACycleService.php';
require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/NextAction.php';

class DashboardController {
    /**
     * Get dashboard data for team
     * 
     * @param int $teamId Team ID
     * @return array Dashboard data
     */
    public static function index(int $teamId): array {
        $currentCycle = PDCACycleService::getCurrentCycle($teamId);
        
        if ($currentCycle === null) {
            return [
                'current_cycle' => null,
                'statistics' => null,
                'recent_evaluations' => [],
                'pending_actions' => []
            ];
        }
        
        $statistics = PDCACycleService::getCycleStatistics($currentCycle->getId());
        $recentEvaluations = Evaluation::findByCycle($currentCycle->getId());
        $actions = NextAction::findByCycle($currentCycle->getId());
        
        // Get weekly aggregated data for chart
        $weeklyChartData = Evaluation::findByTeamGroupedByWeek($teamId);
        
        // Get only pending and in_progress actions
        $pendingActions = array_filter($actions, function($action) {
            return in_array($action->getStatus(), ['pending', 'in_progress']);
        });
        
        // Sort evaluations by date (most recent first) and limit to 5
        usort($recentEvaluations, function($a, $b) {
            return strtotime($b->getCreatedAt()) - strtotime($a->getCreatedAt());
        });
        $recentEvaluations = array_slice($recentEvaluations, 0, 5);
        
        return [
            'current_cycle' => $currentCycle,
            'statistics' => $statistics,
            'recent_evaluations' => $recentEvaluations,
            'pending_actions' => array_values($pendingActions),
            'weekly_chart_data' => $weeklyChartData,
            'all_cycles' => PDCACycleService::getAllCycles($teamId)
        ];
    }
    
    /**
     * Get statistics for team
     * 
     * @param int $teamId Team ID
     * @return array Statistics data
     */
    public static function getStatistics(int $teamId): array {
        $currentCycle = PDCACycleService::getCurrentCycle($teamId);
        
        if ($currentCycle === null) {
            return [];
        }
        
        return PDCACycleService::getCycleStatistics($currentCycle->getId());
    }
}
