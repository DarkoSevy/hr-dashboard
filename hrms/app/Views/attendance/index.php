<?php /** @var array $records $myHistory  @var ?array $mine  @var string $date  @var bool $canView */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Attendance</h1>
  <div class="ms-auto d-flex gap-2">
    <?php if (!$mine || !$mine['clock_in']): ?>
      <form method="post" action="<?= url('attendance/clock-in') ?>" data-gps>
        <?= csrf_field() ?>
        <input type="hidden" name="latitude"><input type="hidden" name="longitude">
        <button class="btn btn-emerald"><i class="bi bi-box-arrow-in-right me-1"></i>Clock In</button>
      </form>
    <?php elseif (!$mine['clock_out']): ?>
      <form method="post" action="<?= url('attendance/clock-out') ?>" data-gps>
        <?= csrf_field() ?>
        <input type="hidden" name="latitude"><input type="hidden" name="longitude">
        <button class="btn btn-ocean"><i class="bi bi-box-arrow-right me-1"></i>Clock Out</button>
      </form>
    <?php else: ?>
      <span class="badge text-bg-success align-self-center py-2 px-3">
        <i class="bi bi-check-circle me-1"></i>Day complete:
        <?= date('H:i', strtotime($mine['clock_in'])) ?> → <?= date('H:i', strtotime($mine['clock_out'])) ?>
      </span>
    <?php endif ?>
  </div>
</div>

<?php if ($mine && $mine['clock_in'] && !$mine['clock_out']): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-clock"></i>
    Clocked in at <strong><?= date('H:i', strtotime($mine['clock_in'])) ?></strong>
    <?= $mine['status'] === 'late' ? '— <span class="text-danger">late by ' . (int) $mine['late_minutes'] . ' min</span>' : '' ?>
  </div>
<?php endif ?>

<div class="row g-3">
  <?php if ($canView): ?>
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex align-items-center">
        Daily Register
        <form method="get" action="<?= url('attendance') ?>" class="ms-auto">
          <input type="date" name="date" value="<?= e($date) ?>" class="form-control form-control-sm"
                 onchange="this.form.submit()">
        </form>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead><tr><th>Employee</th><th>Department</th><th>In</th><th>Out</th><th>Status</th><th>GPS</th></tr></thead>
          <tbody>
          <?php foreach ($records as $r): ?>
            <tr>
              <td class="fw-semibold"><?= e($r['employee_name']) ?><br><small class="text-muted"><?= e($r['employee_no']) ?></small></td>
              <td><?= e($r['department'] ?? '—') ?></td>
              <td><?= $r['clock_in'] ? date('H:i', strtotime($r['clock_in'])) : '—' ?></td>
              <td><?= $r['clock_out'] ? date('H:i', strtotime($r['clock_out'])) : '—' ?></td>
              <td><span class="badge text-bg-<?= match ($r['status']) {
                  'present' => 'success', 'late' => 'warning', 'absent' => 'danger',
                  'on_leave' => 'info', default => 'secondary' } ?>">
                <?= label($r['status']) ?><?= $r['late_minutes'] ? ' +' . $r['late_minutes'] . 'm' : '' ?>
              </span></td>
              <td>
                <?php if ($r['in_latitude']): ?>
                  <a href="https://maps.google.com/?q=<?= e($r['in_latitude']) ?>,<?= e($r['in_longitude']) ?>"
                     target="_blank" class="small"><i class="bi bi-geo-alt"></i></a>
                <?php else: ?>—<?php endif ?>
              </td>
            </tr>
          <?php endforeach ?>
          <?php if (!$records): ?><tr><td colspan="6" class="text-muted small">No records for <?= e($date) ?>.</td></tr><?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif ?>

  <div class="<?= $canView ? 'col-lg-4' : 'col-lg-6' ?>">
    <div class="card">
      <div class="card-header">My Last 30 Days</div>
      <div class="card-body table-responsive">
        <table class="table table-sm">
          <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($myHistory as $h): ?>
            <tr>
              <td><?= e($h['work_date']) ?></td>
              <td><?= $h['clock_in'] ? date('H:i', strtotime($h['clock_in'])) : '—' ?></td>
              <td><?= $h['clock_out'] ? date('H:i', strtotime($h['clock_out'])) : '—' ?></td>
              <td><span class="badge text-bg-<?= $h['status'] === 'late' ? 'warning' : 'success' ?>"><?= label($h['status']) ?></span></td>
            </tr>
          <?php endforeach ?>
          <?php if (!$myHistory): ?><tr><td colspan="4" class="text-muted small">No attendance history yet.</td></tr><?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
