<?php
/** @var string $month $prev $next  @var array $events (Y-m-d → [{type,label}]) */
$firstDay = strtotime($month . '-01');
$daysInMonth = (int) date('t', $firstDay);
$startWeekday = (int) date('N', $firstDay); // 1 = Monday
$today = date('Y-m-d');
?>
<div class="d-flex flex-wrap align-items-center mb-4 gap-2">
  <h1 class="page-title mb-0">HR Calendar — <?= date('F Y', $firstDay) ?></h1>
  <div class="ms-auto btn-group">
    <a href="<?= url('calendar?month=' . $prev) ?>" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <a href="<?= url('calendar') ?>" class="btn btn-outline-secondary">Today</a>
    <a href="<?= url('calendar?month=' . $next) ?>" class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
  </div>
</div>

<div class="mb-3 d-flex gap-3 small">
  <span><span class="cal-event cal-holiday d-inline px-2">Holiday</span></span>
  <span><span class="cal-event cal-leave d-inline px-2">Approved Leave</span></span>
  <span><span class="cal-event cal-training d-inline px-2">Training</span></span>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-bordered cal-table mb-0">
      <thead><tr>
        <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dow): ?>
          <th class="text-center small"><?= $dow ?></th>
        <?php endforeach ?>
      </tr></thead>
      <tbody>
        <tr>
        <?php
        // Leading blanks before the 1st
        for ($i = 1; $i < $startWeekday; $i++) {
            echo '<td class="bg-body-tertiary"></td>';
        }
        $weekday = $startWeekday;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%02d', $month, $day);
            $classes = [];
            if ($date === $today) $classes[] = 'cal-today';
            if ($weekday >= 6) $classes[] = 'bg-body-tertiary';
            echo '<td class="' . implode(' ', $classes) . '">';
            echo '<div class="cal-day">' . $day . '</div>';
            foreach ($events[$date] ?? [] as $ev) {
                echo '<span class="cal-event cal-' . e($ev['type']) . '" title="' . e($ev['label']) . '">'
                   . e($ev['label']) . '</span>';
            }
            echo '</td>';
            if ($weekday === 7 && $day < $daysInMonth) {
                echo '</tr><tr>';
                $weekday = 1;
            } else {
                $weekday++;
            }
        }
        // Trailing blanks
        while ($weekday >= 2 && $weekday <= 7) {
            echo '<td class="bg-body-tertiary"></td>';
            $weekday++;
        }
        ?>
        </tr>
      </tbody>
    </table>
  </div>
</div>
