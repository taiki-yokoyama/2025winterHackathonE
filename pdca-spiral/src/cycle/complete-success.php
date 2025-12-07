<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/validation.php';

requireAuth();

$pageTitle = '週完了 - PDCA Spiral';
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="card text-center py-12">
        <!-- Success Animation -->
        <div class="mb-8 animate-bounce">
            <svg class="mx-auto" width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Outer glow circle -->
                <circle cx="60" cy="60" r="55" fill="url(#cycleGradient)" opacity="0.2"/>
                <!-- Main circle -->
                <circle cx="60" cy="60" r="45" fill="url(#cycleGradient)"/>
                <!-- Checkmark -->
                <path d="M40 60 L52 72 L80 44" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- PDCA Spiral decoration -->
                <circle cx="60" cy="60" r="35" stroke="white" stroke-width="2" fill="none" opacity="0.3" stroke-dasharray="5,5"/>
                <defs>
                    <linearGradient id="cycleGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#1976d2;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#42a5f5;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        
        <!-- Success Message -->
        <h1 class="text-4xl font-bold mb-4" style="color: #1976d2;">
            素晴らしい！
        </h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            1週間のサイクルが完了しました
        </h2>
        <p class="text-gray-600 mb-8 text-lg">
            チームで振り返り、改善を重ねることで<br>
            着実に成長しています。お疲れ様でした！
        </p>
        
        <!-- Motivational Message -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-8 max-w-xl mx-auto">
            <p class="text-gray-700 font-medium mb-2">
                PDCAサイクルは継続が力
            </p>
            <p class="text-gray-600">
                Plan（計画）→ Do（実行）→ Check（評価）→ Act（改善）<br>
                このサイクルを回し続けることで、チームは確実に進化します。
            </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mt-8">
            <a href="/dashboard.php" class="btn-primary px-8 py-3 text-lg">
                ホームへ
            </a>
            <a href="/evaluation/list.php" class="btn-secondary px-8 py-3 text-lg">
                評価履歴を見る
            </a>
        </div>
        
        <!-- Next Steps -->
        <div class="mt-8 text-sm text-gray-500">
            <p>次週も引き続き、週次レビューでチームの改善を続けましょう</p>
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
