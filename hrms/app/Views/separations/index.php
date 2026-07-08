<?php use App\Core\Auth; /** @var array $separations */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Separations & Offboarding</h1>
  <?php if (Auth::can('employees.manage')): ?>
    <a href="<?= url('separations/create') ?>" class="btn btn-ocean ms-auto">
      <i class="bi bi-box-arrow-right me-1"></i>Process Separation
    </a>
  <?php endif ?>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle datatable">
      <thead><tr>
        <th>Employee</th>
        <th>Department / Position</th>
        <th>Type</th>
        <th>Effective Date</th>
        <th>Clearance</th>
        <th>Rehire Eligible</th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($separations as $s): ?>
        <tr>
          <td class="fw-semibold">
            <a href="<?= url('employees/' . $s['employee_id']) ?>" class="text-decoration-none">
              <?= e($s['employee_name']) ?>
            </a>
            <br><small class="text-muted"><?= e($s['employee_no']) ?></small>
          </td>
          <td class="small text-muted">
            <?= e($s['department'] ?? '—') ?><br><?= e($s['position'] ?? '—') ?>
          </td>
          <td>
            <span class="badge text-bg-<?= match($s['separation_type']) {
                'retirement'   => 'info',
                'resignation'  => 'secondary',
                'dismissal'    => 'danger',
                'redundancy'   => 'warning',
                'deceased'     => 'dark',
                default        => 'secondary'
            } ?>">
              <?= label($s['separation_type']) ?>
            </span>
          </td>
          <td><?= e($s['effective_date']) ?></td>
          <td>
            <span class="badge text-bg-<?= match($s['clearance_status']) {
                'completed'   => 'success',
                'in_progress' => 'warning',
                default       => 'secondary'
            } ?>">
              <?= label($s['clearance_status']) ?>
            </span>
          </td>
          <td>
            <?php if ($s['rehire_eligible']): ?>
              <i class="bi bi-check-circle-fill text-success"></i> Yes
            <?php else: ?>
              <i class="bi bi-x-circle-fill text-danger"></i> No
            <?php endif ?>
          </td>
          <td class="text-end text-nowrap">
            <a href="<?= url('separations/' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-eye"></i>
            </a>
            <?php if (Auth::can('employees.manage')): ?>
              <a href="<?= url('separations/' . $s['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i>
              </a>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach ?>
      <?php if (!$separations): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No separation records yet.</td></tr>
      <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
