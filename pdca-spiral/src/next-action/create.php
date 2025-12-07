<?php
require_once __DIR__ . '/../controllers/NextActionController.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$userId = getCurrentUserId();
$teamId = getCurrentTeamId();
$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = NextActionController::create($_POST, $userId, $teamId);
    
    if ($result['success']) {
        header('Location: /next-action/list.php');
        exit;
    } else {
        $errors = $result['errors'];
        $formData = $_POST;
    }
}

$pageTitle = 'ネクストアクション追加 - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="card">
        <h1 class="text-3xl font-bold mb-6 spiral-gradient bg-clip-text text-transparent">
            ネクストアクションを追加
        </h1>
        
        <p class="text-gray-600 mb-6">
            チームの改善のために取り組む具体的なアクションを決定しましょう。
        </p>
        
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo e($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/next-action/create.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-gray-700 font-semibold mb-2">
                    アクションの説明 <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4"
                    class="input-field <?php echo isset($errors['description']) ? 'border-red-500' : ''; ?>"
                    placeholder="具体的なアクションを記入してください。&#10;&#10;例：&#10;- 毎朝15分のスタンドアップミーティングを実施する&#10;- タスク管理ツールの導入を検討する&#10;- コードレビューのガイドラインを作成する"
                    required
                ><?php echo e($formData['description'] ?? ''); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['description']); ?></p>
                <?php else: ?>
                <p class="text-gray-500 text-sm mt-1">実行可能な具体的なアクションを記入してください</p>
                <?php endif; ?>
            </div>
            
            <!-- Target Date -->
            <div class="mb-6">
                <label for="target_date" class="block text-gray-700 font-semibold mb-2">
                    目標日 <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="target_date" 
                    name="target_date" 
                    class="input-field <?php echo isset($errors['target_date']) ? 'border-red-500' : ''; ?>"
                    value="<?php echo e($formData['target_date'] ?? ''); ?>"
                    min="<?php echo date('Y-m-d'); ?>"
                    required
                >
                <?php if (isset($errors['target_date'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['target_date']); ?></p>
                <?php else: ?>
                <p class="text-gray-500 text-sm mt-1">このアクションを完了する目標日を設定してください</p>
                <?php endif; ?>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="submit" class="btn-primary flex-1">
                    アクションを追加
                </button>
                <a href="/next-action/list.php" class="btn-secondary flex-1 text-center">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
