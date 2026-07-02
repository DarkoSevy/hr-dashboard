<?php use App\Core\Auth; ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Employees</h1>
  <?php if (Auth::can('employees.manage')): ?>
    <a href="<?= url('employees/create') ?>" class="btn btn-emerald ms-auto">
      <i class="bi bi-person-plus me-1"></i> New Employee
    </a>
  <?php endif ?>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle datatable">
        <thead>
          <tr>
            <th>No.</th><th>Name</th><th>Department</th><th>Position</th>
            <th>Type</th><th>Status</th><th>Hired</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $emp): ?>
          <tr>
            <td class="fw-semibold"><?= e($emp['employee_no']) ?></td>
            <td>
              <a href="<?= url('employees/' . $emp['id']) ?>" class="text-decoration-none fw-semibold">
                <?= e($emp['full_name']) ?>
              </a>
              <?php if ($emp['is_driver']): ?><i class="bi bi-truck-front text-success ms-1" title="Driver"></i><?php endif ?>
              <br><small class="text-muted"><?= e($emp['email'] ?? '') ?></small>
            </td>
            <td><?= e($emp['department'] ?? '—') ?></td>
            <td><?= e($emp['position'] ?? '—') ?></td>
            <td><span class="badge text-bg-light border"><?= label($emp['employment_type']) ?></span></td>
            <td>
              <?php $tone = match ($emp['status']) {
                  'active' => 'success', 'suspended' => 'warning', default => 'secondary' }; ?>
              <span class="badge badge-status text-bg-<?= $tone ?>"><?= label($emp['status']) ?></span>
            </td>
            <td><?= e($emp['date_hired']) ?></td>
            <td class="text-end text-nowrap">
              <a href="<?= url('employees/' . $emp['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Profile"><i class="bi bi-eye"></i></a>
              <?php if (Auth::can('employees.manage')): ?>
                <a href="<?= url('employees/' . $emp['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
