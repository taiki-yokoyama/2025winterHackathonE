<?php
require_once __DIR__ . '/../controllers/EvaluationController.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$userId = getCurrentUserId();
$teamId = getCurrentTeamId();
$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = EvaluationController::create($_POST, $userId, $teamId);
    
    if ($result['success']) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $errors = $result['errors'];
        $formData = $_POST;
    }
}

$pageTitle = '評価を記録 - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="card">
        <h1 class="text-3xl font-bold mb-6 spiral-gradient bg-clip-text text-transparent">
            チーム評価を記録
        </h1>
        
        <p class="text-gray-600 mb-6">
            チーム全体のパフォーマンスを0から10のスコアで評価し、その原因や理由を振り返りましょう。
        </p>
        
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo e($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/evaluation/create.php" id="evaluationForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <!-- Score Slider -->
            <div class="mb-6">
                <label for="score" class="block text-gray-700 font-semibold mb-2">
                    スコア (0-10) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">0</span>
                    <input 
                        type="range" 
                        id="score" 
                        name="score" 
                        min="0" 
                        max="10" 
                        value="<?php echo e($formData['score'] ?? '5'); ?>"
                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        oninput="document.getElementById('scoreValue').textContent = this.value"
                    >
                    <span class="text-gray-600">10</span>
                </div>
                <div class="text-center mt-2">
                    <span class="text-4xl font-bold spiral-gradient bg-clip-text text-transparent" id="scoreValue">
                        <?php echo e($formData['score'] ?? '5'); ?>
                    </span>
                </div>
                <?php if (isset($errors['score'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['score']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Reflection -->
            <div class="mb-6">
                <label for="reflection" class="block text-gray-700 font-semibold mb-2">
                    振り返り・原因・理由 <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="reflection" 
                    name="reflection" 
                    rows="6"
                    class="input-field <?php echo isset($errors['reflection']) ? 'border-red-500' : ''; ?>"
                    placeholder="今回のスコアをつけた理由や、チームの状況について振り返ってください。&#10;&#10;例：&#10;- コミュニケーションが改善された&#10;- タスクの優先順位付けに課題がある&#10;- 新しいツールの導入がうまくいった"
                    required
                ><?php echo e($formData['reflection'] ?? ''); ?></textarea>
                <?php if (isset($errors['reflection'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['reflection']); ?></p>
                <?php else: ?>
                <p class="text-gray-500 text-sm mt-1">もやもやした感情を言語化してみましょう</p>
                <?php endif; ?>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="submit" class="btn-primary flex-1">
                    評価を記録
                </button>
                <a href="/dashboard.php" class="btn-secondary flex-1 text-center">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Save form data to localStorage on input
document.getElementById('evaluationForm').addEventListener('input', function() {
    const formData = {
        score: document.getElementById('score').value,
        reflection: document.getElementById('reflection').value,
        timestamp: Date.now()
    };
    localStorage.setItem('evaluation_draft', JSON.stringify(formData));
});

// Restore form data from localStorage on page load
window.addEventListener('DOMContentLoaded', function() {
    const savedData = localStorage.getItem('evaluation_draft');
    if (savedData) {
        const data = JSON.parse(savedData);
        // Only restore if less than 1 hour old
        if (Date.now() - data.timestamp < 3600000) {
            if (!document.getElementById('score').value || document.getElementById('score').value === '5') {
                document.getElementById('score').value = data.score;
                document.getElementById('scoreValue').textContent = data.score;
            }
            if (!document.getElementById('reflection').value) {
                document.getElementById('reflection').value = data.reflection;
            }
        }
    }
});

// Clear localStorage on successful submission
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)): ?>
localStorage.removeItem('evaluation_draft');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
