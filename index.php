<?php
session_start(); // start session first
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Digital Complaint Box System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/topbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to the Complaint Management System</h1>
        <p>Your Voice Matters — We’re Here to Listen.</p>
        <div class="btn-container">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Complainer Register</a>
        </div>
    </div>

</body>
</html>
