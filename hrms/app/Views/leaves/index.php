<?php
use App\Core\Auth;
/** @var array $leaves $balances  @var bool $canApproveSup $canApproveHr */
?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Leave Management</h1>
  <?php if (Auth::can('leaves.request')): ?>
    <a href="<?= url('leaves/create') ?>" class="btn btn-emerald ms-auto"><i class="bi bi-plus-lg me-1"></i>Request Leave</a>
  <?php endif ?>
</div>

<?php if ($balances): ?>
  <div class="row g-2 mb-4">
    <?php foreach ($balances as $b): ?>
      <div class="col-lg-2 col-md-3 col-6">
        <div class="card"><div class="card-body py-2 text-center">
          <div class="fw-bold fs-4 text-ocean"><?= e((string) ($b['entitled'] + $b['carried_over'] - $b['used'])) ?></div>
          <small class="text-muted"><?= e($b['leave_type']) ?> left</small>
        </div></div>
      </div>
    <?php endforeach ?>
  </div>
<?php endif ?>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle datatable">
      <thead><tr>
        <th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($leaves as $l): ?>
        <tr>
          <td class="fw-semibold"><?= e($l['employee_name']) ?><br><small class="text-muted"><?= e($l['employee_no']) ?></small></td>
          <td><?= e($l['leave_type']) ?></td>
          <td><?= e($l['start_date']) ?></td>
          <td><?= e($l['end_date']) ?></td>
          <td><?= e((string) $l['days']) ?></td>
          <td class="small text-muted" style="max-width:200px"><?= e($l['reason'] ?? '') ?></td>
          <td>
            <span class="badge badge-status text-bg-<?= match ($l['status']) {
                'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary',
                'pending_hr' => 'info', default => 'warning' } ?>">
              <?= label($l['status']) ?>
            </span>
            <?php if ($l['supervisor_comment'] || $l['hr_comment']): ?>
              <i class="bi bi-chat-left-text ms-1 text-muted" title="<?= e(trim(($l['supervisor_comment'] ?? '') . ' ' . ($l['hr_comment'] ?? ''))) ?>"></i>
            <?php endif ?>
          </td>
          <td class="text-end text-nowrap">
            <?php
            $canDecide = ($l['status'] === 'pending_supervisor' && $canApproveSup)
                      || ($l['status'] === 'pending_hr' && $canApproveHr);
            ?>
            <?php if ($canDecide): ?>
              <form method="post" action="<?= url('leaves/' . $l['id'] . '/approve') ?>" class="d-inline">
                <?= csrf_field() ?><button class="btn btn-sm btn-emerald" title="Approve"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="post" action="<?= url('leaves/' . $l['id'] . '/reject') ?>" class="d-inline" data-confirm="Reject this leave request?">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
              </form>
            <?php elseif (in_array($l['status'], ['pending_supervisor', 'pending_hr'], true)
                          && Auth::employeeId() === (int) $l['employee_id']): ?>
              <form method="post" action="<?= url('leaves/' . $l['id'] . '/cancel') ?>" class="d-inline" data-confirm="Cancel your leave request?">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary">Cancel</button>
              </form>
            <?php endif ?>
            <?php if ($l['attachment_path']): ?>
              <a href="<?= url('storage/' . $l['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Attachment"><i class="bi bi-paperclip"></i></a>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
