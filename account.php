<?php
session_start();
require_once __DIR__ . '/config.php';

// Only allow logged-in complainers
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'complainer') {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user']['id'];

// Fetch current user info
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if ($name === '' || $email === '') {
        $message = '<p style="color:red;">Name and Email are required.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color:red;">Invalid email format.</p>';
    } elseif ($password !== $password_confirm) {
        $message = '<p style="color:red;">Passwords do not match.</p>';
    } else {
        // Update user info
        if ($password !== '') {
            // Update with password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        } else {
            // Update without password
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $message = '<p style="color:green;">Account updated successfully.</p>';
            // Update session user info
            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['email'] = $email;
            // Refresh user data variable for form
            $user['name']  = $name;
            $user['email'] = $email;
        } else {
            $message = '<p style="color:red;">Error updating account.</p>';
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
<title>My Account | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Complainer</h3>
    <a href="complainer_dashboard.php">Dashboard</a>
    <a href="submit_complaint.php">Add Complaint</a>
    <a href="my_complaints.php">My Complaints</a>
    <a href="account.php" class="active">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content">
    <h1>My Account</h1>
    <div class="message"><?= $message ?></div>

    <form method="post" novalidate class="form card">
      <label for="name">Name *</label>
      <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['name']) ?>">

      <label for="email">Email *</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

      <label for="password">New Password (leave blank to keep current)</label>
      <input type="password" id="password" name="password" autocomplete="new-password">

      <label for="password_confirm">Confirm New Password</label>
      <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">

      <button type="submit" class="btn btn-primary">Update Account</button>
    </form>
  </section>
</main>

</body>
</html>
