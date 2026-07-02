<?php $flash = flash(''); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password — PTS HRMS</title>
<link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="card auth-card shadow-lg">
    <div class="card-body p-4 p-md-5 text-center">
      <i class="bi bi-envelope-lock display-4 text-ocean"></i>
      <h5 class="mt-3 fw-bold">Forgot your password?</h5>
      <p class="text-muted small">Enter your account email and we'll send you a reset link
        (valid for 1 hour).</p>
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> py-2 small">
          <?= e($flash['message']) ?></div>
      <?php endif ?>
      <form method="post" action="/forgot">
        <input type="email" name="email" class="form-control mb-3" placeholder="you@pts.rw" required autofocus>
        <button class="btn btn-ocean w-100 py-2">Send Reset Link</button>
      </form>
      <a href="/login" class="small d-inline-block mt-3">Back to sign in</a>
    </div>
  </div>
</div>
</body>
</html>
