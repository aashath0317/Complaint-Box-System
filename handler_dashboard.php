<?php
// handler_dashboard.php
session_start();
require_once __DIR__ . '/config.php'; // provides $conn (mysqli)

// protect page: must be logged in and user_type = handler
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'handler') {
    header('Location: login.php');
    exit;
}

$handler_id = (int) $_SESSION['user']['id'];
$message = '';

// Handle inline status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $complaint_id = (int) ($_POST['complaint_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');

    // basic validation
    $allowed = ['Pending', 'In Progress', 'Solved'];
    if ($complaint_id > 0 && in_array($new_status, $allowed, true)) {
        // update only if this complaint is assigned to this handler
        $upd = $conn->prepare("UPDATE complaints SET status = ?, updated_at = NOW() WHERE id = ? AND handler_id = ?");
        $upd->bind_param('sii', $new_status, $complaint_id, $handler_id);
        if ($upd->execute() && $upd->affected_rows > 0) {
            $message = '<p class="msg msg-success">Status updated successfully.</p>';
        } else {
            $message = '<p class="msg msg-error">Unable to update status (maybe not assigned or no change).</p>';
        }
        $upd->close();
    } else {
        $message = '<p class="msg msg-error">Invalid input for status update.</p>';
    }
}

// --- fetch counts ---
$counts = ['Solved' => 0, 'In Progress' => 0, 'Pending' => 0];
$stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM complaints WHERE handler_id = ? GROUP BY status");
$stmt->bind_param('i', $handler_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $counts[$row['status']] = (int) $row['total'];
}
$stmt->close();

// --- fetch recent complaints assigned to this handler ---
$recent = [];
$stmt2 = $conn->prepare("SELECT c.id, c.title, c.description, c.photo_path, c.video_path, c.audio_path, c.status, c.created_at, u.name AS complainer_name
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.handler_id = ?
    ORDER BY c.created_at DESC
    LIMIT 20");
$stmt2->bind_param('i', $handler_id);
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
<title>Handler Dashboard | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Handler</h3>
    <a href="handler_dashboard.php" class="active">Dashboard</a>
    <a href="handler_my_complaints.php">My Complaints</a>
    <a href="handler_profile.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content">
    <div class="header-row">
      <div>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Handler') ?></h1>
        <p class="muted">Assigned complaints quick view.</p>
      </div>
    </div>

    <?= $message ?>

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

    <!-- Recent assigned complaints -->
    <div class="table-wrap card">
      <h3 class="mt-0">Assigned Complaints</h3>

      <?php if (count($recent) === 0): ?>
        <div class="empty">No complaints assigned to you yet.</div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title / Complainer</th>
              <th class="media-cell">Media</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $row):
              $id = (int)$row['id'];
            ?>
            <tr>
              <td><?= $id ?></td>
              <td>
                <div class="fw-700"><a class="view-link" href="view_complaint_handler.php?id=<?= $id ?>"><?= htmlspecialchars($row['title']) ?></a></div>
                <div class="muted text-sm">By: <?= htmlspecialchars($row['complainer_name'] ?? '—') ?></div>
              </td>

              <td class="media-cell">
                <?php if ($row['photo_path']): ?>
                  <img src="<?= htmlspecialchars($row['photo_path']) ?>" alt="photo" class="thumb">
                <?php elseif ($row['video_path']): ?>
                  <video class="thumb" controls>
                    <source src="<?= htmlspecialchars($row['video_path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                  </video>
                <?php elseif ($row['audio_path']): ?>
                  <audio controls class="audio-inline">
                    <source src="<?= htmlspecialchars($row['audio_path']) ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                  </audio>
                <?php else: ?>
                  <span class="muted text-sm">No media</span>
                <?php endif; ?>
              </td>

              <td>
                <span class="badge <?= htmlspecialchars(str_replace(' ', '\\ ', $row['status'])) ?>"><?= htmlspecialchars($row['status']) ?></span>
              </td>

              <td><?= htmlspecialchars(substr($row['created_at'],0,16)) ?></td>

              <td>
                <div class="actions">
                  <a class="view-link" href="view_complaint_handler.php?id=<?= $id ?>">View</a>

                  <!-- inline status update form -->
                  <form class="form-inline" method="post">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="complaint_id" value="<?= $id ?>">
                    <select name="status" aria-label="status">
                      <option value="Pending"     <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="In Progress" <?= $row['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                      <option value="Solved"      <?= $row['status'] === 'Solved' ? 'selected' : '' ?>>Solved</option>
                    </select>
                    <button type="submit" title="Update status" class="btn btn-primary btn-sm">Update</button>
                  </form>
                </div>
              </td>
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
