<?php
session_start();

$_SESSION = [];

// Delete the session cookie if set
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Choose where to send users after logout
$redirect = 'login.php'; 

if (!headers_sent()) {
    header("Location: {$redirect}");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($redirect) ?>">
  <title>Logging out…</title>
</head>
<body>
  <p>Logging out… If you are not redirected, <a href="<?= htmlspecialchars($redirect) ?>">click here</a>.</p>
  <script>location.replace('<?= addslashes($redirect) ?>');</script>
</body>
</html>
