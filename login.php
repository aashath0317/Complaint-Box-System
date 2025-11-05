<?php
session_start();
$active = 'login';
require_once __DIR__ . '/config.php'; // provides $conn

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'complainer';

    if ($email === '' || $password === '') {
        $err = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ? AND user_type = ? LIMIT 1");
        $stmt->bind_param('ss', $email, $user_type);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type']
                ];
                if ($user['user_type'] === 'handler') {
                    header('Location: handler_dashboard.php');
                    exit;
                } else {
                    header('Location: complainer_dashboard.php');
                    exit;
                }
            } else {
                $err = "Incorrect password.";
            }
        } else {
            $err = "No user found with that email and user type.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login | DCBS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="auth-main">
  <div class="card auth-card" role="region" aria-label="Login card">
    <div class="left">
      <h1>Welcome Back</h1>
      <p class="lead">Login to your account and manage complaints.</p>

      <?php if($err !== ''): ?>
        <div class="error"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <form method="post" id="loginForm" novalidate>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required>

        <label>Login as</label>
        <div class="radio-row">
          <label class="radio"><input type="radio" name="user_type" value="complainer" checked> Complainer</label>
          <label class="radio"><input type="radio" name="user_type" value="handler"> Handler</label>
        </div>

        <button class="btn btn-primary btn-submit" type="submit">Login</button>

        <div class="auth-links">
          <!-- Forgot password intentionally hidden for local-only run -->
          <a href="register.php">Create account</a>
        </div>
      </form>
    </div>
  </div>
</main>

</body>
</html>
