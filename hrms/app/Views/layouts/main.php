<?php
use App\Core\Auth;
use App\Core\Database;

$user = Auth::user();
$unread = $user ? (int) Database::scalar(
    'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [$user['id']]) : 0;
$currentModule = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'))[0] ?: 'dashboard';

$nav = [
    'Overview' => [
        ['dashboard', 'speedometer2', 'Dashboard', 'dashboard.view'],
        ['notifications', 'bell', 'Notifications', null],
    ],
    'People' => [
        ['employees', 'people-fill', 'Employees', 'employees.view'],
        ['org-chart', 'diagram-2', 'Org Chart', 'employees.view'],
        ['departments', 'diagram-3', 'Departments', 'departments.manage'],
        ['positions', 'person-badge', 'Positions', 'departments.manage'],
        ['branches', 'geo-alt', 'Branches', 'departments.manage'],
        ['contracts', 'file-earmark-text', 'Contracts', 'employees.view'],
        ['documents', 'folder2-open', 'Document Vault', 'documents.view'],
    ],
    'Time & Pay' => [
        ['leaves', 'calendar2-week', 'Leave', 'leaves.request'],
        ['calendar', 'calendar3', 'HR Calendar', 'leaves.request'],
        ['attendance', 'fingerprint', 'Attendance', 'attendance.clock'],
        ['shifts', 'clock-history', 'Shifts', 'attendance.view'],
        ['holidays', 'calendar-heart', 'Holidays', 'leaves.view'],
        ['payroll', 'cash-stack', 'Payroll', 'payroll.view'],
    ],
    'Talent' => [
        ['vacancies', 'megaphone', 'Vacancies', 'recruitment.view'],
        ['applicants', 'people', 'Applicants', 'recruitment.view'],
        ['interviews', 'chat-square-text', 'Interviews', 'recruitment.view'],
        ['offers', 'envelope-check', 'Offers', 'recruitment.view'],
        ['performance', 'graph-up-arrow', 'Performance', 'performance.view'],
        ['goals', 'bullseye', 'Goals & KPIs', 'performance.view'],
        ['courses', 'mortarboard', 'Training Courses', 'training.view'],
        ['training-sessions', 'calendar-event', 'Training Sessions', 'training.view'],
        ['training-participants', 'person-check', 'Participants', 'training.view'],
    ],
    'Fleet' => [
        ['drivers', 'truck-front', 'Drivers', 'drivers.view'],
        ['vehicles', 'car-front', 'Vehicles', 'drivers.view'],
        ['trips', 'signpost', 'Trips', 'drivers.view'],
        ['driver-incidents', 'exclamation-triangle', 'Driver Incidents', 'drivers.view'],
    ],
    'Workplace' => [
        ['requests', 'inbox', 'Internal Requests', 'requests.create'],
        ['assets', 'laptop', 'Assets', 'assets.view'],
        ['asset-assignments', 'box-arrow-right', 'Asset Assignments', 'assets.view'],
        ['disciplinary', 'shield-exclamation', 'Disciplinary', 'disciplinary.view'],
        ['checkups', 'heart-pulse', 'Medical Checkups', 'health.view'],
        ['incidents', 'cone-striped', 'Incident Reports', 'health.view'],
        ['announcements', 'broadcast', 'Announcements', 'dashboard.view'],
    ],
    'Insight & Admin' => [
        ['reports', 'clipboard-data', 'Reports', 'reports.view'],
        ['users', 'person-lock', 'User Accounts', 'users.manage'],
        ['audit', 'journal-text', 'Audit Trail', 'audit.view'],
        ['settings', 'gear', 'Settings', 'settings.manage'],
    ],
];
$flash = flash('');
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PTS HRMS — Premier Transport &amp; Tour Services</title>
<link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="/assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="/assets/vendor/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?= url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="hrms-shell">
  <aside class="hrms-sidebar" id="sidebar">
    <div class="hrms-brand">
      <i class="bi bi-bus-front"></i>
      <div>
        <strong>PTS HRMS</strong>
        <small>Premier Transport &amp; Tours</small>
      </div>
    </div>
    <nav class="hrms-nav">
      <?php foreach ($nav as $group => $items): ?>
        <?php
        $visible = array_filter($items, fn($i) => $i[3] === null || Auth::can($i[3]));
        if (!$visible) continue;
        ?>
        <div class="hrms-nav-group"><?= e($group) ?></div>
        <?php foreach ($visible as [$path, $icon, $labelText]): ?>
          <a href="<?= url($path) ?>" class="hrms-nav-link <?= $currentModule === $path ? 'active' : '' ?>">
            <i class="bi bi-<?= $icon ?>"></i><span><?= e($labelText) ?></span>
            <?php if ($path === 'notifications' && $unread): ?>
              <span class="badge rounded-pill text-bg-danger ms-auto"><?= $unread ?></span>
            <?php endif ?>
          </a>
        <?php endforeach ?>
      <?php endforeach ?>
    </nav>
  </aside>

  <div class="hrms-main">
    <header class="hrms-topbar">
      <button class="btn btn-link d-lg-none p-0 me-2 fs-4" id="sidebarToggle"><i class="bi bi-list"></i></button>
      <div class="hrms-search d-none d-md-block">
        <i class="bi bi-search"></i>
        <input type="search" id="globalSearch" class="form-control form-control-sm"
               placeholder="Search this page… ( / )">
      </div>
      <div class="ms-auto d-flex align-items-center gap-3">
        <button class="btn btn-light btn-sm rounded-circle" id="themeToggle" title="Toggle dark mode">
          <i class="bi bi-moon-stars"></i>
        </button>
        <a href="<?= url('notifications') ?>" class="btn btn-light btn-sm rounded-circle position-relative" title="Notifications">
          <i class="bi bi-bell"></i>
          <?php if ($unread): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread ?></span>
          <?php endif ?>
        </a>
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <span class="hrms-avatar"><?= e(strtoupper(substr($user['username'] ?? 'U', 0, 1))) ?></span>
            <span class="d-none d-sm-inline">
              <?= e($user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['username']) ?>
            </span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted"><?= e($user['role_name'] ?? '') ?></span></li>
            <?php if ($user['emp_id'] ?? null): ?>
              <li><a class="dropdown-item" href="<?= url('employees/' . $user['emp_id']) ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
            <?php endif ?>
            <li><a class="dropdown-item" href="<?= url('leaves') ?>"><i class="bi bi-calendar2-week me-2"></i>My Leave</a></li>
            <li><a class="dropdown-item" href="<?= url('account/payslips') ?>"><i class="bi bi-receipt me-2"></i>My Payslips</a></li>
            <li><a class="dropdown-item" href="<?= url('account') ?>"><i class="bi bi-gear me-2"></i>My Account</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
          </ul>
        </div>
      </div>
    </header>

    <main class="hrms-content">
      <?php if ($flash): ?>
        <div data-flash-type="<?= e($flash['type']) ?>" data-flash-message="<?= e($flash['message']) ?>" hidden></div>
      <?php endif ?>
      <?= $content ?>
    </main>

    <footer class="hrms-footer">
      © <?= date('Y') ?> Premier Transport &amp; Tour Services Ltd — HRMS · Kigali, Rwanda
    </footer>
  </div>
</div>

<script src="/assets/vendor/bootstrap.bundle.min.js"></script>
<script src="/assets/vendor/jquery.min.js"></script>
<script src="/assets/vendor/jquery.dataTables.min.js"></script>
<script src="/assets/vendor/dataTables.bootstrap5.min.js"></script>
<script src="/assets/vendor/chart.umd.min.js"></script>
<script src="/assets/vendor/sweetalert2.all.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
