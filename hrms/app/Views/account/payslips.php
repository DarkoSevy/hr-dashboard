<?php /** @var array $entries  @var bool $linked */ ?>
<h1 class="page-title mb-4">My Payslips</h1>

<div class="card">
  <div class="card-body table-responsive">
    <?php if (!$linked): ?>
      <p class="text-muted small mb-0">Your account is not linked to an employee record — contact HR.</p>
    <?php elseif (!$entries): ?>
      <p class="text-muted small mb-0">No payslips published yet. Payslips appear here once
        Finance locks the payroll period.</p>
    <?php else: ?>
      <table class="table table-hover align-middle">
        <thead><tr><th>Period</th><th>Gross</th><th>RSSB</th><th>PAYE</th><th>Other Deductions</th><th>Net Pay</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($entries as $en): ?>
          <tr>
            <td class="fw-semibold"><?= e($en['period_name']) ?></td>
            <td><?= rwf($en['gross_salary']) ?></td>
            <td><?= rwf($en['rssb_employee']) ?></td>
            <td><?= rwf($en['paye']) ?></td>
            <td><?= rwf($en['loan_deduction'] + $en['advance_deduction'] + $en['other_deduction']) ?></td>
            <td class="fw-bold text-success"><?= rwf($en['net_salary']) ?></td>
            <td class="text-end">
              <a href="<?= url('account/payslip/' . $en['id']) ?>" target="_blank" class="btn btn-sm btn-ocean">
                <i class="bi bi-printer me-1"></i>View / Print
              </a>
            </td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    <?php endif ?>
  </div>
</div>
