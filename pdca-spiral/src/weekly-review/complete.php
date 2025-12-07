<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$pageTitle = '週次レビュー完了 - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="card text-center py-12">
        <!-- Success Animation -->
        <div class="mb-8 animate-bounce">
            <svg class="mx-auto" width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="60" cy="60" r="55" fill="url(#successGradient)" opacity="0.2"/>
                <circle cx="60" cy="60" r="45" fill="url(#successGradient)"/>
                <path d="M40 60 L52 72 L80 44" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="successGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#22c55e;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#16a34a;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        
        <!-- Success Message -->
        <h1 class="text-4xl font-bold mb-4" style="color: #22c55e;">
            1週間お疲れ様でした！
        </h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            週次レビューが完了しました
        </h2>
        <p class="text-gray-600 mb-8 text-lg">
            振り返りとアクション設定、素晴らしいです！<br>
            継続的な改善がチームの成長につながります。
        </p>
        
        <!-- Motivational Message -->
        <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-6 mb-8 max-w-xl mx-auto">
            <p class="text-gray-700 font-medium mb-2">
                 PDCAサイクルを回し続けることで
            </p>
            <p class="text-gray-600">
                チームは着実に成長していきます。<br>
                次週も一緒に頑張りましょう！
            </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-8">
            <a href="/dashboard.php" class="btn-primary px-8 py-3 text-lg mr-2">
                ホームへ
            </a>
            <a href="/next-action/list.php" class="btn-secondary px-8 py-3 text-lg ml-2">
                アクション一覧を見る
            </a>
        </div>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.animate-bounce {
    animation: bounce 1s ease-in-out 2;
}
</style>



<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
