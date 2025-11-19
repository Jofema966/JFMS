<?php
// session_helpers.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectToLoginInCurrentDir(): void {
    header('Location: login.php');
    exit;
}

function requireAdmin(): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirectToLoginInCurrentDir(); // admin/login.php
    }
}

function requireSocio(): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'socio') {
        redirectToLoginInCurrentDir(); // socio/login.php
    }
}
