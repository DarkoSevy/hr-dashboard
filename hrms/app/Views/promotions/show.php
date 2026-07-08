<?php use App\Core\Auth; /** @var array $promotion */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Promotion Record</h1>
  <div class="ms-auto d-flex gap-2">
    <?php if (Auth::can('employees.manage')): ?>
      <a href="<?= url('promotions/' . $promotion['id'] . '/edit') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Edit
      </a>
    <?php endif ?>
    <a href="<?= url('promotions') ?>" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>All Promotions
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 mb-4">
          <div>
            <span class="badge text-bg-<?= match($promotion['promotion_type']) {
                'promotion'        => 'success',
                'lateral_transfer' => 'info',
                'acting'           => 'warning',
                'demotion'         => 'danger',
                default            => 'secondary'
            } ?> fs-6 mb-1"><?= label($promotion['promotion_type']) ?></span>
            <h2 class="h4 mb-0">
              <a href="<?= url('employees/' . $promotion['emp_id']) ?>" class="text-decoration-none">
                <?= e($promotion['employee_name']) ?>
              </a>
              <small class="text-muted fs-6"><?= e($promotion['employee_no']) ?></small>
            </h2>
          </div>
        </div>

        <!-- Career move visualisation -->
        <div class="row g-0 mb-4">
          <div class="col p-3 bg-light rounded-start border">
            <div class="small text-uppercase text-muted fw-bold mb-2">From</div>
            <div class="fw-semibold"><?= e($promotion['old_position'] ?? '—') ?></div>
            <?php if ($promotion['old_department']): ?>
              <div class="small text-muted"><?= e($promotion['old_department']) ?></div>
            <?php endif ?>
            <?php if ($promotion['old_salary'] !== null): ?>
              <div class="mt-2 small text-muted"><?= number_format((float)$promotion['old_salary'], 0) ?> RWF/mo</div>
            <?php endif ?>
          </div>
          <div class="col-auto d-flex align-items-center px-2 bg-white border-top border-bottom">
            <i class="bi bi-arrow-right fs-4 text-emerald"></i>
          </div>
          <div class="col p-3 bg-emerald-soft rounded-end border border-emerald">
            <div class="small text-uppercase text-emerald fw-bold mb-2">To</div>
            <div class="fw-bold"><?= e($promotion['new_position']) ?></div>
            <?php if ($promotion['new_department']): ?>
              <div class="small text-muted"><?= e($promotion['new_department']) ?></div>
            <?php endif ?>
            <?php if ($promotion['new_salary'] !== null): ?>
              <?php $diff = ($promotion['old_salary'] !== null) ? (float)$promotion['new_salary'] - (float)$promotion['old_salary'] : null; ?>
              <div class="mt-2 small fw-semibold <?= $diff !== null ? ($diff >= 0 ? 'text-success' : 'text-danger') : '' ?>">
                <?= number_format((float)$promotion['new_salary'], 0) ?> RWF/mo
                <?php if ($diff !== null): ?>
                  (<?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 0) ?> RWF)
                <?php endif ?>
              </div>
            <?php endif ?>
          </div>
        </div>

        <dl class="row mb-0">
          <dt class="col-sm-4">Effective Date</dt>
          <dd class="col-sm-8"><?= e($promotion['effective_date']) ?></dd>

          <?php if ($promotion['reason']): ?>
            <dt class="col-sm-4">Reason / Justification</dt>
            <dd class="col-sm-8"><?= nl2br(e($promotion['reason'])) ?></dd>
          <?php endif ?>

          <?php if ($promotion['approved_by_name']): ?>
            <dt class="col-sm-4">Approved By</dt>
            <dd class="col-sm-8"><?= e($promotion['approved_by_name']) ?></dd>
          <?php endif ?>

          <dt class="col-sm-4">Recorded</dt>
          <dd class="col-sm-8 text-muted small"><?= e($promotion['created_at']) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Quick Actions</h6>
        <a href="<?= url('employees/' . $promotion['emp_id']) ?>" class="btn btn-outline-ocean w-100 mb-2">
          <i class="bi bi-person me-1"></i>View Employee Profile
        </a>
        <a href="<?= url('promotions/create?employee_id=' . $promotion['emp_id']) ?>" class="btn btn-outline-secondary w-100 mb-2">
          <i class="bi bi-plus-lg me-1"></i>Record Another Move
        </a>
        <?php if (Auth::can('employees.manage')): ?>
          <form method="post" action="<?= url('promotions/' . $promotion['id'] . '/delete') ?>"
                data-confirm="Permanently delete this promotion record?">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete Record</button>
          </form>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
