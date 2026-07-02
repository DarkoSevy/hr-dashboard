<?php
/** @var array $cards @var array $charts @var array $upcoming */
$statCards = [
    ['total_employees', 'Total Employees', 'people-fill', 'bg-ocean'],
    ['drivers', 'Drivers', 'truck-front', 'bg-emerald'],
    ['office_staff', 'Office Staff', 'building', 'bg-ocean'],
    ['attendance_today', 'Present Today', 'fingerprint', 'bg-emerald'],
    ['late_today', 'Late Today', 'alarm', 'bg-sunset'],
    ['on_leave_today', 'On Leave Today', 'calendar2-x', 'bg-sunset'],
    ['pending_leave', 'Pending Leave', 'hourglass-split', 'bg-ocean'],
    ['birthdays_month', 'Birthdays This Month', 'cake2', 'bg-emerald'],
    ['new_this_month', 'New Hires (Month)', 'person-plus', 'bg-ocean'],
    ['open_vacancies', 'Open Vacancies', 'megaphone', 'bg-emerald'],
    ['contracts_expiring', 'Contracts Expiring 60d', 'file-earmark-x', 'bg-sunset'],
    ['licenses_expiring', 'Licenses Expiring 60d', 'card-heading', 'bg-sunset'],
];
$chartData = fn(array $rows) => e(json_encode([
    'labels' => array_map(fn($r) => label((string) $r['label']), array_column($rows, 'label') ? $rows : []),
    'values' => array_map('floatval', array_column($rows, 'value')),
]));
?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <div>
    <h1 class="page-title mb-0">Executive Dashboard</h1>
    <small class="text-muted"><?= date('l, d F Y') ?> — Kigali</small>
  </div>
</div>

<div class="row g-3 mb-4">
  <?php foreach ($statCards as [$key, $labelText, $icon, $bg]): ?>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center gap-3 py-3">
          <div class="stat-icon <?= $bg ?>"><i class="bi bi-<?= $icon ?>"></i></div>
          <div>
            <div class="stat-value"><?= (int) $cards[$key] ?></div>
            <div class="stat-label"><?= e($labelText) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Department Distribution</div>
      <div class="card-body" style="height:280px">
        <canvas data-chart="<?= $chartData($charts['departments']) ?>" data-type="bar"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card h-100">
      <div class="card-header">Gender Distribution</div>
      <div class="card-body" style="height:280px">
        <canvas data-chart="<?= $chartData($charts['gender']) ?>" data-type="doughnut"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card h-100">
      <div class="card-header">Age Distribution</div>
      <div class="card-body" style="height:280px">
        <canvas data-chart="<?= $chartData($charts['age']) ?>" data-type="doughnut"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Employee Growth (12 months)</div>
      <div class="card-body" style="height:240px">
        <canvas data-chart="<?= $chartData($charts['growth']) ?>" data-type="line"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Attendance Trend (14 days)</div>
      <div class="card-body" style="height:240px">
        <canvas data-chart="<?= $chartData($charts['attendance_trend']) ?>" data-type="line"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">Recruitment Funnel</div>
      <div class="card-body" style="height:240px">
        <canvas data-chart="<?= $chartData($charts['recruitment_funnel']) ?>" data-type="bar"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4 col-md-6">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-cake2 me-2 text-warning"></i>Upcoming Birthdays</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($upcoming['birthdays'] ?: [] as $b): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= e($b['name']) ?></span><span class="text-muted"><?= e($b['day']) ?></span>
          </li>
        <?php endforeach ?>
        <?php if (!$upcoming['birthdays']): ?>
          <li class="list-group-item text-muted small">No birthdays in the next 30 days.</li>
        <?php endif ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-file-earmark-x me-2 text-danger"></i>Expiring Contracts &amp; Licenses</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($upcoming['expiring_contracts'] as $c): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= e($c['name']) ?> <small class="text-muted">(contract)</small></span>
            <span class="badge text-bg-warning"><?= e($c['end_date']) ?></span>
          </li>
        <?php endforeach ?>
        <?php foreach ($upcoming['expiring_licenses'] as $c): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span><?= e($c['name']) ?> <small class="text-muted">(license)</small></span>
            <span class="badge text-bg-danger"><?= e($c['end_date']) ?></span>
          </li>
        <?php endforeach ?>
        <?php if (!$upcoming['expiring_contracts'] && !$upcoming['expiring_licenses']): ?>
          <li class="list-group-item text-muted small">Nothing expiring soon. 🎉</li>
        <?php endif ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-hourglass-split me-2 text-ocean"></i>Pending Leave Approvals</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($upcoming['pending_leaves'] as $l): ?>
          <li class="list-group-item">
            <div class="d-flex justify-content-between">
              <a href="<?= url('leaves') ?>" class="text-decoration-none"><?= e($l['name']) ?></a>
              <span class="badge text-bg-secondary"><?= label($l['status']) ?></span>
            </div>
            <small class="text-muted"><?= e($l['leave_type']) ?> · <?= e($l['start_date']) ?> · <?= e((string) $l['days']) ?> day(s)</small>
          </li>
        <?php endforeach ?>
        <?php if (!$upcoming['pending_leaves']): ?>
          <li class="list-group-item text-muted small">No pending requests.</li>
        <?php endif ?>
      </ul>
    </div>
  </div>
</div>
