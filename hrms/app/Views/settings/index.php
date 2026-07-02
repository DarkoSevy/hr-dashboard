<?php /** @var array $known $stored */ ?>
<h1 class="page-title mb-4">System Settings</h1>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-4">
        <form method="post" action="<?= url('settings') ?>">
          <?= csrf_field() ?>
          <?php foreach ($known as $key => [$labelText, $hint]): ?>
            <div class="row mb-3 align-items-center">
              <label class="col-md-5 col-form-label">
                <?= e($labelText) ?>
                <small class="d-block text-muted fw-normal"><?= e($hint) ?></small>
              </label>
              <div class="col-md-7">
                <input name="<?= e($key) ?>" class="form-control"
                       value="<?= e($stored[$key] ?? '') ?>">
              </div>
            </div>
          <?php endforeach ?>
          <div class="alert alert-light border small">
            <i class="bi bi-info-circle me-1 text-ocean"></i>
            Changes take effect immediately and are recorded in the audit trail.
          </div>
          <div class="text-end">
            <button class="btn btn-ocean px-4"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
