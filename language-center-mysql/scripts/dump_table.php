<?php
require_once __DIR__ . '/../functions/db.php';
header('Content-Type: text/plain; charset=utf-8');
if(PHP_SAPI !== 'cli'){
  $table = $_GET['table'] ?? '';
} else {
  $table = $argv[1] ?? '';
}
if(!$table){
  echo "Usage: dump_table.php?table=<table_name>\n";
  exit;
}
// basic validation: allow only letters, numbers and underscore
if(!preg_match('/^[A-Za-z0-9_]+$/', $table)){
  echo "Invalid table name\n"; exit;
}
$conn = getDbConnection();
if(!mysqli_query($conn, "SHOW TABLES LIKE '".mysqli_real_escape_string($conn,$table)."'")){
  echo "Table check query failed: ".mysqli_error($conn)."\n"; mysqli_close($conn); exit;
}
$res = mysqli_query($conn, "SELECT * FROM `".mysqli_real_escape_string($conn,$table)."` LIMIT 1000");
if(!$res){
  echo "Query failed: ".mysqli_error($conn)."\n"; mysqli_close($conn); exit;
}
$rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
if(empty($rows)){
  echo "No rows in $table\n";
} else {
  // Print a simple table
  $cols = array_keys($rows[0]);
  // header
  echo implode("\t", $cols) . "\n";
  foreach($rows as $r){
    $out = [];
    foreach($cols as $c) $out[] = (string)($r[$c] ?? '');
    echo implode("\t", $out) . "\n";
  }
}
mysqli_close($conn);
