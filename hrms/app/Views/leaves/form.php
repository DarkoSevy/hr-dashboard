<?php /** @var array $types $employees */ ?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Request Leave</h1>
  <a href="<?= url('leaves') ?>" class="btn btn-outline-secondary ms-auto"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body p-4">
        <form method="post" action="<?= url('leaves') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <?php if ($employees): ?>
            <div class="mb-3">
              <label class="form-label">Employee (HR only — leave blank for yourself)</label>
              <select name="employee_id" class="form-select">
                <option value="">Myself</option>
                <?php foreach ($employees as $e): ?>
                  <option value="<?= $e['id'] ?>"><?= e($e['name']) ?></option>
                <?php endforeach ?>
              </select>
            </div>
          <?php endif ?>
          <div class="mb-3">
            <label class="form-label">Leave Type *</label>
            <select name="leave_type_id" class="form-select" required>
              <option value="">Select…</option>
              <?php foreach ($types as $t): ?>
                <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e((string) $t['days_per_year']) ?> days/yr<?= $t['is_paid'] ? '' : ', unpaid' ?>)</option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Start Date *</label>
              <input type="date" name="start_date" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">End Date *</label>
              <input type="date" name="end_date" class="form-control" required></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" rows="3" class="form-control" placeholder="Optional context for your approvers"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Attachment (medical note, invitation… — pdf/jpg/png)</label>
            <input type="file" name="attachment" class="form-control">
          </div>
          <div class="alert alert-light border small">
            <i class="bi bi-info-circle me-1 text-ocean"></i>
            Working days are calculated automatically — weekends and public holidays are excluded.
            Your request goes to your supervisor first, then HR. You'll be notified at each step.
          </div>
          <button class="btn btn-ocean w-100 py-2"><i class="bi bi-send me-1"></i>Submit Request</button>
        </form>
      </div>
    </div>
  </div>
</div>
