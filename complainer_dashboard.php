<?php
// complainer_dashboard.php
session_start();

// protect page: must be logged in and user_type = complainer
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'complainer') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php'; // provides $conn (mysqli)

// get logged in user id
$user_id = (int) $_SESSION['user']['id'];

// --- fetch counts ---
$counts = ['Solved' => 0, 'In Progress' => 0, 'Pending' => 0];
$stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM complaints WHERE user_id = ? GROUP BY status");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $counts[$row['status']] = (int) $row['total'];
}
$stmt->close();

// --- fetch recent complaints ---
$recent = [];
$stmt2 = $conn->prepare("SELECT id, title, handler_id, created_at, status FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
$stmt2->bind_param('i', $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) {
    $recent[] = $r;
}
$stmt2->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Complainer Dashboard | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <h3>Complainer</h3>
    <a href="complainer_dashboard.php" class="active">Dashboard</a>
    <a href="submit_complaint.php">Add Complaint</a>
    <a href="my_complaints.php">My Complaints</a>
    <a href="account.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <!-- Main content -->
  <section class="content">
    <div class="header-row">
      <div>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Complainer') ?></h1>
        <p class="muted">Here is a quick view of your complaints.</p>
      </div>
      <div>
        <a href="submit_complaint.php" class="btn btn-primary">Submit New Complaint</a>
      </div>
    </div>

    <!-- Status cards -->
    <div class="cards">
      <div class="card card--green">
        <h4>✅ Solved</h4>
        <div class="num"><?= (int) $counts['Solved'] ?></div>
      </div>
      <div class="card card--orange">
        <h4>🔄 In Progress</h4>
        <div class="num"><?= (int) $counts['In Progress'] ?></div>
      </div>
      <div class="card card--red">
        <h4>⏳ Pending</h4>
        <div class="num"><?= (int) $counts['Pending'] ?></div>
      </div>
    </div>

    <!-- Recent complaints table -->
    <div class="table-wrap card">
      <h3 class="mt-0">Recent Complaints</h3>
      <?php if (count($recent) === 0): ?>
        <div class="empty">You have not submitted any complaints yet. <a href="submit_complaint.php">Submit your first complaint</a>.</div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $row): 
              $id = (int) $row['id'];
              $title = htmlspecialchars($row['title']);
              $date = htmlspecialchars(substr($row['created_at'],0,19));
              $status = htmlspecialchars($row['status']);
              $status_class = str_replace(' ', '\ ', $status); // keeps existing class logic
            ?>
            <tr>
              <td><?= $id ?></td>
              <td><a href="view_complaint.php?id=<?= $id ?>"><?= $title ?></a></td>
              <td><?= $date ?></td>
              <td><span class="badge <?= $status_class ?>"><?= $status ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </section>
</main>

</body>
</html>
