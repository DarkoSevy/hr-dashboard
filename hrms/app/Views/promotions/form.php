<?php /** @var array|null $promotion $prefill $employees $positions $departments $managers */ ?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0"><?= $promotion ? 'Edit Promotion Record' : 'Record Promotion / Career Move' ?></h1>
  <a href="<?= url('promotions') ?>" class="btn btn-outline-secondary ms-auto">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-4">
        <form method="post" action="<?= $promotion ? url('promotions/' . $promotion['id']) : url('promotions') ?>">
          <?= csrf_field() ?>

          <!-- Employee -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Employee *</label>
            <?php if ($promotion): ?>
              <input type="hidden" name="employee_id" value="<?= $promotion['employee_id'] ?>">
              <input class="form-control bg-light" value="<?= e($promotion['employee_name'] . ' (' . $promotion['employee_no'] . ')') ?>" readonly>
            <?php else: ?>
              <select name="employee_id" class="form-select" required id="employeeSelect">
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
              <label class="form-label fw-semibold">Type *</label>
              <select name="promotion_type" class="form-select" required>
                <?php foreach (['promotion' => 'Promotion', 'lateral_transfer' => 'Lateral Transfer',
                                'acting' => 'Acting / Temporary', 'demotion' => 'Demotion'] as $v => $l): ?>
                  <option value="<?= $v ?>" <?= (($promotion['promotion_type'] ?? 'promotion') === $v) ? 'selected' : '' ?>>
                    <?= $l ?>
                  </option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Effective Date *</label>
              <input type="date" name="effective_date" class="form-control" required
                     value="<?= e($promotion['effective_date'] ?? '') ?>">
            </div>
          </div>

          <!-- From / To positions -->
          <div class="card bg-light border-0 p-3 mb-4">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="fw-bold text-muted small text-uppercase mb-2">FROM</p>
                <div class="mb-3">
                  <label class="form-label">Current Position</label>
                  <select name="old_position_id" class="form-select">
                    <option value="">— none / unknown —</option>
                    <?php foreach ($positions as $pos): ?>
                      <option value="<?= $pos['id'] ?>"
                        <?= (($promotion['old_position_id'] ?? '') == $pos['id']) ? 'selected' : '' ?>>
                        <?= e($pos['title']) ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label">Current Department</label>
                  <select name="old_department_id" class="form-select">
                    <option value="">— none / unknown —</option>
                    <?php foreach ($departments as $dep): ?>
                      <option value="<?= $dep['id'] ?>"
                        <?= (($promotion['old_department_id'] ?? '') == $dep['id']) ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div>
                  <label class="form-label">Current Salary (RWF)</label>
                  <input type="number" name="old_salary" class="form-control" step="0.01" min="0"
                         value="<?= e($promotion['old_salary'] ?? '') ?>" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-6">
                <p class="fw-bold text-emerald small text-uppercase mb-2">TO</p>
                <div class="mb-3">
                  <label class="form-label">New Position *</label>
                  <select name="new_position_id" class="form-select" required>
                    <option value="">Select…</option>
                    <?php foreach ($positions as $pos): ?>
                      <option value="<?= $pos['id'] ?>"
                        <?= (($promotion['new_position_id'] ?? '') == $pos['id']) ? 'selected' : '' ?>>
                        <?= e($pos['title']) ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label">New Department <small class="text-muted">(if changing)</small></label>
                  <select name="new_department_id" class="form-select">
                    <option value="">— same as before —</option>
                    <?php foreach ($departments as $dep): ?>
                      <option value="<?= $dep['id'] ?>"
                        <?= (($promotion['new_department_id'] ?? '') == $dep['id']) ? 'selected' : '' ?>>
                        <?= e($dep['name']) ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                </div>
                <div>
                  <label class="form-label">New Salary (RWF)</label>
                  <input type="number" name="new_salary" class="form-control" step="0.01" min="0"
                         value="<?= e($promotion['new_salary'] ?? '') ?>" placeholder="0.00">
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Reason / Justification</label>
            <textarea name="reason" rows="3" class="form-control"
                      placeholder="Performance achievement, restructuring, business need…"><?= e($promotion['reason'] ?? '') ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Approved By</label>
            <select name="approved_by" class="form-select">
              <option value="">— not specified —</option>
              <?php foreach ($managers as $m): ?>
                <option value="<?= $m['id'] ?>"
                  <?= (($promotion['approved_by'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                  <?= e($m['name']) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <div class="alert alert-light border small">
            <i class="bi bi-info-circle me-1 text-ocean"></i>
            Saving this record will immediately update the employee's position, department and salary
            in their profile. A full history of all career moves is always accessible here.
          </div>

          <div class="d-flex gap-2 justify-content-end">
            <a href="<?= url('promotions') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-emerald px-4">
              <i class="bi bi-check-lg me-1"></i><?= $promotion ? 'Update Record' : 'Save Promotion' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
