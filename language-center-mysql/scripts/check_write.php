<?php
header('Content-Type: text/plain; charset=utf-8');
$paths = [
  __DIR__ . '/post_log.txt',
  dirname(__DIR__) . '/post_log_backup.txt'
];
foreach($paths as $p){
  $ok = @file_put_contents($p, "[".date('Y-m-d H:i:s')."] TEST WRITE\n", FILE_APPEND | LOCK_EX);
  if($ok === false){
    echo "FAILED to write to: $p\n";
  } else {
    echo "WROTE to: $p (bytes: $ok)\n";
  }
}

echo "\nPermissions and stat:\n";
foreach($paths as $p){
  if(file_exists($p)){
    $s = stat($p);
    echo "$p -> size={$s['size']} mtime=".date('Y-m-d H:i:s',$s['mtime'])." mode=".substr(sprintf('%o',$s['mode']), -4)."\n";
  } else echo "$p -> (missing)\n";
}
