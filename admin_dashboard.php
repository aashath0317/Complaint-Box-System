<?php
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

$adminName = htmlspecialchars($_SESSION['user']['name'] ?? 'Admin');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Dashboard | DCBS</title>
<style>
  :root{--logo:#ffbd59;--muted:#4b5563;--black:#000;--white:#fff}
  *{box-sizing:border-box}
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0;color:#111}
  .top{background:var(--black);color:#fff;padding:12px 18px;display:flex;align-items:center;justify-content:space-between}
  .top a{color:#fff;text-decoration:none;margin-left:14px}
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
  h1{margin:0 0 8px}
  .muted{color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.06);padding:18px}
  .card h3{margin:0 0 6px}
  .btn{display:inline-block;background:var(--logo);color:#000;text-decoration:none;padding:10px 14px;border-radius:10px;font-weight:700}
  .btn-outline{display:inline-block;border:1px solid #e5e7eb;background:#fff;color:#111;text-decoration:none;padding:10px 14px;border-radius:10px;font-weight:700}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
</style>
</head>
<body>

<header class="top">
  <div><strong>Admin Dashboard</strong></div>
  <nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_users.php">Users</a>     <!-- Topbar link to Manage Users -->
    <a href="create_handler.php">Create Handler</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="wrap">
  <h1>Welcome, <?= $adminName ?></h1>
  <div class="muted">Quick actions and management tools for administrators.</div>

  <div class="grid">
    <section class="card">
      <h3>Manage Users</h3>
      <p class="muted">Search, filter, and delete users. Deleting a complainer also removes their complaints.</p>
      <div class="actions">
        <a class="btn" href="admin_users.php">Open Users</a>   <!-- The button you asked for -->
        <a class="btn-outline" href="create_handler.php">Create Handler</a>
      </div>
    </section>

    <section class="card">
      <h3>Create a Handler</h3>
      <p class="muted">Add a new handler account who can be assigned complaints.</p>
      <a class="btn" href="create_handler.php">Create Handler</a>
    </section>

    <section class="card">
      <h3>Help</h3>
      <p class="muted">Need instructions or to adjust roles/policies?</p>
      <a class="btn-outline" href="help.php">Open Help</a>
    </section>
  </div>
</div>

</body>
</html>
