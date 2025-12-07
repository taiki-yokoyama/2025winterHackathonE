<?php
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/models/Team.php';
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/validation.php';

requireAuth();

$userId = getCurrentUserId();
$teamId = getCurrentTeamId();

// Check if user has team - if not, get from database
if ($teamId === null && $userId !== null) {
    require_once __DIR__ . '/models/User.php';
    $user = User::findById($userId);
    if ($user !== null) {
        $teamId = $user->getTeamId();
        // Update session with team_id
        $_SESSION['team_id'] = $teamId;
    }
}

// If still no team_id, force re-login
if ($teamId === null) {
    session_destroy();
    header('Location: /login.php?error=session_expired');
    exit;
}

$dashboardData = DashboardController::index($teamId);

$currentCycle = $dashboardData['current_cycle'];
$statistics = $dashboardData['statistics'];
$recentEvaluations = $dashboardData['recent_evaluations'];
$pendingActions = $dashboardData['pending_actions'];

// Get team info and members
$team = Team::findById($teamId);
$teamMembers = $team ? $team->getMembers() : [];

$pageTitle = 'ダッシュボード - PDCA Portal';
$additionalScripts = ['/assets/js/spiral-visualization.js'];
require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Section with Team Info -->
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold mb-2" style="color: #1976d2;">
                    ようこそ、<?php echo e(getCurrentUsername()); ?>さん
                </h1>
                <p class="text-gray-600">継続的な改善で、チームを次のレベルへ</p>
            </div>
            <?php if ($team): ?>
            <div class="text-right">
                <div class="text-sm text-gray-600">チーム</div>
                <div class="text-xl font-bold" style="color: #1976d2;">
                    <?php echo e($team->getTeamName()); ?>
                </div>
                <div class="text-sm text-gray-500">
                    コード: <?php echo e($team->getTeamCode()); ?>
                </div>
                <div class="text-sm text-gray-500">
                    メンバー: <?php echo count($teamMembers); ?>人
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($currentCycle === null): ?>
    <!-- No Cycle State -->
    <div class="card text-center py-12">
        <div class="mb-6">
            <svg class="mx-auto" width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="40" cy="40" r="35" stroke="url(#gradient)" stroke-width="3" fill="none" opacity="0.3"/>
                <path d="M 40 5 Q 60 20, 60 40 T 40 75 Q 20 60, 20 40 T 40 5" stroke="url(#gradient)" stroke-width="4" fill="none" stroke-linecap="round"/>
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#1976d2;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#42a5f5;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <h2 class="text-2xl font-semibold text-gray-700 mb-2">
            PDCAサイクルを開始しましょう
        </h2>
        <p class="text-gray-600 mb-6">
            まだアクティブなサイクルがありません。最初の週次レビューを記録してサイクルを開始しましょう！
        </p>
        <a href="/weekly-review/create.php" class="btn-primary inline-block">
            週次レビューを開始
        </a>
    </div>
    <?php else: ?>
    
    <!-- Current Cycle Info -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Cycle Number -->
        <div class="card text-center">
            <p class="text-gray-600 mb-2 text-sm">現在の週</p>
            <p class="text-3xl font-bold" style="color: #1976d2;">
                <?php echo $currentCycle->getCycleNumber(); ?>週間目
            </p>
        </div>
        
        <!-- Average Score -->
        <div class="card text-center">
            <p class="text-gray-600 mb-2 text-sm">平均スコア</p>
            <p class="text-3xl font-bold" style="color: #1976d2;">
                <?php echo number_format($statistics['average_score'], 1); ?>
            </p>
        </div>
        
        <!-- Evaluations Count -->
        <div class="card text-center">
            <p class="text-gray-600 mb-2 text-sm">評価数</p>
            <p class="text-3xl font-bold" style="color: #1976d2;">
                <?php echo $statistics['evaluation_count']; ?>
            </p>
        </div>
        
        <!-- Actions Count -->
        <div class="card text-center">
            <p class="text-gray-600 mb-2 text-sm">アクション</p>
            <p class="text-3xl font-bold" style="color: #1976d2;">
                <?php echo $statistics['action_stats']['completed']; ?> / <?php echo $statistics['action_count']; ?>
            </p>
        </div>
    </div>
    
    <!-- Team Members Section -->
    <div class="card mb-6">
        <h2 class="text-xl font-semibold mb-4" style="color: #1976d2;">チームメンバー</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php foreach ($teamMembers as $member): ?>
            <div class="p-3 bg-gray-50 rounded text-center <?php echo $member->getId() === $userId ? 'border-2' : ''; ?>" style="<?php echo $member->getId() === $userId ? 'border-color: #1976d2;' : ''; ?>">
                <div class="w-12 h-12 rounded-full mx-auto mb-2 flex items-center justify-center text-white font-bold" style="background: #1976d2;">
                    <?php echo mb_substr($member->getUsername(), 0, 1); ?>
                </div>
                <div class="font-semibold text-sm"><?php echo e($member->getUsername()); ?></div>
                <?php if ($member->getId() === $userId): ?>
                <div class="text-xs text-blue-600">あなた</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Team Evaluations -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold" style="color: #1976d2;">チームの評価</h2>
                <a href="/evaluation/list.php" style="color: #1976d2;" class="hover:underline text-sm">
                    すべて見る →
                </a>
            </div>
            
            <?php if (empty($recentEvaluations)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>まだ評価がありません</p>
                <a href="/weekly-review/create.php" style="color: #1976d2;" class="hover:underline mt-2 inline-block">
                    週次レビューを記録
                </a>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentEvaluations as $evaluation): ?>
                <div class="p-3 bg-gray-50 rounded">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center" style="gap: 1rem;">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center text-white font-bold" style="width: 40px; height: 40px; min-width: 40px; background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%); box-shadow: 0 2px 4px rgba(25, 118, 210, 0.3); font-size: 1rem;">
                                <?php echo $evaluation->getScore(); ?>
                            </div>
                            <span class="font-semibold text-gray-800" style="font-size: 0.95rem;">
                                <?php echo e($evaluation->getUsername()); ?>
                            </span>
                        </div>
                        <span class="text-xs text-gray-500">
                            <?php echo date('m/d H:i', strtotime($evaluation->getCreatedAt())); ?>
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm line-clamp-2">
                        <?php echo e($evaluation->getReflection()); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="/weekly-review/create.php" class="btn-primary w-full text-center">
                    週次レビューを記録
                </a>
            </div>
        </div>
        
        <!-- Team Actions -->
        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold" style="color: #1976d2;">チームのアクション</h2>
                <a href="/next-action/list.php" style="color: #1976d2;" class="hover:underline text-sm">
                    すべて見る →
                </a>
            </div>
            
            <?php if (empty($pendingActions)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>進行中のアクションがありません</p>
                <a href="/weekly-review/create.php" style="color: #1976d2;" class="hover:underline mt-2 inline-block">
                    週次レビューでアクションを設定
                </a>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($pendingActions, 0, 5) as $action): ?>
                <div class="p-3 bg-gray-50 rounded">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800">
                                <?php echo e($action->getUsername()); ?>
                            </span>
                            <span class="inline-block px-2 py-1 text-xs rounded" style="<?php echo $action->getStatus() === 'in_progress' ? 'background: #bbdefb; color: #1565c0;' : 'background: #f5f5f5; color: #666;'; ?>">
                                <?php echo $action->getStatus() === 'in_progress' ? '進行中' : '未着手'; ?>
                            </span>
                        </div>
                        <span class="text-xs text-gray-500">
                            <?php echo date('m/d', strtotime($action->getTargetDate())); ?>
                        </span>
                    </div>
                    <p class="text-gray-700 text-sm line-clamp-2">
                        <?php echo e($action->getDescription()); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="/next-action/list.php" class="btn-secondary w-full text-center">
                    アクション一覧を見る
                </a>
            </div>
        </div>
    </div>
    
    <!-- Spiral Visualization -->
    <div class="card mb-6">
        <h2 class="text-xl font-semibold mb-4" style="color: #1976d2;">評価の推移</h2>
        <div id="spiralVisualization" class="w-full h-96 bg-gray-50 rounded flex items-center justify-center">
            <canvas id="spiralCanvas" width="800" height="400"></canvas>
        </div>
        <div class="mt-4 text-center text-gray-600 text-sm">
            螺旋階段のように、繰り返しながら上昇する改善プロセス
        </div>
    </div>
    
    <!-- Cycle Management -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4" style="color: #1976d2;">週の管理</h2>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">
                    <?php echo $currentCycle->getCycleNumber(); ?>週間目を完了して、次の週に進みますか？
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    開始日: <?php echo date('Y年m月d日', strtotime($currentCycle->getStartDate())); ?>
                </p>
            </div>
            <form method="POST" action="/cycle/complete.php" onsubmit="return confirm('本当にこの週を完了しますか？');">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="cycle_id" value="<?php echo $currentCycle->getId(); ?>">
                <button type="submit" class="btn-secondary">
                    週を完了
                </button>
            </form>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
// Pass evaluation data to JavaScript for visualization
const evaluationData = <?php echo json_encode(array_map(function($e) {
    return [
        'score' => $e->getScore(),
        'date' => $e->getCreatedAt(),
        'reflection' => $e->getReflection(),
        'username' => $e->getUsername()
    ];
}, $recentEvaluations)); ?>;
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
