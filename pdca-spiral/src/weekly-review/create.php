<?php
require_once __DIR__ . '/../controllers/EvaluationController.php';
require_once __DIR__ . '/../controllers/NextActionController.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$userId = getCurrentUserId();
$teamId = getCurrentTeamId();
$errors = [];
$formData = [];
$step = $_POST['step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 3) {
        // 最終ステップ：すべてを保存
        Database::beginTransaction();
        
        try {
            // 評価を保存
            $evaluationResult = EvaluationController::create($_POST, $userId, $teamId);
            
            if (!$evaluationResult['success']) {
                Database::rollback();
                $errors = $evaluationResult['errors'];
                $formData = $_POST;
            } else {
                // ネクストアクションを保存
                $actionResult = NextActionController::create($_POST, $userId, $teamId);
                
                if (!$actionResult['success']) {
                    Database::rollback();
                    $errors = $actionResult['errors'];
                    $formData = $_POST;
                } else {
                    Database::commit();
                    setFlashMessage('success', '週次レビューを完了しました！');
                    header('Location: /dashboard.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            Database::rollback();
            $errors['general'] = 'エラーが発生しました。もう一度お試しください。';
            $formData = $_POST;
        }
    } else {
        // 次のステップへ
        $formData = $_POST;
        $step = (int)$step + 1;
    }
}

$pageTitle = '週次レビュー - PDCA Portal';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="card">
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold <?php echo $step >= 1 ? 'text-blue-700' : 'text-gray-400'; ?>">
                    1. 評価
                </span>
                <span class="text-sm font-semibold <?php echo $step >= 2 ? 'text-blue-700' : 'text-gray-400'; ?>">
                    2. 振り返り
                </span>
                <span class="text-sm font-semibold <?php echo $step >= 3 ? 'text-blue-700' : 'text-gray-400'; ?>">
                    3. アクション
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-700 h-2 rounded-full transition-all duration-300" style="width: <?php echo ($step / 3) * 100; ?>%"></div>
            </div>
        </div>

        <h1 class="text-2xl font-bold mb-2" style="color: #1976d2;">
            週次レビュー
        </h1>
        <p class="text-gray-600 mb-6">
            今週のチームパフォーマンスを振り返り、次週のアクションを決めましょう
        </p>
        
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 mb-4" style="border-left: 4px solid #f44336; border-radius: 4px;">
            <?php echo e($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/weekly-review/create.php" id="reviewForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="step" value="<?php echo $step; ?>">
            
            <!-- Step 1: Score -->
            <?php if ($step == 1): ?>
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">今週のチーム評価</h2>
                <p class="text-gray-600 mb-6">
                    今週のチーム全体のパフォーマンスを10点満点で評価してください
                </p>
                
                <div class="mb-6">
                    <label for="score" class="block text-gray-700 font-semibold mb-3">
                        スコア <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-4 mb-4">
                        <span class="text-gray-600 text-sm">0点</span>
                        <input 
                            type="range" 
                            id="score" 
                            name="score" 
                            min="0" 
                            max="10" 
                            value="<?php echo e($formData['score'] ?? '5'); ?>"
                            class="flex-1"
                            style="height: 8px; border-radius: 4px; background: #e0e0e0;"
                            oninput="updateScoreDisplay(this.value)"
                            required
                        >
                        <span class="text-gray-600 text-sm">10点</span>
                    </div>
                    <div class="text-center p-6 bg-gray-50 rounded" style="border: 2px solid #1976d2;">
                        <div class="text-5xl font-bold mb-2" style="color: #1976d2;" id="scoreValue">
                            <?php echo e($formData['score'] ?? '5'); ?>
                        </div>
                        <div class="text-sm text-gray-600" id="scoreLabel">普通</div>
                    </div>
                    <?php if (isset($errors['score'])): ?>
                    <p class="text-red-500 text-sm mt-2"><?php echo e($errors['score']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Step 2: Reflection -->
            <?php if ($step == 2): ?>
            <input type="hidden" name="score" value="<?php echo e($formData['score'] ?? ''); ?>">
            
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">振り返り</h2>
                <p class="text-gray-600 mb-6">
                    このスコアをつけた理由や要因を具体的に記入してください
                </p>
                
                <div class="mb-4 p-4 bg-blue-50 rounded">
                    <div class="text-sm text-gray-600 mb-1">あなたの評価</div>
                    <div class="text-3xl font-bold" style="color: #1976d2;">
                        <?php echo e($formData['score']); ?>点
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="reflection" class="block text-gray-700 font-semibold mb-2">
                        評価の理由・要因 <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="reflection" 
                        name="reflection" 
                        rows="8"
                        class="input-field <?php echo isset($errors['reflection']) ? 'border-red-500' : ''; ?>"
                        placeholder="例：&#10;&#10;【良かった点】&#10;・チーム内のコミュニケーションが活発になった&#10;・タスクの進捗が予定通り進んだ&#10;&#10;【課題点】&#10;・ドキュメント作成が遅れている&#10;・テストの自動化が進んでいない"
                        required
                    ><?php echo e($formData['reflection'] ?? ''); ?></textarea>
                    <?php if (isset($errors['reflection'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($errors['reflection']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm mt-1">良かった点と課題点を具体的に書きましょう</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Step 3: Next Action -->
            <?php if ($step == 3): ?>
            <input type="hidden" name="score" value="<?php echo e($formData['score'] ?? ''); ?>">
            <input type="hidden" name="reflection" value="<?php echo e($formData['reflection'] ?? ''); ?>">
            
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">ネクストアクション</h2>
                <p class="text-gray-600 mb-6">
                    来週取り組む具体的なアクションを決めましょう
                </p>
                
                <div class="mb-4 p-4 bg-gray-50 rounded">
                    <div class="text-sm text-gray-600 mb-2">今週の評価</div>
                    <div class="text-2xl font-bold mb-3" style="color: #1976d2;">
                        <?php echo e($formData['score']); ?>点
                    </div>
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">
                        <?php echo e(mb_substr($formData['reflection'] ?? '', 0, 100)); ?>...
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 font-semibold mb-2">
                        アクション内容 <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="5"
                        class="input-field <?php echo isset($errors['description']) ? 'border-red-500' : ''; ?>"
                        placeholder="例：&#10;・毎朝10分のスタンドアップミーティングを実施する&#10;・ドキュメント作成のテンプレートを整備する&#10;・テスト自動化の勉強会を開催する"
                        required
                    ><?php echo e($formData['description'] ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($errors['description']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm mt-1">実行可能な具体的なアクションを記入してください</p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label for="target_date" class="block text-gray-700 font-semibold mb-2">
                        目標日 <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="target_date" 
                        name="target_date" 
                        class="input-field <?php echo isset($errors['target_date']) ? 'border-red-500' : ''; ?>"
                        value="<?php echo e($formData['target_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >
                    <?php if (isset($errors['target_date'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($errors['target_date']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm mt-1">通常は1週間後を設定します</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Navigation Buttons -->
            <div class="flex space-x-4">
                <?php if ($step == 1): ?>
                <a href="/dashboard.php" class="btn-secondary flex-1 text-center">
                    キャンセル
                </a>
                <?php endif; ?>
                
                <?php if ($step > 1): ?>
                <button type="button" onclick="history.back()" class="btn-secondary flex-1">
                    戻る
                </button>
                <?php endif; ?>
                
                <button type="submit" class="btn-primary flex-1">
                    <?php echo $step == 3 ? '完了' : '次へ'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateScoreDisplay(value) {
    document.getElementById('scoreValue').textContent = value;
    
    const labels = {
        0: '非常に悪い', 1: '非常に悪い', 2: '悪い', 3: '悪い',
        4: 'やや悪い', 5: '普通', 6: 'やや良い',
        7: '良い', 8: '良い', 9: '非常に良い', 10: '非常に良い'
    };
    document.getElementById('scoreLabel').textContent = labels[value];
}

// Auto-save to localStorage
const form = document.getElementById('reviewForm');
if (form) {
    form.addEventListener('input', function() {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.timestamp = Date.now();
        localStorage.setItem('weekly_review_draft', JSON.stringify(data));
    });
}

// Restore from localStorage
window.addEventListener('DOMContentLoaded', function() {
    const savedData = localStorage.getItem('weekly_review_draft');
    if (savedData) {
        const data = JSON.parse(savedData);
        // Only restore if less than 24 hours old
        if (Date.now() - data.timestamp < 86400000) {
            // Restore logic here if needed
        }
    }
});
</script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
