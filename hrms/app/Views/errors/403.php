<div class="text-center py-5">
  <h1 class="display-1 text-warning fw-bold">403</h1>
  <p class="text-muted">
    You don't have permission to do this
    <?php if (!empty($permission)): ?>(<code><?= e($permission) ?></code>)<?php endif ?>.
    Contact HR or the system administrator if you believe this is an error.
  </p>
  <a href="<?= url('dashboard') ?>" class="btn btn-ocean"><i class="bi bi-house me-1"></i>Back to Dashboard</a>
</div>
