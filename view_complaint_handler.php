<?php
// view_complaint_handler.php
session_start();
require_once __DIR__ . '/config.php';

// protect page: must be logged in and user_type = handler
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'handler') {
    header('Location: login.php');
    exit;
}

$handler_id = (int)$_SESSION['user']['id'];
$complaint_id = (int)($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    die('Invalid complaint ID.');
}

// Fetch complaint with user info
$stmt = $conn->prepare("
    SELECT c.*, u.name AS complainer_name, u.email AS complainer_email
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.id = ? AND c.handler_id = ?
");
$stmt->bind_param('ii', $complaint_id, $handler_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();
$stmt->close();

if (!$complaint) {
    die('Complaint not found or not assigned to you.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Complaint #<?= htmlspecialchars($complaint['id']) ?> | Handler</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Handler</h3>
    <a href="handler_dashboard.php">Dashboard</a>
    <a href="handler_my_complaints.php" class="active">My Complaints</a>
    <a href="handler_profile.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content content-narrow">
    <div class="header-row">
      <h1 class="title-accent">Complaint #<?= htmlspecialchars($complaint['id']) ?></h1>
      <button class="btn btn-primary btn-sm" onclick="window.print()">🖨️ Print</button>
    </div>

    <div class="card complaint-view">
      <section class="field">
        <div class="field-label">Title</div>
        <div class="field-val prewrap"><?= nl2br(htmlspecialchars($complaint['title'])) ?></div>
      </section>

      <section class="field">
        <div class="field-label">Description</div>
        <div class="field-val prewrap"><?= nl2br(htmlspecialchars($complaint['description'])) ?></div>
      </section>

      <section class="meta-grid">
        <div>
          <div class="field-label">Complainer Name</div>
          <div class="field-val"><?= htmlspecialchars($complaint['complainer_name']) ?></div>
        </div>
        <div>
          <div class="field-label">Complainer Email</div>
          <div class="field-val"><a href="mailto:<?= htmlspecialchars($complaint['complainer_email']) ?>"><?= htmlspecialchars($complaint['complainer_email']) ?></a></div>
        </div>
      </section>

      <section class="meta-grid">
        <div>
          <div class="field-label">Status</div>
          <div class="field-val">
            <span class="badge <?= htmlspecialchars(str_replace(' ', '\\ ', $complaint['status'])) ?>">
              <?= htmlspecialchars($complaint['status']) ?>
            </span>
          </div>
        </div>
        <div>
          <div class="field-label">Submitted On</div>
          <div class="field-val"><?= htmlspecialchars($complaint['created_at']) ?></div>
        </div>
      </section>

      <?php if ($complaint['photo_path']): ?>
      <section class="field">
        <div class="field-label">Photo Evidence</div>
        <div class="media">
          <img src="<?= htmlspecialchars($complaint['photo_path']) ?>" alt="Photo evidence" class="media-img">
        </div>
      </section>
      <?php endif; ?>

      <?php if ($complaint['video_path']): ?>
      <section class="field">
        <div class="field-label">Video Evidence</div>
        <div class="media">
          <video controls class="media-video">
            <source src="<?= htmlspecialchars($complaint['video_path']) ?>" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($complaint['audio_path']): ?>
      <section class="field">
        <div class="field-label">Audio Evidence</div>
        <div class="media">
          <audio controls class="audio-inline">
            <source src="<?= htmlspecialchars($complaint['audio_path']) ?>" type="audio/mpeg" />
            Your browser does not support the audio element.
          </audio>
        </div>
      </section>
      <?php endif; ?>

      <div class="back-link-wrap">
        <a class="view-link" href="handler_my_complaints.php">&larr; Back to My Complaints</a>
      </div>
    </div>
  </section>
</main>

</body>
</html>
