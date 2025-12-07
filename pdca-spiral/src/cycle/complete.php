<?php
require_once __DIR__ . '/../services/PDCACycleService.php';
require_once __DIR__ . '/../utils/session.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $cycleId = (int)($_POST['cycle_id'] ?? 0);
        
        if (PDCACycleService::completeCycle($cycleId)) {
            header('Location: /cycle/complete-success.php');
            exit;
        } else {
            setFlashMessage('error', 'サイクルの完了に失敗しました。');
            header('Location: /dashboard.php');
            exit;
        }
    }
}

header('Location: /dashboard.php');
exit;
