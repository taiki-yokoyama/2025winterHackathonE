<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PDCA Spiral'; ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
    <!-- Chart.js for progress visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php
    require_once __DIR__ . '/../../utils/session.php';
    initSession();
    $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
    $username = $_SESSION['username'] ?? '';
    ?>
    
    <!-- Navigation - Clean & Simple -->
    <nav style="background: #1976d2; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="<?php echo $isLoggedIn ? '/dashboard.php' : '/'; ?>" class="flex items-center">
                        <span class="text-xl font-bold text-white" style="letter-spacing: 0.5px;">
                            PDCA Spiral
                        </span>
                    </a>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <div class="flex items-center">
                    <a href="/dashboard.php" class="nav-link text-white font-medium text-sm mr-10" style="letter-spacing: 0.08em; padding: 8px 12px; border-radius: 6px; transition: background 0.2s;">
                        ホーム
                    </a>
                    <a href="/weekly-review/create.php" class="nav-link text-white font-medium text-sm mr-10" style="letter-spacing: 0.08em; padding: 8px 12px; border-radius: 6px; transition: background 0.2s;">
                        週次レビュー
                    </a>
                    <a href="/evaluation/list.php" class="nav-link text-white font-medium text-sm mr-10" style="letter-spacing: 0.08em; padding: 8px 12px; border-radius: 6px; transition: background 0.2s;">
                        履歴
                    </a>
                    <div class="flex items-center mr-6" style="padding: 4px 14px 4px 4px; background: rgba(255,255,255,0.1); border-radius: 20px; border: 1px solid rgba(255,255,255,0.15);">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; color: white; font-size: 0.875rem;">
                            <?php echo mb_substr($username, 0, 1); ?>
                        </div>
                        <span class="text-white text-sm font-medium" style="letter-spacing: 0.03em;">
                            <?php echo e($username); ?>
                        </span>
                    </div>
                    <a href="/logout.php" class="logout-btn text-white text-sm font-medium" style="letter-spacing: 0.03em; padding: 8px 18px; background: rgba(255,255,255,0.12); border-radius: 6px; transition: background 0.2s; border: 1px solid rgba(255,255,255,0.2);">
                        ログアウト
                    </a>
                </div>
                <?php else: ?>
                <div class="flex items-center space-x-4">
                    <a href="/login.php" class="text-white font-medium text-sm" style="padding: 8px 16px; border-radius: 6px; transition: background 0.2s;">
                        ログイン
                    </a>
                    <a href="/register.php" class="text-white font-medium text-sm" style="padding: 8px 16px; border-radius: 6px; transition: background 0.2s;">
                        新規登録
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <style>
        .nav-link:hover {
            background: rgba(255,255,255,0.12);
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2) !important;
        }
        
        a[href="/login.php"]:hover,
        a[href="/register.php"]:hover {
            background: rgba(255,255,255,0.12);
        }
    </style>
    
    <!-- Flash Messages - Simple Style -->
    <?php
    $flashMessage = getFlashMessage();
    if ($flashMessage):
        $bgColor = $flashMessage['type'] === 'success' ? 'bg-green-100' : 'bg-red-100';
        $textColor = $flashMessage['type'] === 'success' ? 'text-green-800' : 'text-red-800';
        $borderColor = $flashMessage['type'] === 'success' ? '#4caf50' : '#f44336';
    ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="<?php echo $bgColor; ?> <?php echo $textColor; ?> p-3 animate-fade-in" role="alert" style="border-left: 4px solid <?php echo $borderColor; ?>; border-radius: 4px;">
            <span style="font-weight: 600;"><?php echo e($flashMessage['message']); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
