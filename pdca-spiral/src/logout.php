<?php
require_once __DIR__ . '/controllers/AuthController.php';

AuthController::logout();
header('Location: /login.php');
exit;
