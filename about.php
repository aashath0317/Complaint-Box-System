<?php
session_start();
$active = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About | Digital Complaint Box System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="page">
  <section class="card page-body">
    <h1 class="mt-0">About Digital Complaint Box System</h1>

    <p class="lead">
      Welcome to the <strong>Digital Complaint Box System (DCBS)</strong>. DCBS is built to streamline complaint submission,
      tracking, and handling — making the process transparent and efficient for both complainers and handlers.
    </p>

    <p>Our goal is to modernize how complaints are received and resolved by providing:</p>
    <ul class="list">
      <li>Secure complaint submission with attachments and timestamps.</li>
      <li>Role-based access (Complainer, Handler) for proper workflows.</li>
      <li>Real-time status updates and notifications.</li>
      <li>Simple, responsive UI that works on desktop and mobile.</li>
    </ul>

    <p>
      DCBS is actively maintained and will receive improvements based on user feedback and operational needs.
      If you have suggestions or need support, please contact the system administrator.
    </p>

    <hr class="hr" />

    <p class="small">
      Thank you for using <span class="fw-700" style="color: var(--logo)">Digital Complaint Box System</span>.
    </p>
  </section>
</main>

</body>
</html>
