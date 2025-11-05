<?php
// create_handler.php
session_start();
require_once __DIR__ . '/config.php';


if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

$message = '';
$errors  = [];

// Helper: safe trim
function v($arr, $key) { return trim($arr[$key] ?? ''); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = v($_POST, 'name');
    $email    = v($_POST, 'email');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($password === '' || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        // 1) Check for existing email first (prevents duplicate insert)
        $chk = $conn->prepare("SELECT id, user_type FROM users WHERE email = ? LIMIT 1");
        $chk->bind_param('s', $email);
        $chk->execute();
        $res = $chk->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $errors[] = "This email already belongs to a {$row['user_type']} account.";
        }
        $chk->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 2) Try inserting; if the unique constraint on email still catches a race, handle errno 1062
        $ins = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, 'handler')");
        $ins->bind_param('sss', $name, $email, $hash);

        try {
            if ($ins->execute()) {
                $message = 'Handler created successfully.';
            } else {
                // Non-exception path (in case exceptions are disabled)
                if ($ins->errno === 1062) {
                    $errors[] = 'Email already exists. Choose a different email.';
                } else {
                    $errors[] = 'Failed to create handler: ' . htmlspecialchars($ins->error);
                }
            }
        } catch (mysqli_sql_exception $e) {
            // Exception path (when mysqli is set to throw)
            if ($e->getCode() === 1062) {
                $errors[] = 'Email already exists. Choose a different email.';
            } else {
                $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        } finally {
            $ins->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Create Handler | Admin</title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0}
  .wrap{max-width:640px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
  h1{margin:0 0 10px}
  label{display:block;margin-top:12px;font-weight:600}
  input[type=text],input[type=email],input[type=password]{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;margin-top:6px;font-size:14px}
  .btn{margin-top:14px;background:#ffbd59;color:#000;padding:10px 14px;border:none;border-radius:8px;font-weight:700;cursor:pointer}
  .msg{background:#ecfdf5;color:#065f46;padding:10px;border-radius:6px;margin-bottom:10px}
  .err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:10px}
  .back{display:inline-block;margin-top:12px}
</style>
</head>
<body>
<div class="wrap">
  <h1>Create Handler</h1>

  <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="err">
      <ul style="margin:0;padding-left:18px;">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" required>

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required>

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>

    <button class="btn" type="submit">Create Handler</button>
  </form>

  <a class="back" href="admin_dashboard.php">&larr; Back to Admin Dashboard</a>
</div>
</body>
</html>
