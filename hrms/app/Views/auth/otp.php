<?php $flash = flash(''); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verification — PTS HRMS</title>
<link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="card auth-card shadow-lg">
    <div class="card-body p-4 p-md-5 text-center">
      <i class="bi bi-shield-lock display-4 text-ocean"></i>
      <h5 class="mt-3 fw-bold">Two-Factor Verification</h5>
      <p class="text-muted small">Enter the 6-digit code sent to your email. It expires in 10 minutes.</p>
      <?php if ($flash): ?>
        <div class="alert alert-danger py-2 small"><?= e($flash['message']) ?></div>
      <?php endif ?>
      <form method="post" action="/otp">
        <input type="text" name="code" maxlength="6" pattern="\d{6}" required autofocus
               class="form-control form-control-lg text-center fs-3 mb-3" style="letter-spacing:.5em">
        <button class="btn btn-ocean w-100 py-2 fw-semibold">Verify</button>
      </form>
      <a href="/login" class="small d-inline-block mt-3">Back to sign in</a>
    </div>
  </div>
</div>
</body>
</html>
