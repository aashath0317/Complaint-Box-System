<?php
// handler_profile.php
session_start();
require_once __DIR__ . '/config.php';

// protect page: must be logged in and user_type = handler
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'handler') {
    header('Location: login.php');
    exit;
}

$handler_id = (int)$_SESSION['user']['id'];
$message = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ? AND user_type = 'handler' LIMIT 1");
$stmt->bind_param('i', $handler_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) { die('User not found.'); }

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if ($name === '' || $email === '') {
        $message = '<p class="msg msg-error">Name and Email cannot be empty.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p class="msg msg-error">Invalid email format.</p>';
    } elseif ($password !== '' && $password !== $password_confirm) {
        $message = '<p class="msg msg-error">Passwords do not match.</p>';
    } else {
        if ($password !== '') {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ? AND user_type = 'handler'");
            $stmt->bind_param('sssi', $name, $email, $hashed_password, $handler_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND user_type = 'handler'");
            $stmt->bind_param('ssi', $name, $email, $handler_id);
        }

        if ($stmt->execute()) {
            $message = '<p class="msg msg-success">Profile updated successfully.</p>';
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            $message = '<p class="msg msg-error">Failed to update profile.</p>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Handler Profile | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Handler</h3>
    <a href="handler_dashboard.php">Dashboard</a>
    <a href="handler_my_complaints.php">My Complaints</a>
    <a href="handler_profile.php" class="active">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content" style="max-width:600px">
    <h1 class="title-accent">My Profile</h1>

    <div class="message"><?= $message ?></div>

    <form method="post" class="card form">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['name']) ?>" />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>" />

      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" placeholder="Enter new password" />

      <label for="password_confirm">Confirm New Password</label>
      <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm new password" />

      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
  </section>
</main>

</body>
</html>
