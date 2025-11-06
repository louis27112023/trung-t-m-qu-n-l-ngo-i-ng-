<?php
// Simple DB check page for local development
require_once __DIR__ . '/../functions/db.php';
header('Content-Type: text/html; charset=utf-8');
try{
    $conn = getDbConnection();
} catch(Exception $e){
    echo '<h3>Không thể kết nối database</h3>';
    echo '<pre>'.htmlspecialchars($e->getMessage()).'</pre>';
    exit;
}
if(!$conn){
    echo '<h3>Kết nối trả về false</h3>'; exit;
}
$db = null;
$res = @mysqli_query($conn, "SELECT DATABASE() as db");
if($res){ $r = mysqli_fetch_assoc($res); $db = $r['db'] ?? null; }
?>
<!doctype html>
<html lang="vi">
<head><meta charset="utf-8"><title>DB Check</title></head>
<body>
  <h2>DB Check</h2>
  <p><strong>Connected DB:</strong> <?= htmlspecialchars($db) ?></p>
  <p><strong>Host:</strong> <?= htmlspecialchars(mysqli_get_host_info($conn)) ?></p>
  <p><strong>Last MySQL error:</strong> <?= htmlspecialchars(mysqli_error($conn)) ?></p>
  <h3>Tables</h3>
  <ul>
<?php
$tables = [];
$tr = @mysqli_query($conn, "SHOW TABLES");
if($tr){ while($row = mysqli_fetch_row($tr)){ $tables[] = $row[0]; } }
foreach($tables as $t){
    $cnt = 'N/A';
    $cres = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM `".mysqli_real_escape_string($conn,$t)."`");
    if($cres){ $ca = mysqli_fetch_assoc($cres); $cnt = $ca['c']; }
    echo '<li>'.htmlspecialchars($t).' — <strong>'.$cnt.'</strong> rows</li>';
}
if(empty($tables)) echo '<li><em>Không tìm thấy bảng nào</em></li>';
?>
  </ul>
  <h3>Quick checks</h3>
  <ul>
    <li>courses table present? <?= in_array('courses',$tables) ? '<strong>Yes</strong>' : '<strong>No</strong>' ?></li>
    <li>classes table present? <?= in_array('classes',$tables) ? '<strong>Yes</strong>' : '<strong>No</strong>' ?></li>
    <li>students table present? <?= in_array('students',$tables) ? '<strong>Yes</strong>' : '<strong>No</strong>' ?></li>
    <li>teachers table present? <?= in_array('teachers',$tables) ? '<strong>Yes</strong>' : '<strong>No</strong>' ?></li>
  </ul>
  <p>Đóng kết nối.</p>
</body>
</html>
<?php mysqli_close($conn); ?>