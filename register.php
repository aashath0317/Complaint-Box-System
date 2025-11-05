<?php
session_start();
$active = 'register';

// DB connection
require_once __DIR__ . '/config.php'; // must define $conn (mysqli)

// initialization
$errors = [];
$name = '';
$email = '';

// handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $user_type = 'complainer'; // registration for complainers only

    // basic server-side validation
    if ($name === '') $errors[] = "Name is required.";
    if ($email === '') $errors[] = "Email is required.";
    if ($password === '') $errors[] = "Password is required.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    if (empty($errors)) {
        // check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $errors[] = "An account with this email already exists. Try logging in.";
        } else {
            // insert user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
            $ins->bind_param('ssss', $name, $email, $hash, $user_type);
            if ($ins->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = "Registration failed. Please try again. (" . htmlspecialchars($ins->error) . ")";
            }
            $ins->close();
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
  <title>Register | DCBS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css?v=1">
  <script>
    // client-side password match check
    function validateForm(e) {
      const pw = document.getElementById('password').value;
      const cpw = document.getElementById('confirm_password').value;
      if (pw.length < 6) {
        alert('Password must be at least 6 characters.');
        e.preventDefault();
        return false;
      }
      if (pw !== cpw) {
        alert('Passwords do not match.');
        e.preventDefault();
        return false;
      }
      return true;
    }
    document.addEventListener('DOMContentLoaded', function(){
      const form = document.getElementById('regForm');
      if (form) form.addEventListener('submit', validateForm);
    });
  </script>
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="auth-main">
  <div class="card auth-card reg-card" role="region" aria-label="Registration card">
    <div class="left">
      <h1>Create your account</h1>
      <p class="lead">Register as a complainer to submit complaints and view responses.</p>

      <?php if (!empty($errors)): ?>
        <div class="error" role="alert" style="margin-bottom:12px">
          <strong>Please fix the following:</strong>
          <ul style="margin:8px 0 0 18px">
            <?php foreach($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form id="regForm" method="post" action="register.php" novalidate>
        <label for="name">Full name</label>
        <input id="name" name="name" type="text" value="<?= htmlspecialchars($name) ?>" required>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required placeholder="Min 6 characters">

        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" required>

        <button class="btn btn-primary btn-submit" type="submit">Register</button>

        <div class="auth-links">
          Already have an account? <a href="login.php">Login</a>
        </div>
      </form>
    </div>
  </div>
</main>

</body>
</html>
