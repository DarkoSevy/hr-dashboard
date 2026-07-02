<?php /** @var array $reports $rows  @var ?string $active  @var ?array $report */ ?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">HR Reports</h1>
  <?php if ($active): ?>
    <a href="<?= url('reports/export?report=' . urlencode($active)) ?>" class="btn btn-emerald ms-auto">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
    </a>
  <?php endif ?>
</div>

<div class="row g-3">
  <div class="col-lg-3">
    <div class="card">
      <div class="card-header">Available Reports</div>
      <div class="list-group list-group-flush">
        <?php foreach ($reports as $key => $title): ?>
          <a href="<?= url('reports?report=' . urlencode($key)) ?>"
             class="list-group-item list-group-item-action <?= $active === $key ? 'active' : '' ?>">
            <?= e($title) ?>
          </a>
        <?php endforeach ?>
      </div>
    </div>
  </div>
  <div class="col-lg-9">
    <div class="card">
      <div class="card-header"><?= $report ? e($report['title']) : 'Select a report' ?></div>
      <div class="card-body table-responsive">
        <?php if ($report === null): ?>
          <p class="text-muted small mb-0">Choose a report from the list. Every report can be exported to CSV
            (opens in Excel) for onward analysis or sharing.</p>
        <?php elseif (!$rows): ?>
          <p class="text-muted small mb-0">No data for this report yet.</p>
        <?php else: ?>
          <table class="table table-sm table-hover datatable">
            <thead><tr>
              <?php foreach (array_keys($rows[0]) as $col): ?><th><?= e($col) ?></th><?php endforeach ?>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
              <tr><?php foreach ($row as $cell): ?><td><?= e((string) $cell) ?></td><?php endforeach ?></tr>
            <?php endforeach ?>
            </tbody>
          </table>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
