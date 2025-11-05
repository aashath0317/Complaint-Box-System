<?php
session_start();
$active = 'help';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Help | DCBS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css?v=2">
</head>
<body>

<?php require __DIR__ . '/topbar.php'; ?>

<main class="page">
  <section class="card hero hero--help">
    <h1 class="mt-0">Help & Support</h1>
    <p class="lead small">
      Need help using the Digital Complaint Box System? Below are quick answers and ways to contact us.
    </p>
  </section>

  <!-- FAQ -->
  <section class="card faq" aria-labelledby="faq-title">
    <h2 id="faq-title" class="section-title">Frequently Asked Questions</h2>

    <div class="faq-item" role="button" tabindex="0">
      <div class="question">
        <span>How do I create an account?</span>
        <span class="chev">▾</span>
      </div>
      <div class="answer">Go to <a class="view-link" href="register.php">Register</a> and fill in your name, email, and password. After registering you can login and submit complaints.</div>
    </div>

    <div class="faq-item" role="button" tabindex="0">
      <div class="question">
        <span>What should I include in a complaint?</span>
        <span class="chev">▾</span>
      </div>
      <div class="answer">Provide a clear title, a detailed description, and an optional image if available. Choose the most relevant category so handlers can respond faster.</div>
    </div>

    <div class="faq-item" role="button" tabindex="0">
      <div class="question">
        <span>I forgot my password. What now?</span>
        <span class="chev">▾</span>
      </div>
      <div class="answer">On the login page, click <em>Forgot password</em> and follow the instructions. (You mentioned you’ll add this later.)</div>
    </div>

    <div class="faq-item" role="button" tabindex="0">
      <div class="question">
        <span>How long until my complaint is handled?</span>
        <span class="chev">▾</span>
      </div>
      <div class="answer">Response times vary by department. Handlers update the complaint status—check <a class="view-link" href="complainer_dashboard.php">My Complaints</a> for progress.</div>
    </div>
  </section>

  <!-- Contact -->
  <section class="card">
    <h2 class="section-title">Contact Support</h2>

    <div class="contact-grid">
      <div class="contact-box">
        <strong>Support email</strong>
        <p class="small muted">For account issues or technical problems:</p>
        <p><a class="view-link" href="mailto:support@example.com">support@example.com</a></p>
      </div>

      <div class="contact-box">
        <strong>Phone / WhatsApp</strong>
        <p class="small muted">If urgent, call or message:</p>
        <p><a class="view-link" href="tel:+94123456789">+94 12 345 6789</a></p>
      </div>

      <div class="contact-box">
        <strong>Submit a complaint</strong>
        <p class="small muted">Ready to tell us? Click below to submit.</p>
        <p><a class="button" href="submit_complaint.php">Submit Complaint</a></p>
      </div>

      <div class="contact-box">
        <strong>Account help</strong>
        <p class="small muted">Need to register or login?</p>
        <p>
          <a class="button" href="register.php">Register</a>
          <a class="button ml-8" href="login.php">Login</a>
        </p>
      </div>
    </div>
  </section>

  <!-- Guidelines -->
  <section class="card">
    <h2 class="section-title">Guidelines & Good Practices</h2>
    <ul class="bullets small muted">
      <li>Be specific — include dates, locations, and steps to reproduce the issue when possible.</li>
      <li>Attach evidence — images or documents help handlers resolve issues faster.</li>
      <li>Respect privacy — do not include sensitive personal data in public complaints.</li>
    </ul>
    <p class="help-footer">Still need help? Email <a class="view-link" href="mailto:support@example.com">support@example.com</a> or call +94 12 345 6789.</p>
  </section>
</main>

<script>
  // Simple accordion
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.faq-item').forEach(function(item){
      item.addEventListener('click', function(){
        const ans = item.querySelector('.answer');
        const chev = item.querySelector('.chev');
        const open = ans.style.display === 'block';
        document.querySelectorAll('.faq .answer').forEach(a => a.style.display = 'none');
        document.querySelectorAll('.faq .chev').forEach(c => c.classList.remove('open'));
        if (!open) { ans.style.display = 'block'; chev.classList.add('open'); }
      });
    });
  });
</script>

</body>
</html>
