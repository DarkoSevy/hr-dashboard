<?php
/** @var array $departments  @var array $children  (manager_id → [employees]) */
$renderNode = function (array $emp) use (&$renderNode, $children) {
    $reports = $children[(int) $emp['id']] ?? [];
    ?>
    <li>
      <div class="org-node">
        <a href="<?= url('employees/' . $emp['id']) ?>" class="text-decoration-none">
          <div class="fw-semibold"><?= e($emp['name']) ?></div>
        </a>
        <small class="text-muted d-block"><?= e($emp['position'] ?? '—') ?></small>
        <small class="text-muted"><?= e($emp['department'] ?? '') ?></small>
        <?php if ($reports): ?>
          <span class="badge rounded-pill text-bg-light border ms-1"><?= count($reports) ?> report<?= count($reports) > 1 ? 's' : '' ?></span>
        <?php endif ?>
      </div>
      <?php if ($reports): ?>
        <ul>
          <?php foreach ($reports as $report) { $renderNode($report); } ?>
        </ul>
      <?php endif ?>
    </li>
    <?php
};
?>
<h1 class="page-title mb-4">Organization Chart</h1>

<div class="card mb-4">
  <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>Reporting Structure</div>
  <div class="card-body org-tree-wrap">
    <?php if (empty($children[0])): ?>
      <p class="text-muted small mb-0">No employees without a manager found — assign top-level
        managers on employee records to build the tree.</p>
    <?php else: ?>
      <ul class="org-tree">
        <?php foreach ($children[0] as $root) { $renderNode($root); } ?>
      </ul>
    <?php endif ?>
  </div>
</div>

<div class="card">
  <div class="card-header"><i class="bi bi-building me-2"></i>Departments</div>
  <div class="card-body">
    <div class="row g-3">
      <?php foreach ($departments as $dept): ?>
        <div class="col-lg-4 col-md-6">
          <div class="border rounded p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
              <strong class="text-ocean"><?= e($dept['name']) ?></strong>
              <span class="badge text-bg-light border"><?= e($dept['code']) ?></span>
            </div>
            <div class="small text-muted mt-1">
              <i class="bi bi-person-badge me-1"></i>
              Manager: <?= e($dept['manager_name'] ?? 'Unassigned') ?>
            </div>
            <div class="small text-muted">
              <i class="bi bi-geo-alt me-1"></i><?= e($dept['branch'] ?? '—') ?>
            </div>
            <div class="mt-2">
              <span class="badge text-bg-primary"><?= (int) $dept['headcount'] ?> staff</span>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
</div>
