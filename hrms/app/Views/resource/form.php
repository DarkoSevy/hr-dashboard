<?php
/** Generic create/edit form for metadata modules.
 * @var string $module @var array $cfg $options @var ?array $row */
$edit = $row !== null;
$action = $edit ? url($module . '/' . $row['id']) : url($module);
$hasFile = (bool) array_filter($cfg['fields'], fn($f) => $f['type'] === 'file');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0"><?= $edit ? 'Edit' : 'New' ?> — <?= e($cfg['title']) ?></h1>
  <a href="<?= url($module) ?>" class="btn btn-outline-secondary ms-auto"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body p-4">
        <form method="post" action="<?= $action ?>" <?= $hasFile ? 'enctype="multipart/form-data"' : '' ?>>
          <?= csrf_field() ?>
          <div class="row g-3">
            <?php foreach ($cfg['fields'] as $col => $field): ?>
              <?php
              $value = $row[$col] ?? '';
              $req = ($field['required'] ?? false) ? 'required' : '';
              $wide = in_array($field['type'], ['textarea'], true);
              ?>
              <div class="<?= $wide ? 'col-12' : 'col-md-6' ?>">
                <?php if ($field['type'] === 'checkbox'): ?>
                  <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="<?= $col ?>" id="f-<?= $col ?>"
                           <?= ($edit ? $value : 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="f-<?= $col ?>"><?= e($field['label']) ?></label>
                  </div>
                <?php else: ?>
                  <label class="form-label"><?= e($field['label']) ?><?= $req ? ' *' : '' ?></label>
                  <?php switch ($field['type']): case 'textarea': ?>
                    <textarea name="<?= $col ?>" rows="3" class="form-control" <?= $req ?>><?= e((string) $value) ?></textarea>
                  <?php break; case 'select': ?>
                    <select name="<?= $col ?>" class="form-select" <?= $req ?>>
                      <?php if (!$req): ?><option value="">—</option><?php endif ?>
                      <?php foreach ($field['options'] as $optVal => $optLabel): ?>
                        <option value="<?= e((string) $optVal) ?>" <?= (string) $value === (string) $optVal ? 'selected' : '' ?>>
                          <?= e($optLabel) ?></option>
                      <?php endforeach ?>
                    </select>
                  <?php break; case 'relation': ?>
                    <select name="<?= $col ?>" class="form-select" <?= $req ?>>
                      <option value="">—</option>
                      <?php foreach ($options[$col] ?? [] as $optVal => $optLabel): ?>
                        <option value="<?= e((string) $optVal) ?>" <?= (string) $value === (string) $optVal ? 'selected' : '' ?>>
                          <?= e((string) $optLabel) ?></option>
                      <?php endforeach ?>
                    </select>
                  <?php break; case 'file': ?>
                    <input type="file" name="<?= $col ?>" class="form-control">
                    <?php if ($edit && $value): ?>
                      <small class="text-muted">Current: <a href="<?= url('storage/' . $value) ?>" target="_blank">view file</a>
                        — uploading replaces it.</small>
                    <?php endif ?>
                  <?php break; case 'password': ?>
                    <input type="password" name="<?= $col ?>" class="form-control" autocomplete="new-password">
                  <?php break; default: ?>
                    <?php $type = match ($field['type']) {
                        'decimal' => 'number', 'datetime' => 'datetime-local', default => $field['type'] }; ?>
                    <input type="<?= $type ?>" name="<?= $col ?>" class="form-control" <?= $req ?>
                           <?= $field['type'] === 'decimal' ? 'step="0.01"' : '' ?>
                           value="<?= e(str_replace(' ', 'T', (string) $value)) ?>">
                  <?php endswitch ?>
                <?php endif ?>
              </div>
            <?php endforeach ?>
          </div>
          <div class="text-end mt-4">
            <button class="btn btn-ocean px-4"><i class="bi bi-check-lg me-1"></i><?= $edit ? 'Save Changes' : 'Create' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
