<?php
session_start();
require_once __DIR__ . '/config.php';

// Check login and complainer type
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'complainer') {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user']['id'];
$complaint_id = (int) ($_GET['id'] ?? 0);
if ($complaint_id <= 0) {
    die("Invalid complaint ID.");
}

// Fetch complaint details, only if belongs to logged-in user
$sql = "SELECT c.*, u.name AS handler_name
        FROM complaints c
        LEFT JOIN users u ON c.handler_id = u.id
        WHERE c.id = ? AND c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();
$stmt->close();

if (!$complaint) {
    die("Complaint not found or you do not have permission.");
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Complaint Details | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php $active = ''; require __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Complainer</h3>
    <a href="complainer_dashboard.php">Dashboard</a>
    <a href="submit_complaint.php">Add Complaint</a>
    <a href="my_complaints.php">My Complaints</a>
    <a href="account.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content content-narrow">
    <h1 class="title-accent">Complaint Details</h1>

    <button class="btn btn-primary btn-print" onclick="window.print()">🖨️ Print</button>

    <div class="card">
      <section>
        <label class="field-label">Complaint ID</label>
        <div><?= htmlspecialchars($complaint['id']) ?></div>
      </section>

      <section>
        <label class="field-label">Title</label>
        <div><?= htmlspecialchars($complaint['title']) ?></div>
      </section>

      <section>
        <label class="field-label">Description</label>
        <div class="prewrap"><?= htmlspecialchars($complaint['description']) ?></div>
      </section>

      <section>
        <label class="field-label">Handler</label>
        <div><?= htmlspecialchars($complaint['handler_name'] ?? 'Not Assigned') ?></div>
      </section>

      <section>
        <label class="field-label">Status</label>
        <div>
          <span class="badge <?= htmlspecialchars(str_replace(' ', '\\ ', $complaint['status'])) ?>">
            <?= htmlspecialchars($complaint['status']) ?>
          </span>
        </div>
      </section>

      <section class="meta-grid">
        <div>
          <label class="field-label">Created At</label>
          <div><?= date("Y-m-d H:i", strtotime($complaint['created_at'])) ?></div>
        </div>
        <div>
          <label class="field-label">Updated At</label>
          <div><?= date("Y-m-d H:i", strtotime($complaint['updated_at'])) ?></div>
        </div>
      </section>

      <section>
        <label class="field-label">Photo</label>
        <div class="media">
          <?php if ($complaint['photo_path']): ?>
            <img src="<?= htmlspecialchars($complaint['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            <span class="muted text-sm">No Photo Uploaded.</span>
          <?php endif; ?>
        </div>
      </section>

      <section>
        <label class="field-label">Video</label>
        <div class="media">
          <?php if ($complaint['video_path']): ?>
            <video controls>
              <source src="<?= htmlspecialchars($complaint['video_path']) ?>" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          <?php else: ?>
            <span class="muted text-sm">No Video Uploaded.</span>
          <?php endif; ?>
        </div>
      </section>

      <section>
        <label class="field-label">Audio</label>
        <div class="media">
          <?php if ($complaint['audio_path']): ?>
            <audio controls class="audio-inline">
              <source src="<?= htmlspecialchars($complaint['audio_path']) ?>" type="audio/mpeg">
              Your browser does not support the audio element.
            </audio>
          <?php else: ?>
            <span class="muted text-sm">No Audio Uploaded.</span>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </section>
</main>

</body>
</html>
