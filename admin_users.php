<?php
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Create CSRF token for deletes
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

// Filters
$q      = trim($_GET['q'] ?? '');
$role   = trim($_GET['role'] ?? '');
$params = [];
$where  = [];

if ($q !== '') {
    $where[] = "(name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%'))";
    $params[] = $q; $params[] = $q;
}
if ($role !== '' && in_array($role, ['complainer','handler','admin'], true)) {
    $where[] = "user_type = ?";
    $params[] = $role;
}

$sql = "SELECT id, name, email, user_type, created_at FROM users";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY user_type ASC, name ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    // dynamic types string: all strings
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

$flash = $_GET['m'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Manage Users | Admin</title>
<style>
  :root{--logo:#ffbd59;--muted:#4b5563;--black:#000;--white:#fff}
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0}
  .top{background:#000;color:#fff;padding:12px 18px;display:flex;align-items:center;justify-content:space-between}
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
  h1{margin:0 0 10px}
  .card{background:#fff;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.06);padding:16px}
  .row{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:12px}
  label{display:block;font-weight:600;margin-bottom:6px}
  input[type=text],select{padding:10px;border:1px solid #e5e7eb;border-radius:8px;min-width:220px}
  .btn{background:var(--logo);border:none;color:#000;padding:10px 14px;border-radius:8px;font-weight:700;cursor:pointer}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px}
  th{background:#fafafa}
  .badge{display:inline-block;padding:4px 8px;border-radius:6px;font-weight:700;font-size:12px}
  .role-admin{background:#111;color:#fff}
  .role-handler{background:#e6f4ff;color:#0b5ea8}
  .role-complainer{background:#fff0e6;color:#7a3b00}
  .danger{background:#ef4444;color:#fff}
  .muted{color:#6b7280}
  .flash{background:#ecfdf5;color:#065f46;padding:10px;border-radius:8px;margin-bottom:10px}
  .warn{background:#fff7ed;color:#7a2e0e;padding:10px;border-radius:8px;margin-bottom:10px}
  .top a{color:#fff;text-decoration:none;margin-left:12px}
</style>
</head>
<body>

<header class="top">
  <div><strong>Admin</strong> — Manage Users</div>
  <nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_users.php">Users</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="wrap">
  <h1>Users</h1>

  <div class="card">
    <?php if ($flash): ?>
      <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php else: ?>
      <div class="warn">Deleting a <strong>complainer</strong> also deletes all complaints they created. Deleting a <strong>handler</strong> is blocked if they still have assigned complaints.</div>
    <?php endif; ?>

    <form class="row" method="get" action="">
      <div>
        <label for="q">Search</label>
        <input id="q" name="q" type="text" value="<?= htmlspecialchars($q) ?>" placeholder="name or email">
      </div>
      <div>
        <label for="role">Role</label>
        <select id="role" name="role">
          <option value="">Any</option>
          <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
          <option value="handler" <?= $role==='handler'?'selected':'' ?>>Handler</option>
          <option value="complainer" <?= $role==='complainer'?'selected':'' ?>>Complainer</option>
        </select>
      </div>
      <div>
        <button class="btn" type="submit">Filter</button>
      </div>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name / Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="5" class="muted">No users found.</td></tr>
        <?php else: foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td>
              <div style="font-weight:700"><?= htmlspecialchars($u['name'] ?: '—') ?></div>
              <div class="muted"><?= htmlspecialchars($u['email']) ?></div>
            </td>
            <td>
              <?php
                $cls = $u['user_type']==='admin' ? 'role-admin' : ($u['user_type']==='handler'?'role-handler':'role-complainer');
              ?>
              <span class="badge <?= $cls ?>"><?= htmlspecialchars($u['user_type']) ?></span>
            </td>
            <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
            <td>
              <?php if ((int)$u['id'] === (int)($_SESSION['user']['id'])): ?>
                <span class="muted">You</span>
              <?php else: ?>
                <form method="post" action="admin_delete_user.php" onsubmit="return confirm('Delete this user<?= $u['user_type']==='complainer' ? ' and all their complaints' : '' ?>?');" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button class="btn danger" type="submit">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
