<?php
session_start();

// Only allow logged-in complainers
if (empty($_SESSION['user']) || ($_SESSION['user']['user_type'] ?? '') !== 'complainer') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php'; // DB connection

$user_id = (int) $_SESSION['user']['id'];

// Get list of handlers
$handlers = [];
$res = $conn->query("SELECT id, name FROM users WHERE user_type = 'handler' ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $handlers[] = $row;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $handler_id = isset($_POST['handler_id']) && $_POST['handler_id'] !== '' ? (int) $_POST['handler_id'] : null;

    // Basic validation
    if ($title === '' || $description === '' || $handler_id === null) {
        $message = '<p style="color:red">Please fill all required fields.</p>';
    } else {
        // Prepare upload directory
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // Initialize paths
        $photo_path = null;
        $video_path = null;
        $audio_path = null;

        // Function to handle uploads
        function handle_upload($file_input_name, $upload_dir) {
            if (!empty($_FILES[$file_input_name]['name'])) {
                $filename = time() . '_' . basename($_FILES[$file_input_name]['name']);
                $target_path = $upload_dir . $filename;

                // Validate file type by mime for security (basic)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES[$file_input_name]['tmp_name']);
                finfo_close($finfo);

                // Accept certain mime types depending on input
                $allowed_photo = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $allowed_video = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
                $allowed_audio = ['audio/mpeg', 'audio/wav', 'audio/ogg'];

                if (
                    ($file_input_name === 'photo' && in_array($mime, $allowed_photo)) ||
                    ($file_input_name === 'video' && in_array($mime, $allowed_video)) ||
                    ($file_input_name === 'audio' && in_array($mime, $allowed_audio))
                ) {
                    if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_path)) {
                        return 'uploads/' . $filename;
                    }
                }
            }
            return null;
        }

        $photo_path = handle_upload('photo', $upload_dir);
        $video_path = handle_upload('video', $upload_dir);
        $audio_path = handle_upload('audio', $upload_dir);

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, handler_id, title, description, photo_path, video_path, audio_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())");
        $stmt->bind_param('iisssss', $user_id, $handler_id, $title, $description, $photo_path, $video_path, $audio_path);

        if ($stmt->execute()) {
            $message = '<p style="color:green">Complaint submitted successfully!</p>';
        } else {
            $message = '<p style="color:red">Error submitting complaint: ' . htmlspecialchars($stmt->error) . '</p>';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Submit Complaint | DCBS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="layout">
  <aside class="sidebar">
    <h3>Complainer</h3>
    <a href="complainer_dashboard.php">Dashboard</a>
    <a href="submit_complaint.php" class="active">Add Complaint</a>
    <a href="my_complaints.php">My Complaints</a>
    <a href="account.php">My Account</a>
    <a href="login.php">Logout</a>
  </aside>

  <section class="content">
    <h1>Submit New Complaint</h1>
    <div class="message"><?= $message ?></div>

    <form method="post" enctype="multipart/form-data" class="card form form-complaint">
      <label for="title">Title *</label>
      <input type="text" name="title" id="title" required>

      <label for="description">Description *</label>
      <textarea name="description" id="description" rows="5" required></textarea>

      <label for="handler_id">Assign To *</label>
      <select name="handler_id" id="handler_id" required>
        <option value="">-- Select Handler --</option>
        <?php foreach ($handlers as $h): ?>
          <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="photo">Photo (optional)</label>
      <input type="file" name="photo" id="photo" accept="image/*">

      <label for="video">Video (optional)</label>
      <input type="file" name="video" id="video" accept="video/*">

      <label for="audio">Audio (optional)</label>
      <input type="file" name="audio" id="audio" accept="audio/*">

      <button type="submit" class="btn btn-primary">Submit Complaint</button>
    </form>
  </section>
</main>

</body>
</html>
