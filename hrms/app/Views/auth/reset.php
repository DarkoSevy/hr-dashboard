<?php $flash = flash(''); /** @var string $token */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Choose New Password — PTS HRMS</title>
<link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="card auth-card shadow-lg">
    <div class="card-body p-4 p-md-5">
      <div class="text-center">
        <i class="bi bi-key display-4 text-ocean"></i>
        <h5 class="mt-3 fw-bold">Choose a new password</h5>
        <p class="text-muted small">8+ characters with upper &amp; lower case, a digit and a symbol.</p>
      </div>
      <?php if ($flash): ?>
        <div class="alert alert-danger py-2 small"><?= e($flash['message']) ?></div>
      <?php endif ?>
      <form method="post" action="/reset">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3"><label class="form-label small fw-semibold">New Password</label>
          <input type="password" name="password" class="form-control" required autofocus autocomplete="new-password"></div>
        <div class="mb-4"><label class="form-label small fw-semibold">Confirm Password</label>
          <input type="password" name="confirm" class="form-control" required autocomplete="new-password"></div>
        <button class="btn btn-ocean w-100 py-2 fw-semibold">Set Password</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
