<?php
use App\Core\Auth;
/** @var array $period $entries */
$editable = in_array($period['status'], ['open', 'processing'], true) && Auth::can('payroll.manage');
?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Payroll — <?= e($period['name']) ?>
    <span class="badge fs-6 text-bg-<?= $period['status'] === 'exported' ? 'success' : 'info' ?>"><?= label($period['status']) ?></span>
  </h1>
  <div class="ms-auto d-flex gap-2">
    <?php if (Auth::can('payroll.manage')): ?>
      <?php if ($editable): ?>
        <form method="post" action="<?= url('payroll/' . $period['id'] . '/lock') ?>" data-confirm="Lock this period? Entries can no longer be edited.">
          <?= csrf_field() ?><button class="btn btn-outline-secondary"><i class="bi bi-lock me-1"></i>Lock</button>
        </form>
      <?php endif ?>
      <a href="<?= url('payroll/' . $period['id'] . '/export') ?>" class="btn btn-emerald">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
      </a>
    <?php endif ?>
    <a href="<?= url('payroll') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-sm table-hover align-middle datatable">
      <thead><tr>
        <th>Employee</th><th>Basic</th><th>Allowances</th><th>Gross</th>
        <th>RSSB</th><th>PAYE</th><th>Deductions</th><th>Net</th><?php if ($editable): ?><th></th><?php endif ?>
      </tr></thead>
      <tbody>
      <?php foreach ($entries as $en): ?>
        <?php
        $allowances = $en['transport_allowance'] + $en['telephone_allowance'] + $en['per_diem']
                    + $en['overtime'] + $en['bonus'] + $en['commission'];
        $deductions = $en['loan_deduction'] + $en['advance_deduction'] + $en['other_deduction'];
        ?>
        <tr>
          <td class="fw-semibold"><?= e($en['employee_name']) ?><br><small class="text-muted"><?= e($en['employee_no']) ?></small></td>
          <td><?= rwf($en['basic_salary']) ?></td>
          <td><?= rwf($allowances) ?></td>
          <td class="fw-semibold"><?= rwf($en['gross_salary']) ?></td>
          <td><?= rwf($en['rssb_employee']) ?></td>
          <td><?= rwf($en['paye']) ?></td>
          <td><?= rwf($deductions) ?></td>
          <td class="fw-bold text-success"><?= rwf($en['net_salary']) ?></td>
          <?php if ($editable): ?>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-<?= $en['id'] ?>">
              <i class="bi bi-pencil"></i>
            </button>
          </td>
          <?php endif ?>
        </tr>
        <?php if ($editable): ?>
        <tr class="collapse" id="edit-<?= $en['id'] ?>">
          <td colspan="9" class="bg-body-tertiary">
            <form method="post" action="<?= url('payroll/' . $en['id']) ?>" class="row g-2 p-2">
              <?= csrf_field() ?>
              <?php foreach ([
                  'transport_allowance' => 'Transport', 'telephone_allowance' => 'Telephone',
                  'per_diem' => 'Per Diem', 'overtime' => 'Overtime', 'bonus' => 'Bonus',
                  'commission' => 'Commission', 'loan_deduction' => 'Loan',
                  'advance_deduction' => 'Advance', 'other_deduction' => 'Other Ded.'] as $field => $lbl): ?>
                <div class="col-md-2 col-4">
                  <label class="form-label small mb-0"><?= $lbl ?></label>
                  <input type="number" step="0.01" min="0" name="<?= $field ?>"
                         value="<?= e((string) $en[$field]) ?>" class="form-control form-control-sm">
                </div>
              <?php endforeach ?>
              <div class="col-md-2 col-4 d-flex align-items-end">
                <button class="btn btn-sm btn-ocean w-100">Recalculate</button>
              </div>
            </form>
          </td>
        </tr>
        <?php endif ?>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
