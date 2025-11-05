<?php

$active = $active ?? '';
?>
<header class="dcbs-topbar" role="banner">
  <div class="dcbs-topbar-inner">
    <a class="dcbs-logo-link" href="index.php" aria-label="DCBS Home">
      <img src="logo.png" alt="DCBS Logo" class="dcbs-logo-img">
    </a>

    <nav class="dcbs-menu" aria-label="Main menu">
      <a class="<?= $active === 'home' ? 'active' : '' ?>" href="index.php">Home</a>
      <a class="<?= $active === 'about' ? 'active' : '' ?>" href="about.php">About</a>
      <a class="<?= $active === 'help' ? 'active' : '' ?>" href="help.php">Help</a>

      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= htmlspecialchars($_SESSION['user']['user_type'] === 'handler' ? 'handler_dashboard.php' : 'complainer_dashboard.php') ?>">Dashboard</a>
        <a href="logout.php" class="logout-link">Logout</a>
      <?php else: ?>
        <a class="<?= $active === 'login' ? 'active' : '' ?>" href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
