<?php
// handler_my_complaints.php
session_start();
require_once __DIR__ . '/config.php'; // provides $conn (mysqli)


$handler_id = (int) $_SESSION['user']['id'];
$message = '';

// Handle inline status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $complaint_id = (int) ($_POST['complaint_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');
    $allowed = ['Pending', 'In Progress', 'Solved'];

    if ($complaint_id > 0 && in_array($new_status, $allowed, true)) {
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

// Fetch all complaints assigned to this handler
$complaints = [];
$sql = "SELECT c.id, c.title, c.description, c.photo_path, c.video_path, c.audio_path, c.status, c.created_at, u.name AS complainer_name
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.handler_id = ?
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $handler_id);
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
<title>My Complaints (Handler) | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Handler</h3>
    <a href="handler_dashboard.php">Dashboard</a>
    <a href="handler_my_complaints.php" class="active">My Complaints</a>
    <a href="handler_profile.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content">
    <h1>Complaints Assigned to You</h1>

    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <div class="table-wrap card">
      <?php if (empty($complaints)): ?>
        <div class="empty">No complaints assigned to you yet.</div>
      <?php else: ?>
      <table id="complaintsTable" class="table">
        <thead>
          <tr>
            <th data-type="number">ID</th>
            <th data-type="string">Title</th>
            <th data-type="string">Complainer</th>
            <th class="media-cell" data-type="string">Media</th>
            <th data-type="string">Status</th>
            <th data-type="date">Created At</th>
            <th data-type="string">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($complaints as $c): $id = (int)$c['id']; ?>
          <tr>
            <td><?= $id ?></td>
            <td class="fw-700">
              <a class="view-link" href="view_complaint_handler.php?id=<?= $id ?>"><?= htmlspecialchars($c['title']) ?></a>
            </td>
            <td><?= htmlspecialchars($c['complainer_name'] ?? '—') ?></td>

            <td class="media-cell">
              <?php if (!empty($c['photo_path'])): ?>
                <img src="<?= htmlspecialchars($c['photo_path']) ?>" alt="photo" class="thumb">
              <?php elseif (!empty($c['video_path'])): ?>
                <video class="thumb" controls>
                  <source src="<?= htmlspecialchars($c['video_path']) ?>" type="video/mp4">
                  Your browser does not support the video tag.
                </video>
              <?php elseif (!empty($c['audio_path'])): ?>
                <audio controls class="audio-inline">
                  <source src="<?= htmlspecialchars($c['audio_path']) ?>" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio>
              <?php else: ?>
                <span class="muted text-sm">No media</span>
              <?php endif; ?>
            </td>

            <td><span class="badge <?= htmlspecialchars(str_replace(' ', '\\ ', $c['status'])) ?>"><?= htmlspecialchars($c['status']) ?></span></td>
            <td><?= htmlspecialchars(substr($c['created_at'],0,16)) ?></td>

            <td>
              <div class="actions">
                <a class="view-link" href="view_complaint_handler.php?id=<?= $id ?>">View</a>

                <form class="form-inline" method="post">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="complaint_id" value="<?= $id ?>">
                  <select name="status" aria-label="Status">
                    <option value="Pending"     <?= $c['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Solved"      <?= $c['status'] === 'Solved' ? 'selected' : '' ?>>Solved</option>
                  </select>
                  <button type="submit" title="Update" class="btn btn-primary btn-sm">Update</button>
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

<script>
// Simple client-side sortable table (click headers)
document.addEventListener('DOMContentLoaded', function () {
  const table = document.getElementById('complaintsTable');
  if (!table) return;
  const headers = table.querySelectorAll('th');
  let sortDir = Array(headers.length).fill(null);

  headers.forEach((header, index) => {
    header.addEventListener('click', () => {
      const type = header.getAttribute('data-type');
      const tbody = table.tBodies[0];
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const current = sortDir[index];
      const dir = current === 'asc' ? 'desc' : 'asc';

      headers.forEach(h => h.classList.remove('asc','desc'));

      rows.sort((a,b) => {
        let A = a.cells[index].textContent.trim();
        let B = b.cells[index].textContent.trim();

        if (type === 'number') {
          A = parseInt(A) || 0; B = parseInt(B) || 0;
          return dir === 'asc' ? A - B : B - A;
        } else if (type === 'date') {
          A = new Date(A); B = new Date(B);
          return dir === 'asc' ? A - B : B - A;
        } else {
          A = A.toLowerCase(); B = B.toLowerCase();
          if (A < B) return dir === 'asc' ? -1 : 1;
          if (A > B) return dir === 'asc' ? 1 : -1;
          return 0;
        }
      });

      rows.forEach(r => tbody.appendChild(r));
      header.classList.add(dir);
      sortDir.fill(null);
      sortDir[index] = dir;
    });
  });
});
</script>

</body>
</html>
