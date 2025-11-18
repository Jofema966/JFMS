<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireRole(string $role)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        if ($role === 'admin') {
            header("Location: /admin/login.php");
        } else {
            header("Location: /socio/login.php");
        }
        exit;
    }
}

function requireAdmin()
{
    requireRole('admin');
}
