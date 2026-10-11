<?php
$alerts = [];
$toasts = [];
if ($m = session('ok')) {
    $toasts[] = ['ok', 'check-circle', esc($m)];
}
if ($m = session('warn')) {
    $alerts[] = ['warn', 'exclamation-circle', esc($m)];
}
if ($m = session('err')) {
    $alerts[] = ['err', 'exclamation-triangle', esc($m)];
}
if ($errors = session('errors')) {
    $alerts[] = ['err', 'exclamation-triangle', '<strong>Please correct the following:</strong><ul class="mt-1 list-disc pl-5">'
        . implode('', array_map(static fn ($e) => '<li>' . esc($e) . '</li>', (array) $errors)) . '</ul>'];
}
?>
<?php foreach ($alerts as [$type, $icon, $html]): ?>
<div class="flash flash-<?= $type ?>" role="alert">
    <i class="bi bi-<?= $icon ?>"></i>
    <div class="flash-body"><?= $html ?></div>
    <button type="button" class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
</div>
<?php endforeach ?>
<?php if ($toasts): ?>
<div class="toast-stack" role="status">
    <?php foreach ($toasts as [$type, $icon, $html]): ?>
    <div class="flash flash-<?= $type ?>" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.300ms>
        <i class="bi bi-<?= $icon ?>"></i>
        <div class="flash-body"><?= $html ?></div>
        <button type="button" class="flash-close" @click="show = false" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
