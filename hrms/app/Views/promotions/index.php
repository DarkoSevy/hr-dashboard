<?php use App\Core\Auth; /** @var array $promotions */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Promotions & Career Moves</h1>
  <?php if (Auth::can('employees.manage')): ?>
    <a href="<?= url('promotions/create') ?>" class="btn btn-emerald ms-auto">
      <i class="bi bi-plus-lg me-1"></i>Record Promotion
    </a>
  <?php endif ?>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle datatable">
      <thead><tr>
        <th>Employee</th>
        <th>Type</th>
        <th>From</th>
        <th>To</th>
        <th>Salary Change</th>
        <th>Effective Date</th>
        <th>Approved By</th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($promotions as $p): ?>
        <tr>
          <td class="fw-semibold">
            <a href="<?= url('employees/' . $p['employee_id']) ?>" class="text-decoration-none">
              <?= e($p['employee_name']) ?>
            </a>
            <br><small class="text-muted"><?= e($p['employee_no']) ?></small>
          </td>
          <td>
            <span class="badge text-bg-<?= match($p['promotion_type']) {
                'promotion'         => 'success',
                'lateral_transfer'  => 'info',
                'acting'            => 'warning',
                'demotion'          => 'danger',
                default             => 'secondary'
            } ?>">
              <?= label($p['promotion_type']) ?>
            </span>
          </td>
          <td class="small">
            <?= e($p['old_position'] ?? '—') ?>
            <?php if ($p['old_department']): ?>
              <br><span class="text-muted"><?= e($p['old_department']) ?></span>
            <?php endif ?>
          </td>
          <td class="small fw-semibold">
            <?= e($p['new_position']) ?>
            <?php if ($p['new_department']): ?>
              <br><span class="text-muted"><?= e($p['new_department']) ?></span>
            <?php endif ?>
          </td>
          <td class="small text-nowrap">
            <?php if ($p['old_salary'] !== null && $p['new_salary'] !== null): ?>
              <?= number_format((float)$p['old_salary'], 0) ?> → <?= number_format((float)$p['new_salary'], 0) ?>
              <?php $diff = (float)$p['new_salary'] - (float)$p['old_salary']; ?>
              <br><span class="<?= $diff >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 0) ?> RWF
              </span>
            <?php elseif ($p['new_salary'] !== null): ?>
              <?= number_format((float)$p['new_salary'], 0) ?> RWF
            <?php else: ?>
              —
            <?php endif ?>
          </td>
          <td><?= e($p['effective_date']) ?></td>
          <td class="small text-muted"><?= e($p['approved_by_name'] ?? '—') ?></td>
          <td class="text-end text-nowrap">
            <a href="<?= url('promotions/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-eye"></i>
            </a>
            <?php if (Auth::can('employees.manage')): ?>
              <a href="<?= url('promotions/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="post" action="<?= url('promotions/' . $p['id'] . '/delete') ?>" class="d-inline"
                    data-confirm="Delete this promotion record?">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach ?>
      <?php if (!$promotions): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No promotion records yet.</td></tr>
      <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
