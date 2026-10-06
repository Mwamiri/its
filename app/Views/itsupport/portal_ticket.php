<?= view('itsupport/_header') ?>
<?php
$steps = ['new' => 'Received', 'in_progress' => 'In progress', 'waiting_parts' => 'Waiting parts', 'waiting_approval' => 'Waiting approval', 'completed' => 'Resolved', 'closed' => 'Closed'];
?>
<div class="card"><h2><?= esc($ticket['ticket_number']) ?>: <?= esc($ticket['subject']) ?></h2>
<p><span class="badge blue"><?= esc($steps[$ticket['status']] ?? $ticket['status']) ?></span> Priority: <?= esc($ticket['priority']) ?> · Opened <?= esc(substr((string) $ticket['created_at'], 0, 10)) ?></p>
<?php if (!empty($ticket['due_at']) && !in_array($ticket['status'], ['completed', 'closed'], true)) { ?><p class="muted">Target resolution: <?= esc(substr((string) $ticket['due_at'], 0, 16)) ?></p><?php } ?>
<p><?= nl2br(esc($ticket['description'])) ?></p></div>
<?php if (in_array($ticket['status'], ['completed', 'closed'], true)) { ?><div class="card"><h3>How did we do?</h3>
<?php if (!empty($ticket['rating'])) { ?><p>You rated this <strong><?= (int) $ticket['rating'] ?>/5</strong> <?= str_repeat('★', (int) $ticket['rating']) ?><?= $ticket['rating_comment'] ? ' – ' . esc($ticket['rating_comment']) : '' ?></p>
<?php } else { ?><form method="post" action="<?= base_url('its-portal-rate/' . $ticket['id']) ?>"><?= csrf_field() ?>
<label for="rating">Rating</label><select id="rating" name="rating" required><option value="">Choose</option><?php foreach ([5 => 'Excellent', 4 => 'Good', 3 => 'Okay', 2 => 'Poor', 1 => 'Very poor'] as $v => $l) { ?><option value="<?= $v ?>"><?= $v ?> - <?= $l ?></option><?php } ?></select>
<label for="rc">Comment (optional)</label><textarea id="rc" name="comment" maxlength="500"></textarea><button class="btn">Send feedback</button></form><?php } ?></div><?php } ?>
<?php if ($tasks) { ?><div class="card"><h3>Work progress</h3><ul>
<?php foreach ($tasks as $t) { ?><li><strong><?= esc($t['item']) ?></strong> <span class="badge gray"><?= esc(str_replace('_', ' ', $t['status'])) ?></span><?= $t['action_taken'] ? ' – ' . esc($t['action_taken']) : '' ?></li><?php } ?></ul></div><?php } ?>
<div class="card"><h3>Conversation</h3>
<?php foreach ($updates as $u) { ?><p><strong><?= esc($u['author_name']) ?></strong> <span class="muted"><?= $u['author_role'] === 'client' ? 'you' : 'support' ?> · <?= esc($u['created_at']) ?></span><br><?= nl2br(esc($u['message'])) ?></p><?php } ?>
<?php if (!$updates) { ?><p class="muted">No messages yet.</p><?php } ?>
<form method="post" action="<?= base_url('its-portal-reply/' . $ticket['id']) ?>"><?= csrf_field() ?>
<label for="message">Add a follow-up</label><textarea id="message" name="message" maxlength="3000" required></textarea><button class="btn">Send</button></form></div>
<?= view('itsupport/_footer') ?>
