<?php use App\Core\Auth; /** @var array $periods */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Payroll Preparation</h1>
  <?php if (Auth::can('payroll.manage')): ?>
    <form method="post" action="<?= url('payroll/generate') ?>" class="ms-auto d-flex gap-2">
      <?= csrf_field() ?>
      <input type="month" name="month" value="<?= date('Y-m') ?>" class="form-control" required>
      <button class="btn btn-emerald text-nowrap"><i class="bi bi-gear me-1"></i>Generate</button>
    </form>
  <?php endif ?>
</div>

<div class="alert alert-light border small">
  <i class="bi bi-info-circle me-1 text-ocean"></i>
  Payroll data is prepared here (basic pay, allowances, RSSB, PAYE, deductions) and exported as CSV
  for the Finance department / QuickBooks. Actual payment runs in the Finance module.
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Period</th><th>Dates</th><th>Employees</th><th>Total Net</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($periods as $p): ?>
        <tr>
          <td class="fw-semibold"><?= e($p['name']) ?></td>
          <td class="small text-muted"><?= e($p['start_date']) ?> → <?= e($p['end_date']) ?></td>
          <td><?= (int) $p['entries'] ?></td>
          <td class="fw-semibold"><?= rwf($p['total_net']) ?></td>
          <td><span class="badge text-bg-<?= match ($p['status']) {
              'open' => 'secondary', 'processing' => 'warning', 'locked' => 'info', 'exported' => 'success' } ?>">
            <?= label($p['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= url('payroll/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Open</a>
          </td>
        </tr>
      <?php endforeach ?>
      <?php if (!$periods): ?><tr><td colspan="6" class="text-muted small">No payroll periods yet — generate one above.</td></tr><?php endif ?>
      </tbody>
    </table>
  </div>
</div>
