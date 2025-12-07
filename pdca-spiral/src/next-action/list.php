<?php
require_once __DIR__ . '/../controllers/NextActionController.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$teamId = getCurrentTeamId();
$actions = NextActionController::getByTeam($teamId);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_id']) && isset($_POST['status'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        NextActionController::updateStatus((int)$_POST['action_id'], $_POST['status']);
        header('Location: /next-action/list.php');
        exit;
    }
}

$pageTitle = 'ネクストアクション - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold" style="color: #1976d2;">
            チームのアクション
        </h1>
        <a href="/weekly-review/create.php" class="btn-primary">
            週次レビューを記録
        </a>
    </div>
    
    <?php if (empty($actions)): ?>
    <div class="card text-center py-12">
        <div class="mb-6">
            <svg class="mx-auto" width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="40" cy="40" r="30" stroke="url(#gradient3)" stroke-width="3" fill="none"/>
                <circle cx="40" cy="40" r="20" stroke="url(#gradient3)" stroke-width="3" fill="none"/>
                <circle cx="40" cy="40" r="10" stroke="url(#gradient3)" stroke-width="3" fill="none"/>
                <circle cx="40" cy="40" r="3" fill="url(#gradient3)"/>
                <defs>
                    <linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#1976d2;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#42a5f5;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <h2 class="text-2xl font-semibold text-gray-700 mb-2">
            まだアクションがありません
        </h2>
        <p class="text-gray-600 mb-6">
            週次レビューで改善のための具体的なアクションを設定しましょう！
        </p>
        <a href="/weekly-review/create.php" class="btn-primary inline-block">
            週次レビューを開始
        </a>
    </div>
    <?php else: ?>
    
    <?php
    // Group actions by status
    $groupedActions = [
        'pending' => [],
        'in_progress' => [],
        'completed' => [],
        'cancelled' => []
    ];
    
    foreach ($actions as $action) {
        $groupedActions[$action->getStatus()][] = $action;
    }
    
    $statusLabels = [
        'pending' => ['label' => '未着手', 'color' => 'background: #f5f5f5; color: #666;'],
        'in_progress' => ['label' => '進行中', 'color' => 'background: #bbdefb; color: #1565c0;'],
        'completed' => ['label' => '完了', 'color' => 'background: #c8e6c9; color: #2e7d32;'],
        'cancelled' => ['label' => 'キャンセル', 'color' => 'background: #ffcdd2; color: #c62828;']
    ];
    ?>
    
    <?php foreach (['pending', 'in_progress', 'completed'] as $status): ?>
        <?php if (!empty($groupedActions[$status])): ?>
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <span class="inline-block px-3 py-1 rounded text-sm mr-2" style="<?php echo $statusLabels[$status]['color']; ?>">
                    <?php echo $statusLabels[$status]['label']; ?>
                </span>
                <span class="text-gray-500">(<?php echo count($groupedActions[$status]); ?>)</span>
            </h2>
            
            <div class="space-y-3">
                <?php foreach ($groupedActions[$status] as $action): ?>
                <div class="card">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background: #1976d2;">
                                    <?php echo mb_substr($action->getUsername(), 0, 1); ?>
                                </div>
                                <span class="font-semibold text-gray-800">
                                    <?php echo e($action->getUsername()); ?>
                                </span>
                            </div>
                            <p class="text-gray-800 mb-2 whitespace-pre-wrap">
                                <?php echo e($action->getDescription()); ?>
                            </p>
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span>目標: <?php echo date('Y年m月d日', strtotime($action->getTargetDate())); ?></span>
                                <?php
                                $daysUntil = (strtotime($action->getTargetDate()) - time()) / 86400;
                                if ($daysUntil < 0 && $status !== 'completed'):
                                ?>
                                <span class="text-red-600 font-semibold">期限超過</span>
                                <?php elseif ($daysUntil <= 3 && $status !== 'completed'): ?>
                                <span class="text-orange-600 font-semibold">期限間近</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="ml-4">
                            <form method="POST" action="/next-action/list.php" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="action_id" value="<?php echo $action->getId(); ?>">
                                <select 
                                    name="status" 
                                    class="input-field py-1 px-2 text-sm"
                                    onchange="this.form.submit()"
                                >
                                    <?php foreach ($statusLabels as $statusValue => $statusInfo): ?>
                                    <option value="<?php echo $statusValue; ?>" <?php echo $action->getStatus() === $statusValue ? 'selected' : ''; ?>>
                                        <?php echo $statusInfo['label']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
