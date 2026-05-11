<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function refreshSessionUser(PDO $pdo): void
{
    if (!isLoggedIn()) {
        return;
    }

    $userId = (int)($_SESSION['user']['id'] ?? 0);

    if ($userId <= 0) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id, username, email, role, is_banned, ban_reason
         FROM users
         WHERE id = ?'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'is_banned' => $user['is_banned'],
        'ban_reason' => $user['ban_reason']
    ];
}

function logoutBannedUserIfNeeded(PDO $pdo, string $redirect = 'login.php'): void
{
    if (!isLoggedIn()) {
        return;
    }

    refreshSessionUser($pdo);

    if (isAdmin()) {
        return;
    }

    if ((int)($_SESSION['user']['is_banned'] ?? 0) === 1) {
        $banReason = trim($_SESSION['user']['ban_reason'] ?? '');

        if ($banReason === '') {
            $banReason = 'Aucune raison précisée.';
        }

        $_SESSION = [];
        session_destroy();

        session_start();

        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Ton compte a été suspendu. Raison : ' . $banReason
        ];

        header('Location: ' . $redirect);
        exit;
    }
}

function requireLogin(): void
{
    global $pdo;

    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if (isset($pdo)) {
        logoutBannedUserIfNeeded($pdo, 'login.php');
    }
}

function requireAdmin(): void
{
    global $pdo;

    if (isset($pdo)) {
        refreshSessionUser($pdo);
    }

    if (!isAdmin()) {
        header('Location: ../login.php');
        exit;
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}