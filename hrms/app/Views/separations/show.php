<?php use App\Core\Auth; /** @var array $separation */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Separation Record</h1>
  <div class="ms-auto d-flex gap-2">
    <?php if (Auth::can('employees.manage')): ?>
      <a href="<?= url('separations/' . $separation['id'] . '/edit') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Edit
      </a>
    <?php endif ?>
    <a href="<?= url('separations') ?>" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>All Separations
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">

    <!-- Header card -->
    <div class="card mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="icon icon-lg <?= $separation['separation_type'] === 'retirement' ? 'blue' : ($separation['separation_type'] === 'dismissal' ? '' : '') ?>"
               style="width:52px;height:52px;font-size:1.5rem;background:var(--bs-danger-bg-subtle);color:var(--bs-danger)">
            <i class="bi bi-<?= match($separation['separation_type']) {
                'retirement'  => 'hourglass-split',
                'resignation' => 'box-arrow-right',
                'dismissal'   => 'shield-exclamation',
                'redundancy'  => 'briefcase',
                'deceased'    => 'flower2',
                default       => 'person-dash'
            } ?>"></i>
          </div>
          <div>
            <span class="badge text-bg-<?= match($separation['separation_type']) {
                'retirement'   => 'info',
                'resignation'  => 'secondary',
                'dismissal'    => 'danger',
                'redundancy'   => 'warning',
                'deceased'     => 'dark',
                default        => 'secondary'
            } ?> fs-6 mb-1"><?= label($separation['separation_type']) ?></span>
            <h2 class="h4 mb-0">
              <a href="<?= url('employees/' . $separation['emp_id']) ?>" class="text-decoration-none">
                <?= e($separation['employee_name']) ?>
              </a>
              <small class="text-muted fs-6"><?= e($separation['employee_no']) ?></small>
            </h2>
            <div class="text-muted small mt-1">
              <?= e($separation['position'] ?? '') ?><?= ($separation['department'] ? ' · ' . $separation['department'] : '') ?>
            </div>
          </div>
        </div>

        <dl class="row mb-0">
          <dt class="col-sm-4">Effective Date</dt>
          <dd class="col-sm-8"><strong><?= e($separation['effective_date']) ?></strong></dd>

          <?php if ($separation['notice_date']): ?>
            <dt class="col-sm-4">Notice Date</dt>
            <dd class="col-sm-8"><?= e($separation['notice_date']) ?></dd>
          <?php endif ?>

          <?php if ($separation['reason']): ?>
            <dt class="col-sm-4">Reason</dt>
            <dd class="col-sm-8"><?= nl2br(e($separation['reason'])) ?></dd>
          <?php endif ?>
        </dl>
      </div>
    </div>

    <!-- Exit interview -->
    <div class="card mb-4">
      <div class="card-header fw-semibold"><i class="bi bi-chat-left-text me-2"></i>Exit Interview</div>
      <div class="card-body">
        <?php if ($separation['exit_interview_date']): ?>
          <div class="text-muted small mb-2">Conducted: <?= e($separation['exit_interview_date']) ?></div>
          <?php if ($separation['exit_interview_notes']): ?>
            <p class="mb-0"><?= nl2br(e($separation['exit_interview_notes'])) ?></p>
          <?php else: ?>
            <p class="text-muted mb-0">No notes recorded.</p>
          <?php endif ?>
        <?php else: ?>
          <p class="text-muted mb-0">Exit interview not yet scheduled.</p>
        <?php endif ?>
      </div>
    </div>

    <!-- Final pay & notes -->
    <div class="card">
      <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-2"></i>Final Pay & Notes</div>
      <div class="card-body">
        <dl class="row mb-0">
          <?php if ($separation['final_pay_amount'] !== null): ?>
            <dt class="col-sm-4">Final Pay Amount</dt>
            <dd class="col-sm-8 fw-bold"><?= number_format((float)$separation['final_pay_amount'], 0) ?> RWF</dd>
          <?php endif ?>
          <?php if ($separation['final_pay_date']): ?>
            <dt class="col-sm-4">Final Pay Date</dt>
            <dd class="col-sm-8"><?= e($separation['final_pay_date']) ?></dd>
          <?php endif ?>
          <?php if ($separation['notes']): ?>
            <dt class="col-sm-4">Internal Notes</dt>
            <dd class="col-sm-8"><?= nl2br(e($separation['notes'])) ?></dd>
          <?php endif ?>
        </dl>
      </div>
    </div>

  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Clearance Status</h6>
        <span class="badge text-bg-<?= match($separation['clearance_status']) {
            'completed'   => 'success',
            'in_progress' => 'warning',
            default       => 'secondary'
        } ?> fs-6"><?= label($separation['clearance_status']) ?></span>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Rehire Eligibility</h6>
        <?php if ($separation['rehire_eligible']): ?>
          <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Eligible for rehire</span>
        <?php else: ?>
          <span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Not eligible for rehire</span>
        <?php endif ?>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Quick Actions</h6>
        <a href="<?= url('employees/' . $separation['emp_id']) ?>" class="btn btn-outline-ocean w-100 mb-2">
          <i class="bi bi-person me-1"></i>View Employee Profile
        </a>
        <?php if (Auth::can('employees.manage')): ?>
          <a href="<?= url('separations/' . $separation['id'] . '/edit') ?>" class="btn btn-outline-secondary w-100 mb-2">
            <i class="bi bi-pencil me-1"></i>Update Record
          </a>
          <form method="post" action="<?= url('separations/' . $separation['id'] . '/delete') ?>"
                data-confirm="Delete this separation record? The employee status will not be reverted automatically.">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete</button>
          </form>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
