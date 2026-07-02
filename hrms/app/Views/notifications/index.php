<?php /** @var array $notifications */ ?>
<h1 class="page-title mb-4">Notifications</h1>
<div class="card">
  <?php if (!$notifications): ?>
    <div class="card-body text-center text-muted py-5">
      <i class="bi bi-bell-slash display-5 d-block mb-2"></i>
      You're all caught up.
    </div>
  <?php else: ?>
    <ul class="list-group list-group-flush">
      <?php foreach ($notifications as $n): ?>
        <li class="list-group-item d-flex gap-3 py-3 <?= $n['is_read'] ? '' : 'bg-body-tertiary' ?>">
          <i class="bi bi-<?= match (true) {
              str_contains($n['type'], 'leave') => 'calendar2-week text-ocean',
              str_contains($n['type'], 'expir') => 'exclamation-triangle text-warning',
              default => 'info-circle text-secondary' } ?> fs-4"></i>
          <div class="flex-grow-1">
            <div class="fw-semibold"><?= e($n['title']) ?></div>
            <div class="small text-muted"><?= e($n['body'] ?? '') ?></div>
            <?php if ($n['link']): ?>
              <a href="<?= e($n['link']) ?>" class="small">Open →</a>
            <?php endif ?>
          </div>
          <small class="text-muted text-nowrap"><?= e($n['created_at']) ?></small>
        </li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</div>
