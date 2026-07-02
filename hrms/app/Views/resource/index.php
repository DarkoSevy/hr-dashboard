<?php
/** Generic list view for metadata modules.
 * @var string $module @var array $cfg $rows $labels @var bool $canManage */
$fields = $cfg['fields'];
$format = function (string $col, $value) use ($fields, $labels) {
    if ($value === null || $value === '') return '—';
    $field = $fields[$col] ?? null;
    if (!$field) return e((string) $value);
    return match ($field['type']) {
        'relation' => e($labels[$col][$value] ?? ('#' . $value)),
        'checkbox' => $value
            ? '<span class="badge text-bg-success">Yes</span>'
            : '<span class="badge text-bg-secondary">No</span>',
        'select'   => '<span class="badge text-bg-light border">' . e($field['options'][$value] ?? label((string) $value)) . '</span>',
        'decimal'  => e(number_format((float) $value, 2)),
        'file'     => '<a href="' . url('storage/' . $value) . '" target="_blank"><i class="bi bi-paperclip"></i> file</a>',
        default    => e((string) $value),
    };
};
?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0"><i class="bi bi-<?= e($cfg['icon'] ?? 'grid') ?> me-2"></i><?= e($cfg['title']) ?></h1>
  <a href="<?= url($module . '/create') ?>" class="btn btn-emerald ms-auto">
    <i class="bi bi-plus-lg me-1"></i>New
  </a>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle datatable">
      <thead><tr>
        <?php foreach ($cfg['list'] as $col): ?>
          <th><?= e($fields[$col]['label'] ?? label($col)) ?></th>
        <?php endforeach ?>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <?php foreach ($cfg['list'] as $col): ?>
            <td><?= $format($col, $row[$col] ?? null) ?></td>
          <?php endforeach ?>
          <td class="text-end text-nowrap">
            <?php if ($canManage): ?>
              <?php foreach ($cfg['row_actions'] ?? [] as [$act, $icon, $titleText]): ?>
                <form method="post" action="<?= url($module . '/' . $row['id'] . '/' . $act) ?>" class="d-inline"
                      data-confirm="<?= e($titleText) ?> — proceed?">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-emerald" title="<?= e($titleText) ?>"><i class="bi bi-<?= e($icon) ?>"></i></button>
                </form>
              <?php endforeach ?>
              <a href="<?= url($module . '/' . $row['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <form method="post" action="<?= url($module . '/' . $row['id'] . '/delete') ?>" class="d-inline"
                    data-confirm="Delete this <?= e(rtrim($cfg['title'], 's')) ?> record?">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach ?>
      <?php if (!$rows): ?>
        <tr><td colspan="<?= count($cfg['list']) + 1 ?>" class="text-muted small">No records yet.</td></tr>
      <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
