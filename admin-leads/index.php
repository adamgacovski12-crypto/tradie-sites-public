<?php
/**
 * Tradie Sites Co. — lightweight ops view.
 * Protected by .htaccess Basic Auth (admin / Tsc2026Admin — change on server).
 */
require __DIR__ . '/../signup/_helpers.php';
$cfg = tsc_cfg();

$flash = $_GET['flash'] ?? '';
$search = strtolower(trim((string)($_GET['q'] ?? '')));

$rows = tsc_csv_read_all();
usort($rows, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

if ($search !== '') {
    $rows = array_filter($rows, function($r) use ($search) {
        return
            strpos(strtolower($r['reference'] ?? ''), $search) !== false ||
            strpos(strtolower($r['business_name'] ?? ''), $search) !== false ||
            strpos(strtolower($r['contact_name'] ?? ''), $search) !== false ||
            strpos(strtolower($r['email'] ?? ''), $search) !== false;
    });
}

header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup leads — Tradie Sites Co. Ops</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; background: #111; color: #eee; margin: 0; padding: 28px; }
        h1 { color: #FF6A00; margin: 0 0 8px; font-size: 1.6rem; letter-spacing: 1px; }
        p.lede { color: #aaa; margin: 0 0 24px; }
        .top {
            display: flex; gap: 14px; align-items: center; flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .flash {
            background: #0a4a0a; color: #b7f5b7; padding: 10px 14px; border: 2px solid #2d7a2d;
            margin-bottom: 16px;
        }
        .top input[type=search] {
            background: #222; color: #eee; border: 2px solid #444; padding: 8px 12px;
            font-family: inherit; font-size: .95rem; min-width: 280px;
        }
        .top a.btn, .top button {
            background: #FF6A00; color: #000; border: 2px solid #000;
            padding: 8px 14px; font-weight: 700; text-decoration: none;
            cursor: pointer; font-family: inherit;
        }
        table { width: 100%; border-collapse: collapse; background: #1a1a1a; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #333; text-align: left; vertical-align: top; font-size: .92rem; }
        th { background: #222; color: #FF6A00; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; font-size: .78rem; }
        tr.row-click { cursor: pointer; }
        tr.row-click:hover { background: #222; }
        tr.row-detail { display: none; }
        tr.row-detail.is-open { display: table-row; }
        tr.row-detail td { background: #0d0d0d; padding: 16px 18px; }
        .badge {
            display: inline-block; padding: 3px 10px; font-size: .72rem;
            letter-spacing: 1.5px; text-transform: uppercase; font-weight: 800;
            border: 2px solid;
        }
        .badge.awaiting_payment { color: #FF6A00; border-color: #FF6A00; }
        .badge.paid             { color: #69d67a; border-color: #69d67a; }
        .badge.prepped          { color: #8be9fd; border-color: #8be9fd; }
        .badge.deployed         { color: #bd93f9; border-color: #bd93f9; }
        .badge.cancelled        { color: #888; border-color: #888; }
        .actions form { display: inline; margin: 0; }
        .actions button {
            background: #FF6A00; color: #000; border: 2px solid #000;
            padding: 6px 12px; font-weight: 700; cursor: pointer; font-size: .82rem;
        }
        .actions button.cancel { background: #888; }
        .actions a { color: #FF6A00; font-size: .82rem; text-decoration: underline; }
        .detail-grid { display: grid; grid-template-columns: 180px 1fr; gap: 4px 14px; }
        .detail-grid dt { color: #888; font-size: .82rem; }
        .detail-grid dd { margin: 0; word-break: break-word; }
        .empty { padding: 40px; text-align: center; color: #888; }
        .assets-link { color: #FF6A00; font-size: .82rem; }
    </style>
</head>
<body>

<h1>Signup leads</h1>
<p class="lede">All /signup/ submissions. Mark as paid when Adam confirms bank transfer.</p>

<?php if ($flash !== ''): ?>
<div class="flash"><?= tsc_h($flash) ?></div>
<?php endif; ?>

<form class="top" method="GET" action="">
    <input type="search" name="q" placeholder="Search by reference, business, name or email" value="<?= tsc_h($_GET['q'] ?? '') ?>">
    <button type="submit">Search</button>
    <a class="btn" href="/admin-leads/">Clear</a>
    <span style="color:#666; font-size:.82rem;"><?= count($rows) ?> row<?= count($rows) === 1 ? '' : 's' ?></span>
</form>

<?php if (empty($rows)): ?>
<div class="empty">No signups yet.</div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Date</th><th>Reference</th><th>Business</th><th>Contact</th><th>Trade</th><th>Plan</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($rows as $i => $r):
    $ref = tsc_h($r['reference'] ?? '');
    $status = $r['status'] ?? 'awaiting_payment';
?>
        <tr class="row-click" data-target="detail-<?= $i ?>">
            <td><?= tsc_h(substr($r['date'] ?? '', 0, 16)) ?></td>
            <td><code><?= $ref ?></code></td>
            <td><?= tsc_h($r['business_name'] ?? '') ?></td>
            <td><?= tsc_h($r['contact_name'] ?? '') ?><br><span style="color:#777;font-size:.78rem;"><?= tsc_h($r['phone'] ?? '') ?></span></td>
            <td><?= tsc_h($r['trade'] ?? '') ?></td>
            <td><?= tsc_h($r['plan'] ?? '') ?></td>
            <td><span class="badge <?= tsc_h($status) ?>"><?= tsc_h(str_replace('_', ' ', $status)) ?></span></td>
            <td class="actions">
<?php if ($status === 'awaiting_payment'): ?>
                <form method="POST" action="/admin-leads/action.php" onsubmit="return confirm('Mark <?= $ref ?> as PAID and email the customer?');">
                    <input type="hidden" name="reference" value="<?= $ref ?>">
                    <input type="hidden" name="action" value="mark_paid">
                    <button type="submit">Mark paid</button>
                </form>
                <form method="POST" action="/admin-leads/action.php" onsubmit="return confirm('Cancel <?= $ref ?>?');">
                    <input type="hidden" name="reference" value="<?= $ref ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="cancel">Cancel</button>
                </form>
<?php elseif ($status === 'paid'): ?>
                <form method="POST" action="/admin-leads/action.php" onsubmit="return confirm('Prep build folder + CLAUDE_BUILD_PROMPT.md for <?= $ref ?>?');">
                    <input type="hidden" name="reference" value="<?= $ref ?>">
                    <input type="hidden" name="action" value="prep_build">
                    <button type="submit">Prep build</button>
                </form>
<?php elseif ($status === 'prepped' || $status === 'deployed'): ?>
                <a href="/admin-leads/action.php?action=view_build&reference=<?= $ref ?>" target="_blank">View build</a>
                <form method="POST" action="/admin-leads/action.php" style="display:inline;margin-left:6px;">
                    <input type="hidden" name="reference" value="<?= $ref ?>">
                    <input type="hidden" name="action" value="download_zip">
                    <button type="submit">Download ZIP</button>
                </form>
<?php if ($status === 'prepped'): ?>
                <form method="POST" action="/admin-leads/action.php" onsubmit="var u=prompt('Live URL (include https://):', 'https://'); if(!u)return false; this.live_url.value=u; return true;" style="display:inline;margin-left:6px;">
                    <input type="hidden" name="reference" value="<?= $ref ?>">
                    <input type="hidden" name="action" value="mark_deployed">
                    <input type="hidden" name="live_url" value="">
                    <button type="submit">Mark deployed</button>
                </form>
<?php endif; ?>
<?php else: ?>
                <span style="color:#666;font-size:.82rem;">—</span>
<?php endif; ?>
            </td>
        </tr>
        <tr class="row-detail" id="detail-<?= $i ?>">
            <td colspan="8">
                <dl class="detail-grid">
                    <dt>Email</dt><dd><?= tsc_h($r['email'] ?? '') ?></dd>
                    <dt>ABN</dt><dd><?= tsc_h($r['abn'] ?? '') ?></dd>
                    <dt>Suburbs</dt><dd><?= tsc_h($r['suburbs'] ?? '') ?></dd>
                    <dt>Licence</dt><dd><?= tsc_h($r['licence'] ?? '') ?: '—' ?></dd>
                    <dt>Tagline</dt><dd><?= tsc_h($r['tagline'] ?? '') ?: '—' ?></dd>
                    <dt>Services</dt><dd><?= tsc_h($r['services'] ?? '') ?: '—' ?></dd>
                    <dt>Years</dt><dd><?= tsc_h($r['years'] ?? '') ?: '—' ?></dd>
                    <dt>Existing site</dt><dd><?= tsc_h($r['existing_website'] ?? '') ?: '—' ?></dd>
                    <dt>Existing FB</dt><dd><?= tsc_h($r['existing_fb'] ?? '') ?: '—' ?></dd>
                    <dt>Logo</dt><dd><?= $r['logo_path'] ? tsc_h($r['logo_path']) : '—' ?></dd>
                    <dt>Photos</dt><dd><?= $r['photo_paths'] ? nl2br(tsc_h(str_replace(';', "\n", $r['photo_paths']))) : '—' ?></dd>
                    <dt>Payment confirmed</dt><dd><?= tsc_h($r['payment_confirmed_date'] ?? '') ?: '—' ?></dd>
<?php if (!empty($r['live_url'])): ?>
                    <dt>Live URL</dt><dd><a href="<?= tsc_h($r['live_url']) ?>" target="_blank" rel="noopener"><?= tsc_h($r['live_url']) ?></a></dd>
                    <dt>Deployed</dt><dd><?= tsc_h($r['deployed_date'] ?? '') ?: '—' ?></dd>
<?php endif; ?>
                    <dt>JSON record</dt><dd>/signups/records/<?= $ref ?>.json <span style="color:#666">(blocked from web)</span></dd>
                    <dt>Build folder</dt><dd>/builds/<?= $ref ?>/ <span style="color:#666">(after prep; gitignored)</span></dd>
<?php if (!empty($r['logo_path']) || !empty($r['photo_paths'])): ?>
                    <dt>Uploads</dt><dd><a class="assets-link" href="/admin-leads/action.php?action=list_uploads&reference=<?= $ref ?>">View uploaded files</a></dd>
<?php endif; ?>
                </dl>
            </td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<script>
document.querySelectorAll('tr.row-click').forEach(row => {
    row.addEventListener('click', e => {
        if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) return;
        const target = document.getElementById(row.dataset.target);
        if (target) target.classList.toggle('is-open');
    });
});
</script>

</body>
</html>
