<?php
session_start();
require_once __DIR__ . '/config.php'; // defines $conn (mysqli)

$ADMIN_EMAIL = 'dbmanager@email.com';
$ADMIN_PASSWORD_PLAIN = 'dbmanager123@';
$ADMIN_NAME = 'DB Manager';

// 1) Ensure admin exists (promote if present, else create).
$ADMIN_PASSWORD_HASH = password_hash($ADMIN_PASSWORD_PLAIN, PASSWORD_DEFAULT);

// Try to promote existing row with this email (any user_type) to admin and reset password.
$upd = $conn->prepare("UPDATE users SET name = ?, user_type = 'admin', password = ? WHERE email = ? LIMIT 1");
$upd->bind_param('sss', $ADMIN_NAME, $ADMIN_PASSWORD_HASH, $ADMIN_EMAIL);
$upd->execute();
$promoted = $upd->affected_rows > 0;
$upd->close();

// If not found, insert a new admin row.
if (!$promoted) {
    $ins = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, 'admin')");
    $ins->bind_param('sss', $ADMIN_NAME, $ADMIN_EMAIL, $ADMIN_PASSWORD_HASH);
    // If this insert fails because email is already there (race or non-unique duplicates), we ignore;
    // the UPDATE above should have handled the main case.
    @$ins->execute();
    $ins->close();
}

$err = '';

// 2) Handle login when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $err = 'Please enter email and password.';
    } else {
        // Fetch by exact email
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $r = $stmt->get_result();

        if ($r && $r->num_rows === 1) {
            $user = $r->fetch_assoc();

            if ($user['user_type'] !== 'admin') {
                $err = 'This account is not an admin account.';
            } elseif (!password_verify($password, $user['password'])) {
                $err = 'Incorrect password.';
            } else {
                $_SESSION['user'] = [
                    'id'        => (int)$user['id'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'user_type' => 'admin',
                ];
                header('Location: admin_dashboard.php');
                exit;
            }
        } else {
            $err = 'Admin account not found.';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | DCBS</title>
<style>
  :root{--logo:#ffbd59;--muted:#4b5563}
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0;padding:40px}
  .card{max-width:520px;margin:40px auto;background:#fff;padding:24px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
  h1{margin:0 0 12px;color:#111}
  label{display:block;margin-top:12px;font-weight:600}
  input[type=email],input[type=password]{width:100%;padding:10px;border-radius:8px;border:1px solid #e5e7eb;margin-top:6px}
  .btn{background:var(--logo);border:none;color:#000;padding:10px 14px;border-radius:8px;font-weight:700;margin-top:14px;cursor:pointer}
  .err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:12px}
  .note{font-size:13px;color:var(--muted);margin-top:12px}
</style>
</head>
<body>

<div class="card">
  <h1>Admin Login</h1>

  <?php if ($err): ?>
    <div class="err"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post" action="" autocomplete="off">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="off" placeholder="admin email">

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="password">

    <button class="btn" type="submit">Login as Admin</button>
  </form>

  <p class="note">
    Admin bootstrap uses email <strong><?= htmlspecialchars($ADMIN_EMAIL) ?></strong>.<br>
    If a user already existed with that email, they were promoted to <strong>admin</strong> and the password set to the specified one.
  </p>
</div>

</body>
</html>
