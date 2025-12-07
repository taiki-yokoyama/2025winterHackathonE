<?php
require_once __DIR__ . '/utils/session.php';

initSession();

// Redirect to dashboard if logged in, otherwise to login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;
