<?php $flash = flash(''); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In — PTS HRMS</title>
<link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="card auth-card shadow-lg">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <i class="bi bi-bus-front display-4 text-ocean"></i>
        <h4 class="mt-2 mb-0 fw-bold text-ocean">PTS HRMS</h4>
        <p class="text-muted small">Premier Transport &amp; Tour Services Ltd</p>
      </div>
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> py-2 small">
          <?= e($flash['message']) ?>
        </div>
      <?php endif ?>
      <form method="post" action="/login">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Username or Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label small fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <button class="btn btn-ocean w-100 py-2 fw-semibold">
          Sign In <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </form>
      <p class="text-center text-muted small mt-4 mb-0">
        Forgot your password? Contact the HR department.
      </p>
    </div>
  </div>
</div>
</body>
</html>
