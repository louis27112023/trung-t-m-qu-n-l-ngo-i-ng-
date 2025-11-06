<?php
header('Content-Type: text/html; charset=utf-8');
$candidates = [
  __DIR__ . '/post_log.txt',            // scripts/post_log.txt
  __DIR__ . '/../scripts/post_log.txt', // fallback: root/scripts/post_log.txt (same)
  __DIR__ . '/../post_log_backup.txt'   // project root backup log
];
$log = null;
foreach($candidates as $p){ if(file_exists($p)){ $log = $p; break; } }
$created_note = '';
if(!$log){
  // try to create the file in the primary location without truncating (open in append)
  $try = $candidates[0];
  $fh = @fopen($try, 'a');
  if($fh){ fclose($fh); $created_note = "(created $try)"; $log = $try; }
}

if(!$log){
  echo '<p>No post_log.txt found in expected locations. Checked:<ul>';
  foreach($candidates as $p) echo '<li>'.htmlspecialchars($p).'</li>';
  echo '</ul>Please submit a form or check file permissions. You can also create an empty file named <code>post_log.txt</code> inside the <code>scripts</code> folder.</p>';
  exit;
}
$lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$meta = @stat($log);
$lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$meta = @stat($log);
$last = array_slice($lines, -200); // show last 200 lines
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Post Log</title></head><body>
<h2>Last post_log entries (file: <?= htmlspecialchars($log) ?>)</h2>
<?php if(!empty($meta)): ?>
  <div style="font-size:0.9rem;color:#666;margin-bottom:8px;">File size: <?= htmlspecialchars(number_format($meta['size'])) ?> bytes — Modified: <?= htmlspecialchars(date('Y-m-d H:i:s',$meta['mtime'])) ?> — Perms: <?= substr(sprintf('%o',$meta['mode']), -4) ?></div>
<?php endif; ?>
<pre style="white-space:pre-wrap;"><?= htmlspecialchars(implode("\n", $last)) ?></pre>
</body></html>