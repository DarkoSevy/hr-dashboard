<?php
use App\Core\Auth;
/** @var array $employee $documents $contracts $leaves $balances $assets $reviews $trainings $discipline  @var ?array $driver */
$initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));
$photo = $employee['photo_path']
    ? url('storage/' . $employee['photo_path']) : null;
$fact = function (string $labelText, ?string $value) {
    echo '<div class="col-md-4 col-sm-6"><small class="text-muted d-block">' . e($labelText) . '</small><span class="fw-semibold">'
       . ($value !== null && $value !== '' ? e($value) : '—') . '</span></div>';
};
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Employee Profile</h1>
  <div class="ms-auto d-flex gap-2">
    <?php if (Auth::can('employees.manage')): ?>
      <a href="<?= url('employees/' . $employee['id'] . '/edit') ?>" class="btn btn-ocean"><i class="bi bi-pencil me-1"></i>Edit</a>
      <form method="post" action="<?= url('employees/' . $employee['id'] . '/delete') ?>" data-confirm="This permanently deletes <?= e($employee['first_name']) ?>'s record and history.">
        <?= csrf_field() ?><button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
    <?php endif ?>
    <a href="<?= url('employees') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body d-flex flex-wrap gap-4 align-items-center">
    <?php if ($photo): ?>
      <img src="<?= e($photo) ?>" class="profile-photo" alt="Photo">
    <?php else: ?>
      <div class="profile-photo-placeholder"><?= e($initials) ?></div>
    <?php endif ?>
    <div>
      <h4 class="mb-1"><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?>
        <small class="text-muted fs-6"><?= e($employee['employee_no']) ?></small></h4>
      <div class="text-muted"><?= e($employee['position'] ?? '—') ?> · <?= e($employee['department'] ?? '—') ?></div>
      <div class="mt-2 d-flex gap-2 flex-wrap">
        <span class="badge badge-status text-bg-<?= $employee['status'] === 'active' ? 'success' : 'secondary' ?>"><?= label($employee['status']) ?></span>
        <span class="badge text-bg-light border"><?= label($employee['employment_type']) ?></span>
        <?php if ($employee['is_driver']): ?><span class="badge text-bg-info">Driver</span><?php endif ?>
      </div>
    </div>
    <div class="ms-auto text-end small text-muted">
      <div><i class="bi bi-telephone me-1"></i><?= e($employee['phone'] ?? '—') ?></div>
      <div><i class="bi bi-envelope me-1"></i><?= e($employee['email'] ?? '—') ?></div>
      <div><i class="bi bi-person-badge me-1"></i>Manager: <?= e($employee['manager_name'] ?? '—') ?></div>
    </div>
  </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
  <?php
  $tabs = ['overview' => 'Overview', 'documents' => 'Documents', 'leave' => 'Leave',
           'assets' => 'Assets', 'performance' => 'Performance', 'training' => 'Training',
           'discipline' => 'Disciplinary'];
  if ($driver) $tabs['driver'] = 'Driver';
  $first = true;
  foreach ($tabs as $key => $title): ?>
    <li class="nav-item"><button class="nav-link <?= $first ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $key ?>"><?= $title ?></button></li>
  <?php $first = false; endforeach ?>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-overview">
    <div class="card"><div class="card-body row g-3">
      <?php
      $fact('National ID', $employee['national_id']);
      $fact('Passport', $employee['passport_no']);
      $fact('Gender', label($employee['gender']));
      $fact('Date of Birth', $employee['date_of_birth']);
      $fact('Marital Status', label($employee['marital_status'] ?? ''));
      $fact('Nationality', $employee['nationality']);
      $fact('Blood Group', $employee['blood_group']);
      $fact('Address', $employee['address']);
      $fact('Emergency Contact', trim(($employee['emergency_name'] ?? '') . ' ' . ($employee['emergency_phone'] ?? '')));
      $fact('Branch', $employee['branch']);
      $fact('Date Hired', $employee['date_hired']);
      $fact('Probation Ends', $employee['probation_end']);
      $fact('Confirmation Date', $employee['confirmation_date']);
      if (Auth::can('payroll.view')) {
          $fact('Basic Salary', rwf($employee['basic_salary']));
          $fact('Bank', trim(($employee['bank_name'] ?? '') . ' ' . ($employee['bank_account'] ?? '')));
          $fact('TIN', $employee['tin_number']);
      }
      $fact('RSSB Number', $employee['rssb_number']);
      $fact('Medical Insurance', trim(($employee['medical_insurer'] ?? '') . ' ' . ($employee['medical_policy_no'] ?? '')));
      $fact('Insurance Expiry', $employee['medical_expiry']);
      ?>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-documents">
    <div class="card"><div class="card-body">
      <?php if (Auth::can('documents.manage')): ?>
        <form method="post" action="<?= url('employees/' . $employee['id'] . '/upload-document') ?>"
              enctype="multipart/form-data" class="row g-2 align-items-end mb-4">
          <?= csrf_field() ?>
          <div class="col-md-3"><label class="form-label small">Type</label>
            <select name="doc_type" class="form-select form-select-sm">
              <?php foreach (['cv','contract','academic_certificate','police_clearance','passport','national_id',
                              'driving_license','medical_certificate','performance_review','insurance','permit','other'] as $t): ?>
                <option value="<?= $t ?>"><?= label($t) ?></option>
              <?php endforeach ?>
            </select></div>
          <div class="col-md-3"><label class="form-label small">Title</label>
            <input name="title" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label small">Expiry</label>
            <input type="date" name="expiry_date" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label small">File</label>
            <input type="file" name="file" class="form-control form-control-sm" required></div>
          <div class="col-md-1"><button class="btn btn-emerald btn-sm w-100"><i class="bi bi-upload"></i></button></div>
        </form>
      <?php endif ?>
      <table class="table table-sm table-hover">
        <thead><tr><th>Type</th><th>Title</th><th>Version</th><th>Expiry</th><th>Uploaded</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($documents as $doc): ?>
          <tr>
            <td><span class="badge text-bg-light border"><?= label($doc['doc_type']) ?></span></td>
            <td><?= e($doc['title']) ?></td>
            <td>v<?= (int) $doc['version'] ?></td>
            <td><?= $doc['expiry_date']
                ? '<span class="badge text-bg-' . (strtotime($doc['expiry_date']) < strtotime('+30 days') ? 'danger' : 'success') . '">' . e($doc['expiry_date']) . '</span>'
                : '—' ?></td>
            <td><small class="text-muted"><?= e($doc['created_at']) ?></small></td>
            <td><a href="<?= url('storage/' . $doc['file_path']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-download"></i></a></td>
          </tr>
        <?php endforeach ?>
        <?php if (!$documents): ?><tr><td colspan="6" class="text-muted small">No documents in the vault yet.</td></tr><?php endif ?>
        </tbody>
      </table>
      <?php if ($contracts): ?>
        <h6 class="mt-4">Contracts</h6>
        <table class="table table-sm">
          <thead><tr><th>Type</th><th>Start</th><th>End</th><th>Salary</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($contracts as $c): ?>
            <tr><td><?= label($c['contract_type']) ?></td><td><?= e($c['start_date']) ?></td>
                <td><?= e($c['end_date'] ?? 'Open-ended') ?></td><td><?= rwf($c['salary']) ?></td>
                <td><span class="badge text-bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>"><?= label($c['status']) ?></span></td></tr>
          <?php endforeach ?>
          </tbody>
        </table>
      <?php endif ?>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-leave">
    <div class="card"><div class="card-body">
      <div class="row g-2 mb-3">
        <?php foreach ($balances as $b): ?>
          <div class="col-md-3 col-6"><div class="border rounded p-2 text-center">
            <div class="fw-bold fs-5"><?= e((string) ($b['entitled'] + $b['carried_over'] - $b['used'])) ?></div>
            <small class="text-muted"><?= e($b['leave_type']) ?> days left</small>
          </div></div>
        <?php endforeach ?>
      </div>
      <table class="table table-sm table-hover">
        <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($leaves as $l): ?>
          <tr><td><?= e($l['leave_type']) ?></td><td><?= e($l['start_date']) ?></td><td><?= e($l['end_date']) ?></td>
              <td><?= e((string) $l['days']) ?></td>
              <td><span class="badge text-bg-<?= match ($l['status']) {
                  'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary', default => 'warning' } ?>"><?= label($l['status']) ?></span></td></tr>
        <?php endforeach ?>
        <?php if (!$leaves): ?><tr><td colspan="5" class="text-muted small">No leave history.</td></tr><?php endif ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-assets">
    <div class="card"><div class="card-body">
      <table class="table table-sm table-hover">
        <thead><tr><th>Tag</th><th>Asset</th><th>Category</th><th>Assigned</th><th>Returned</th></tr></thead>
        <tbody>
        <?php foreach ($assets as $a): ?>
          <tr><td><?= e($a['asset_tag']) ?></td><td><?= e($a['asset_name']) ?></td><td><?= label($a['category']) ?></td>
              <td><?= e($a['assigned_at']) ?></td>
              <td><?= $a['returned_at'] ? e($a['returned_at']) : '<span class="badge text-bg-warning">In use</span>' ?></td></tr>
        <?php endforeach ?>
        <?php if (!$assets): ?><tr><td colspan="5" class="text-muted small">No assets assigned.</td></tr><?php endif ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-performance">
    <div class="card"><div class="card-body">
      <table class="table table-sm table-hover">
        <thead><tr><th>Period</th><th>Type</th><th>KPI Score</th><th>Rating</th><th>Recommendation</th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
          <tr><td><?= e($r['period']) ?></td><td><?= label($r['review_type']) ?></td>
              <td><?= e((string) ($r['kpi_score'] ?? '—')) ?></td>
              <td><?= label($r['overall_rating'] ?? '—') ?></td><td><?= label($r['recommendation']) ?></td></tr>
        <?php endforeach ?>
        <?php if (!$reviews): ?><tr><td colspan="5" class="text-muted small">No reviews yet.</td></tr><?php endif ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-training">
    <div class="card"><div class="card-body">
      <table class="table table-sm table-hover">
        <thead><tr><th>Course</th><th>Date</th><th>Attended</th><th>Score</th><th>Certificate Expiry</th></tr></thead>
        <tbody>
        <?php foreach ($trainings as $t): ?>
          <tr><td><?= e($t['course']) ?></td><td><?= e($t['start_date']) ?></td>
              <td><?= $t['attended'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' ?></td>
              <td><?= e((string) ($t['score'] ?? '—')) ?></td><td><?= e($t['certificate_expiry'] ?? '—') ?></td></tr>
        <?php endforeach ?>
        <?php if (!$trainings): ?><tr><td colspan="5" class="text-muted small">No training records.</td></tr><?php endif ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <div class="tab-pane fade" id="tab-discipline">
    <div class="card"><div class="card-body">
      <?php if ($discipline): ?>
        <div class="timeline">
          <?php foreach ($discipline as $d): ?>
            <div class="timeline-item">
              <div class="fw-semibold"><?= label($d['action']) ?> — <?= e($d['category']) ?></div>
              <small class="text-muted"><?= e($d['case_date']) ?> · <?= label($d['status']) ?></small>
              <div class="small mt-1"><?= e($d['description']) ?></div>
            </div>
          <?php endforeach ?>
        </div>
      <?php else: ?>
        <p class="text-muted small mb-0">Clean record — no disciplinary cases.</p>
      <?php endif ?>
    </div></div>
  </div>

  <?php if ($driver): ?>
  <div class="tab-pane fade" id="tab-driver">
    <div class="card"><div class="card-body row g-3">
      <?php
      $fact('License Number', $driver['license_number']);
      $fact('Categories', $driver['license_categories']);
      $fact('License Expiry', $driver['license_expiry']);
      $fact('Permit Expiry', $driver['permit_expiry']);
      $fact('Medical Exam', $driver['medical_exam_date']);
      $fact('Medical Expiry', $driver['medical_exam_expiry']);
      $fact('Rating', $driver['rating'] . ' / 5');
      $fact('Trips Completed', (string) $driver['trips_completed']);
      $fact('Driver Status', label($driver['status']));
      ?>
    </div></div>
  </div>
  <?php endif ?>
</div>
