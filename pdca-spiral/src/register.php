<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/utils/session.php';
require_once __DIR__ . '/utils/validation.php';

initSession();

// Redirect if already logged in
if (AuthController::checkAuth()) {
    header('Location: /dashboard.php');
    exit;
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::register($_POST);
    
    if ($result['success']) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $errors = $result['errors'];
        $formData = $_POST;
    }
}

$pageTitle = '新規登録 - PDCA Portal';
require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="card">
        <h1 class="text-2xl font-bold text-center mb-6" style="color: #1976d2;">
            新規登録
        </h1>
        
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 mb-4" style="border-left: 4px solid #f44336; border-radius: 4px;">
            <?php echo e($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/register.php" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-semibold mb-2">
                    ユーザー名 <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="input-field <?php echo isset($errors['username']) ? 'border-red-500' : ''; ?>"
                    value="<?php echo e($formData['username'] ?? ''); ?>"
                    required
                >
                <?php if (isset($errors['username'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['username']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">
                    メールアドレス <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="input-field <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                    value="<?php echo e($formData['email'] ?? ''); ?>"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['email']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">
                    パスワード <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="input-field <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>"
                    required
                    minlength="8"
                >
                <?php if (isset($errors['password'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['password']); ?></p>
                <?php else: ?>
                <p class="text-gray-500 text-sm mt-1">8文字以上で入力してください</p>
                <?php endif; ?>
            </div>
            
            <!-- Password Confirmation -->
            <div class="mb-6">
                <label for="password_confirm" class="block text-gray-700 font-semibold mb-2">
                    パスワード（確認） <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    class="input-field <?php echo isset($errors['password_confirm']) ? 'border-red-500' : ''; ?>"
                    required
                >
                <?php if (isset($errors['password_confirm'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['password_confirm']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Team Selection -->
            <div class="mb-4 p-4 bg-gray-50 rounded">
                <h3 class="font-semibold mb-3" style="color: #1976d2;">チーム設定</h3>
                
                <div class="mb-4">
                    <label class="flex items-center mb-2">
                        <input 
                            type="radio" 
                            name="team_action" 
                            value="join" 
                            <?php echo (!isset($formData['team_action']) || $formData['team_action'] === 'join') ? 'checked' : ''; ?>
                            onchange="toggleTeamFields()"
                            class="mr-2"
                        >
                        <span class="font-medium">既存のチームに参加</span>
                    </label>
                    <label class="flex items-center">
                        <input 
                            type="radio" 
                            name="team_action" 
                            value="create" 
                            <?php echo (isset($formData['team_action']) && $formData['team_action'] === 'create') ? 'checked' : ''; ?>
                            onchange="toggleTeamFields()"
                            class="mr-2"
                        >
                        <span class="font-medium">新しいチームを作成</span>
                    </label>
                </div>
                
                <!-- Team Code -->
                <div class="mb-3">
                    <label for="team_code" class="block text-gray-700 font-semibold mb-2">
                        チームコード <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="team_code" 
                        name="team_code" 
                        class="input-field <?php echo isset($errors['team_code']) ? 'border-red-500' : ''; ?>"
                        value="<?php echo e($formData['team_code'] ?? ''); ?>"
                        placeholder="例: TEAM2024"
                        style="text-transform: uppercase;"
                        required
                        maxlength="20"
                    >
                    <?php if (isset($errors['team_code'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($errors['team_code']); ?></p>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm mt-1" id="teamCodeHelp">
                        参加するチームのコードを入力してください
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Team Name (only for new teams) -->
                <div id="teamNameField" style="display: none;">
                    <label for="team_name" class="block text-gray-700 font-semibold mb-2">
                        チーム名 <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="team_name" 
                        name="team_name" 
                        class="input-field <?php echo isset($errors['team_name']) ? 'border-red-500' : ''; ?>"
                        value="<?php echo e($formData['team_name'] ?? ''); ?>"
                        placeholder="例: 開発チームA"
                    >
                    <?php if (isset($errors['team_name'])): ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($errors['team_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn-primary w-full">
                登録する
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                既にアカウントをお持ちですか？
                <a href="/login.php" style="color: #1976d2;" class="hover:underline">
                    ログイン
                </a>
            </p>
        </div>
    </div>
</div>

<script>
function toggleTeamFields() {
    const action = document.querySelector('input[name="team_action"]:checked').value;
    const teamNameField = document.getElementById('teamNameField');
    const teamCodeHelp = document.getElementById('teamCodeHelp');
    const teamNameInput = document.getElementById('team_name');
    
    if (action === 'create') {
        teamNameField.style.display = 'block';
        teamNameInput.required = true;
        teamCodeHelp.textContent = '新しいチームのコードを決めてください（3〜20文字の英数字）';
    } else {
        teamNameField.style.display = 'none';
        teamNameInput.required = false;
        teamCodeHelp.textContent = '参加するチームのコードを入力してください';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleTeamFields);

// Auto-uppercase team code
document.getElementById('team_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<script src="/assets/js/form-validation.js"></script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
