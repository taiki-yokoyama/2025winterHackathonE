<?php
require_once __DIR__ . '/../controllers/EvaluationController.php';
require_once __DIR__ . '/../models/PDCACycle.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$teamId = getCurrentTeamId();
$currentCycle = PDCACycle::getCurrentCycle($teamId);
$evaluations = EvaluationController::getByTeam($teamId);
$averageScore = $currentCycle ? EvaluationController::getAverageScore($currentCycle->getId()) : 0;

// Group evaluations by cycle
$evaluationsByCycle = [];
foreach ($evaluations as $evaluation) {
    $cycleId = $evaluation->getCycleId();
    if (!isset($evaluationsByCycle[$cycleId])) {
        $evaluationsByCycle[$cycleId] = [];
    }
    $evaluationsByCycle[$cycleId][] = $evaluation;
}

// Calculate average score for each cycle
$cycleAverages = [];
foreach ($evaluationsByCycle as $cycleId => $cycleEvaluations) {
    $total = 0;
    foreach ($cycleEvaluations as $eval) {
        $total += $eval->getScore();
    }
    $cycleAverages[$cycleId] = count($cycleEvaluations) > 0 ? $total / count($cycleEvaluations) : 0;
}

// Sort cycles in descending order (newest first)
krsort($evaluationsByCycle);

$pageTitle = '評価一覧 - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold spiral-gradient bg-clip-text text-transparent">
            評価一覧
        </h1>
        <a href="/evaluation/create.php" class="btn-primary">
            + 新しい評価を記録
        </a>
    </div>
    
    <?php if ($currentCycle): ?>
    <div class="card mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    <?php echo $currentCycle->getCycleNumber(); ?>週間目
                </h2>
                <p class="text-gray-600">
                    開始日: <?php echo date('Y年m月d日', strtotime($currentCycle->getStartDate())); ?>
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">平均スコア</p>
                <p class="text-4xl font-bold spiral-gradient bg-clip-text text-transparent">
                    <?php echo number_format($averageScore, 1); ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (empty($evaluations)): ?>
    <div class="card text-center py-12">
        <div class="mb-6">
            <svg class="mx-auto" width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="15" y="10" width="50" height="60" rx="4" stroke="url(#gradient2)" stroke-width="3" fill="none"/>
                <line x1="25" y1="25" x2="55" y2="25" stroke="url(#gradient2)" stroke-width="2" stroke-linecap="round"/>
                <line x1="25" y1="35" x2="55" y2="35" stroke="url(#gradient2)" stroke-width="2" stroke-linecap="round"/>
                <line x1="25" y1="45" x2="45" y2="45" stroke="url(#gradient2)" stroke-width="2" stroke-linecap="round"/>
                <defs>
                    <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#5e35b1;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#7e57c2;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <h2 class="text-2xl font-semibold text-gray-700 mb-2">
            まだ評価がありません
        </h2>
        <p class="text-gray-600 mb-6">
            最初の週次レビューを記録して、チームの改善サイクルを始めましょう！
        </p>
        <a href="/weekly-review/create.php" class="btn-primary inline-block">
            週次レビューを開始
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-6">
        <?php foreach ($evaluationsByCycle as $cycleId => $cycleEvaluations): ?>
        <!-- Cycle Header -->
        <div>
            <div class="card mb-3" style="background: linear-gradient(135deg, #5e35b1 0%, #7e57c2 100%);">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-white">
                            <?php echo $cycleId; ?>週間目
                        </h3>
                        <p class="text-white text-sm" style="opacity: 0.9;">
                            評価数: <?php echo count($cycleEvaluations); ?>件
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-white text-sm" style="opacity: 0.9;">平均スコア</p>
                        <p class="text-3xl font-bold text-white">
                            <?php echo number_format($cycleAverages[$cycleId], 1); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Evaluations in this cycle -->
            <div class="space-y-3 ml-4">
                <?php foreach ($cycleEvaluations as $evaluation): ?>
                <div class="card">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center" style="gap: 1rem;">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center text-white font-bold" style="width: 44px; height: 44px; min-width: 44px; background: linear-gradient(135deg, #5e35b1 0%, #7e57c2 100%); box-shadow: 0 2px 4px rgba(94, 53, 177, 0.3); font-size: 1.125rem;">
                                <?php echo $evaluation->getScore(); ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800" style="font-size: 0.95rem;">
                                    <?php echo e($evaluation->getUsername()); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('Y年m月d日 H:i', strtotime($evaluation->getCreatedAt())); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-gray-50 rounded">
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo e($evaluation->getReflection()); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
