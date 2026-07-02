<?php /** @var array $user  @var ?array $employee */ ?>
<h1 class="page-title mb-4">My Account</h1>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-key me-2"></i>Change Password</div>
      <div class="card-body">
        <form method="post" action="<?= url('account/password') ?>">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
          <div class="mb-3"><label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required autocomplete="new-password">
            <small class="text-muted">8+ characters, with upper &amp; lower case, a digit and a symbol.</small></div>
          <div class="mb-3"><label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password"></div>
          <button class="btn btn-ocean w-100">Update Password</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><i class="bi bi-shield-lock me-2"></i>Two-Factor Authentication</div>
      <div class="card-body">
        <p class="small text-muted">
          When enabled, a 6-digit code is emailed to
          <strong><?= e($user['email']) ?></strong> at each sign-in.
        </p>
        <form method="post" action="<?= url('account/two-factor') ?>">
          <?= csrf_field() ?>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="enable" id="tfa"
                   <?= $user['two_factor_enabled'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="tfa">Enable email verification codes</label>
          </div>
          <button class="btn btn-outline-primary w-100">Save</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><i class="bi bi-telephone me-2"></i>My Contact Information</div>
      <div class="card-body">
        <?php if (!$employee): ?>
          <p class="text-muted small mb-0">Your account is not linked to an employee record —
            contact HR to link it.</p>
        <?php else: ?>
          <form method="post" action="<?= url('account/contact') ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6"><label class="form-label">Phone</label>
              <input name="phone" class="form-control" value="<?= e($employee['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= e($employee['email'] ?? '') ?>"></div>
            <div class="col-12"><label class="form-label">Physical Address</label>
              <input name="address" class="form-control" value="<?= e($employee['address'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Emergency Contact Name</label>
              <input name="emergency_name" class="form-control" value="<?= e($employee['emergency_name'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Emergency Phone</label>
              <input name="emergency_phone" class="form-control" value="<?= e($employee['emergency_phone'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Relationship</label>
              <input name="emergency_relation" class="form-control" value="<?= e($employee['emergency_relation'] ?? '') ?>"></div>
            <div class="col-12 text-end">
              <button class="btn btn-emerald px-4"><i class="bi bi-check-lg me-1"></i>Save Contact Info</button>
            </div>
          </form>
          <div class="alert alert-light border small mt-3 mb-0">
            <i class="bi bi-info-circle me-1 text-ocean"></i>
            Changes are logged and visible to HR. Employment details (department, position,
            salary, statutory numbers) can only be changed by HR.
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
