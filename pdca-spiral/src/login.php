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

// Check for session expired message
if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
    $errors['general'] = 'セッションの有効期限が切れました。再度ログインしてください。';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::login($_POST);
    
    if ($result['success']) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $errors = $result['errors'];
        $formData = $_POST;
    }
}

$pageTitle = 'ログイン - PDCA Spiral';
require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="card">
        <h1 class="text-3xl font-bold text-center mb-6 spiral-gradient bg-clip-text text-transparent">
            ログイン
        </h1>
        
        <?php if (isset($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo e($errors['general']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/login.php">
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
                    autofocus
                >
                <?php if (isset($errors['username'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['username']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2">
                    パスワード <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="input-field <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($errors['password']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn-primary w-full">
                ログイン
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                アカウントをお持ちでないですか？
                <a href="/register.php" class="text-spiral-blue hover:underline">
                    新規登録
                </a>
            </p>
        </div>
        
        <!-- Demo Account Info -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-700 font-semibold mb-2">デモアカウント:</p>
            <p class="text-sm text-gray-600">ユーザー名: demo</p>
            <p class="text-sm text-gray-600">パスワード: password</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
