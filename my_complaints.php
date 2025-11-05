<?php
// my_complaints.php
session_start();
require_once __DIR__ . '/config.php';

$user_id = (int) $_SESSION['user']['id'];

// Fetch complaints submitted by this complainer
$complaints = [];
$sql = "SELECT c.id, c.title, c.description, c.photo_path, c.video_path, c.audio_path, c.status, c.created_at
        FROM complaints c
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $complaints[] = $row;
}
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Complaints | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php include __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Complainer</h3>
    <a href="complainer_dashboard.php">Dashboard</a>
    <a href="submit_complaint.php">Add Complaint</a>
    <a href="my_complaints.php" class="active">My Complaints</a>
    <a href="account.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content">
    <h1>My Complaints</h1>

    <div class="table-wrap card">
      <?php if (empty($complaints)): ?>
        <div class="empty">You haven’t submitted any complaints yet.</div>
      <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th class="media-cell">Media</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($complaints as $c): $id = (int)$c['id']; ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td class="media-cell">
              <?php if (!empty($c['photo_path'])): ?>
                <img src="<?= htmlspecialchars($c['photo_path']) ?>" alt="photo" class="thumb">
              <?php elseif (!empty($c['video_path'])): ?>
                <video class="thumb" controls>
                  <source src="<?= htmlspecialchars($c['video_path']) ?>" type="video/mp4">
                </video>
              <?php elseif (!empty($c['audio_path'])): ?>
                <audio controls class="audio-inline">
                  <source src="<?= htmlspecialchars($c['audio_path']) ?>" type="audio/mpeg">
                </audio>
              <?php else: ?>
                <span class="muted text-sm">No media</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= htmlspecialchars(str_replace(' ', '\\ ', $c['status'])) ?>">
                <?= htmlspecialchars($c['status']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars(substr($c['created_at'],0,16)) ?></td>
            <td><a class="view-link" href="view_complaint.php?id=<?= $id ?>">View</a></td>
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
