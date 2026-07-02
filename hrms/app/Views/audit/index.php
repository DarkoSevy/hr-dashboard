<?php /** @var array $logs */ ?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">Audit Trail</h1>
  <a href="<?= url('audit/export') ?>" class="btn btn-emerald ms-auto">
    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Logs
  </a>
</div>
<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-sm table-hover datatable align-middle">
      <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>Changes</th><th>IP</th><th>Device / Browser</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td class="text-nowrap small"><?= e($log['created_at']) ?></td>
          <td class="fw-semibold"><?= e($log['username'] ?? 'system') ?></td>
          <td><span class="badge text-bg-<?= match (true) {
              str_contains($log['action'], 'delete') => 'danger',
              str_contains($log['action'], 'create') => 'success',
              str_contains($log['action'], 'login') => 'info',
              default => 'secondary' } ?>"><?= label($log['action']) ?></span></td>
          <td class="small"><?= e($log['entity']) ?><?= $log['entity_id'] ? ' #' . e($log['entity_id']) : '' ?></td>
          <td class="small text-muted" style="max-width:340px">
            <?php if ($log['old_values']): ?><div><strong>Old:</strong> <?= e($log['old_values']) ?></div><?php endif ?>
            <?php if ($log['new_values']): ?><div><strong>New:</strong> <?= e($log['new_values']) ?></div><?php endif ?>
          </td>
          <td class="small"><?= e($log['ip_address'] ?? '') ?></td>
          <td class="small text-muted text-truncate" style="max-width:180px" title="<?= e($log['user_agent'] ?? '') ?>">
            <?= e($log['user_agent'] ?? '') ?></td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
