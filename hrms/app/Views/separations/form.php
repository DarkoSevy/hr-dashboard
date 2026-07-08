<?php /** @var array|null $separation $prefill $employees */ ?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0"><?= $separation ? 'Edit Separation Record' : 'Process Employee Separation' ?></h1>
  <a href="<?= url('separations') ?>" class="btn btn-outline-secondary ms-auto">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-4">
        <form method="post" action="<?= $separation ? url('separations/' . $separation['id']) : url('separations') ?>">
          <?= csrf_field() ?>

          <!-- Employee -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Employee *</label>
            <?php if ($separation): ?>
              <input type="hidden" name="employee_id" value="<?= $separation['employee_id'] ?>">
              <input class="form-control bg-light"
                     value="<?= e($separation['employee_name'] . ' (' . $separation['employee_no'] . ')') ?>" readonly>
            <?php else: ?>
              <select name="employee_id" class="form-select" required>
                <option value="">Select employee…</option>
                <?php foreach ($employees as $e): ?>
                  <option value="<?= $e['id'] ?>"
                    <?= (($prefill['id'] ?? 0) == $e['id'] || ($_POST['employee_id'] ?? '') == $e['id']) ? 'selected' : '' ?>>
                    <?= e($e['name']) ?>
                  </option>
                <?php endforeach ?>
              </select>
            <?php endif ?>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Separation Type *</label>
              <select name="separation_type" class="form-select" required>
                <option value="">Select…</option>
                <?php foreach ([
                    'resignation'  => 'Resignation',
                    'retirement'   => 'Retirement',
                    'dismissal'    => 'Dismissal',
                    'redundancy'   => 'Redundancy',
                    'contract_end' => 'Contract End',
                    'deceased'     => 'Deceased',
                    'other'        => 'Other',
                ] as $v => $l): ?>
                  <option value="<?= $v ?>"
                    <?= (($separation['separation_type'] ?? '') === $v) ? 'selected' : '' ?>>
                    <?= $l ?>
                  </option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Effective Date *</label>
              <input type="date" name="effective_date" class="form-control" required
                     value="<?= e($separation['effective_date'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Notice Given Date</label>
              <input type="date" name="notice_date" class="form-control"
                     value="<?= e($separation['notice_date'] ?? '') ?>">
              <div class="form-text">Date employee or employer gave formal notice.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Clearance Status</label>
              <select name="clearance_status" class="form-select">
                <option value="pending"     <?= (($separation['clearance_status'] ?? 'pending') === 'pending')     ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= (($separation['clearance_status'] ?? '') === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="completed"   <?= (($separation['clearance_status'] ?? '') === 'completed')   ? 'selected' : '' ?>>Completed</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Reason</label>
            <textarea name="reason" rows="3" class="form-control"
                      placeholder="Voluntary resignation, poor performance, organisational restructure…"><?= e($separation['reason'] ?? '') ?></textarea>
          </div>

          <!-- Exit interview -->
          <div class="card bg-light border-0 p-3 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2 text-ocean"></i>Exit Interview</h6>
            <div class="mb-3">
              <label class="form-label">Interview Date</label>
              <input type="date" name="exit_interview_date" class="form-control"
                     value="<?= e($separation['exit_interview_date'] ?? '') ?>">
            </div>
            <div>
              <label class="form-label">Interview Notes</label>
              <textarea name="exit_interview_notes" rows="4" class="form-control"
                        placeholder="Key feedback, reasons for leaving, suggestions…"><?= e($separation['exit_interview_notes'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Final pay -->
          <div class="card bg-light border-0 p-3 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2 text-ocean"></i>Final Pay</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Final Pay Amount (RWF)</label>
                <input type="number" name="final_pay_amount" class="form-control" step="0.01" min="0"
                       value="<?= e($separation['final_pay_amount'] ?? '') ?>" placeholder="0.00">
              </div>
              <div class="col-md-6">
                <label class="form-label">Final Pay Date</label>
                <input type="date" name="final_pay_date" class="form-control"
                       value="<?= e($separation['final_pay_date'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="rehire_eligible" id="rehire"
                     <?= ($separation['rehire_eligible'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="rehire">
                <strong>Eligible for rehire</strong> — employee may be considered for future positions
              </label>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Internal Notes</label>
            <textarea name="notes" rows="2" class="form-control"
                      placeholder="Handover notes, outstanding items, HR observations…"><?= e($separation['notes'] ?? '') ?></textarea>
          </div>

          <?php if (!$separation): ?>
            <div class="alert alert-warning border small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              <strong>This action will change the employee's status</strong> in the system
              (resigned / retired / terminated). Payroll access and leave requests will be affected.
              Ensure all clearance steps are completed before setting status to <em>completed</em>.
            </div>
          <?php endif ?>

          <div class="d-flex gap-2 justify-content-end">
            <a href="<?= url('separations') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-ocean px-4">
              <i class="bi bi-check-lg me-1"></i><?= $separation ? 'Update Record' : 'Process Separation' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
