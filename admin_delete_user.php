<?php

session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: admin_users.php?m=' . urlencode('Invalid CSRF token.'));
    exit;
}

$user_id = (int) ($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    header('Location: admin_users.php?m=' . urlencode('Invalid user id.'));
    exit;
}

if ($user_id === (int)$_SESSION['user']['id']) {
    header('Location: admin_users.php?m=' . urlencode('You cannot delete your own admin account.'));
    exit;
}

// fetch target user
$stmt = $conn->prepare("SELECT id, user_type FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$target = $res->fetch_assoc();
$stmt->close();

if (!$target) {
    header('Location: admin_users.php?m=' . urlencode('User not found.'));
    exit;
}

$type = $target['user_type'];

// If handler, ensure no assigned complaints
if ($type === 'handler') {
    $q = $conn->prepare("SELECT COUNT(*) AS n FROM complaints WHERE handler_id = ?");
    $q->bind_param('i', $user_id);
    $q->execute();
    $nres = $q->get_result()->fetch_assoc();
    $q->close();

    if ((int)$nres['n'] > 0) {
        header('Location: admin_users.php?m=' . urlencode('Cannot delete handler with assigned complaints. Reassign or close them first.'));
        exit;
    }
}

// If complainer, delete their complaints first (or rely on FK CASCADE if you set it)
if ($type === 'complainer') {
    $delc = $conn->prepare("DELETE FROM complaints WHERE user_id = ?");
    $delc->bind_param('i', $user_id);
    $delc->execute();
    $delc->close();
}

// Finally delete the user
$delu = $conn->prepare("DELETE FROM users WHERE id = ? LIMIT 1");
$delu->bind_param('i', $user_id);
$ok = $delu->execute();
$delu->close();

if ($ok) {
    header('Location: admin_users.php?m=' . urlencode('User deleted successfully.'));
} else {
    header('Location: admin_users.php?m=' . urlencode('Failed to delete user.'));
}
exit;
