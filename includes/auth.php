<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function getCurrentAdmin(): ?array {
    return $_SESSION['admin'] ?? null;
}

function setAdminSession(array $admin): void {
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email']    = $admin['email'] ?? '';
    $_SESSION['admin_role']     = $admin['role'] ?? 'Admin';
    $_SESSION['admin']          = [
        'id'       => $admin['id'],
        'username' => $admin['username'],
        'email'    => $admin['email'] ?? '',
        'role'     => $admin['role'] ?? 'Admin',
    ];
    session_regenerate_id(true);
}

function destroySession(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
