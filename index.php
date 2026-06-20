<?php
// index.php — project root entry point
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/Auth.php';

Auth::startSession();

// If already logged in, go straight to dashboard
// Otherwise send to login
if (Auth::check()) {
    header('Location: ' . BASE_URL . '/modules/ict/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
}
exit;