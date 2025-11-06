<?php
require_once __DIR__ . '/functions/db.php';
$require_once = null;
$require_once = null;
require_once __DIR__ . '/functions/util.php';
require_once __DIR__ . '/functions/auth.php';
$conn = getDbConnection();

// start session for flash messages
if(session_status() === PHP_SESSION_NONE) session_start();

// simple flash helpers
function set_flash($msg){ $_SESSION['flash'] = $msg; }
function get_flash(){ $m = $_SESSION['flash'] ?? null; if(isset($_SESSION['flash'])) unset($_SESSION['flash']); return $m; }

// CSRF helpers
function csrf_token(){
  if(empty($_SESSION['csrf_token'])){
    if(function_exists('random_bytes')) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    else $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
  }
  return $_SESSION['csrf_token'];
}
function verify_csrf($t){ return isset($_SESSION['csrf_token']) && is_string($t) && hash_equals($_SESSION['csrf_token'],$t); }

// Generic table helpers
function table_exists_conn($conn, $tbl){
  $tbl = mysqli_real_escape_string($conn,$tbl);
  $r = @mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");
  return $r && mysqli_num_rows($r) > 0;
}
function find_table_for_candidates($conn, array $cands){
  foreach($cands as $c){ if(table_exists_conn($conn,$c)) return $c; }
  return null;
}
function get_table_columns($conn,$tbl){
  if(!table_exists_conn($conn,$tbl)) return [];
  $res = mysqli_query($conn, "SHOW COLUMNS FROM `".mysqli_real_escape_string($conn,$tbl)."`");
  $cols = [];
  while($r = mysqli_fetch_assoc($res)) $cols[] = $r;
  return $cols;
}
function get_primary_key_from_cols($cols){
  foreach($cols as $c){ if(!empty($c['Key']) && strtoupper($c['Key']) === 'PRI') return $c['Field']; }
  // fallback
  return 'id';
}
function get_table_rows($conn,$tbl,$limit=500){
  if(!table_exists_conn($conn,$tbl)) return [];
  // Try to detect primary key column so we can ORDER BY it; fallback to no ORDER BY
  $pk = null;
  $colsRes = false;
  $colsRes = @mysqli_query($conn, "SHOW COLUMNS FROM `".mysqli_real_escape_string($conn,$tbl)."`");
  if($colsRes){
    while($c = mysqli_fetch_assoc($colsRes)){
      if(!empty($c['Key']) && strtoupper($c['Key']) === 'PRI'){
        $pk = $c['Field']; break;
      }
    }
    mysqli_free_result($colsRes);
  }
  $tbl_esc = mysqli_real_escape_string($conn,$tbl);
  if($pk){
    $pk_esc = mysqli_real_escape_string($conn,$pk);
    $sql = "SELECT * FROM `{$tbl_esc}` ORDER BY `{$pk_esc}` DESC LIMIT " . intval($limit);
  } else {
    $sql = "SELECT * FROM `{$tbl_esc}` LIMIT " . intval($limit);
  }
  $res = mysqli_query($conn, $sql);
  return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

// DB error logger (append to post_log.txt) — local debug only
function log_db_error($conn, $context, $sql = null){
  try{
    $path = __DIR__ . '/scripts/post_log.txt';
    $msg = "[".date('Y-m-d H:i:s')."] DB ERROR ({$context}): ".(mysqli_error($conn) ?: 'no error')."\n";
    if($sql) $msg .= "SQL: ". $sql ."\n";
    file_put_contents($path, $msg, FILE_APPEND | LOCK_EX);
  }catch(Throwable $e){}
}

// DB success logger (append to post_log.txt) — local debug only
function log_db_success($conn, $context, $sql = null, $stmt = null){
  try{
    $path = __DIR__ . '/scripts/post_log.txt';
    $affected = null;
    if($stmt) $affected = mysqli_stmt_affected_rows($stmt);
    else $affected = mysqli_affected_rows($conn);
    $insertId = mysqli_insert_id($conn);
    $msg = "[".date('Y-m-d H:i:s')."] DB OK ({$context}): affected=".($affected===null? 'null': $affected)." insert_id=".$insertId."\n";
    if($sql) $msg .= "SQL: ". $sql ."\n";
    file_put_contents($path, $msg, FILE_APPEND | LOCK_EX);
  }catch(Throwable $e){}
}

// Handle POST actions (scaffolding for future DB operations)
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';
  // Lightweight POST logging for debugging (local only)
  try{
    $logPath = __DIR__ . '/scripts/post_log.txt';
    $entry = "[".date('Y-m-d H:i:s')."] POST received: action=".($action ?: 'none')."\n";
    $entry .= "POST: ".print_r($_POST, true)."\n";
    // write primary log
    @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
    // also write a backup log in project root in case scripts/ is not writable
    @file_put_contents(__DIR__ . '/post_log_backup.txt', $entry, FILE_APPEND | LOCK_EX);
  }catch(Throwable $e){}
  // validate CSRF token for POST actions
  $token = $_POST['csrf_token'] ?? '';
  if(!verify_csrf($token)){
    set_flash('Yêu cầu không hợp lệ (CSRF).');
    try{ file_put_contents(__DIR__ . '/scripts/post_log.txt', "[".date('Y-m-d H:i:s')."] CSRF failed for action={$action}\nPOST:".print_r($_POST,true)."\n", FILE_APPEND | LOCK_EX); }catch(Throwable $e){}
    header('Location: index.php?page=courses'); exit;
  }
  // Login POST handled here
  if($action === 'login'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if($username === '' || $password === ''){ set_flash('Vui lòng nhập tên đăng nhập và mật khẩu.'); header('Location: index.php?page=login'); exit; }
    $user = verify_admin_credentials($conn, $username, $password);
    if($user){
      // set minimal session user
      $_SESSION['user'] = [ 'id' => $user['id'], 'username' => $user['username'], 'name' => $user['name'] ?? $user['username'], 'role' => $user['role'] ?? 'admin' ];
      set_flash('Đăng nhập thành công.');
      header('Location: index.php?page=dashboard'); exit;
    } else {
      set_flash('Tên đăng nhập hoặc mật khẩu không đúng.'); header('Location: index.php?page=login'); exit;
    }
  }
  if($action === 'add_course'){
    // expected fields: name, level, price, duration, status
    $name = trim($_POST['name'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $price = $_POST['price'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $status = trim($_POST['status'] ?? 'active');

    if($name === ''){
      set_flash('Tên khóa học không được để trống.');
      header('Location: index.php?page=courses'); exit;
    }

    // attempt insert using prepared statement if table exists
    $tblRes = @mysqli_query($conn, "SHOW TABLES LIKE 'courses'");
    if($tblRes && mysqli_num_rows($tblRes) > 0){
      $sql = "INSERT INTO `courses` (`name`,`level`,`price`,`duration`,`status`) VALUES (?,?,?,?,?)";
      $stmt = mysqli_prepare($conn, $sql);
      if($stmt){
        // normalize numeric fields
        $price_val = is_numeric($price) ? $price : 0;
        $duration_val = is_numeric($duration) ? intval($duration) : 0;
        mysqli_stmt_bind_param($stmt, 'ssdds', $name, $level, $price_val, $duration_val, $status);
        $ok = mysqli_stmt_execute($stmt);
          if($ok){ set_flash('Thêm khóa học thành công.'); log_db_success($conn,'add_course',$sql,$stmt); }
          else { set_flash('Lỗi khi thêm khóa học.'); log_db_error($conn,'add_course',$sql); }
        mysqli_stmt_close($stmt);
      } else {
        set_flash('Không thể chuẩn bị truy vấn thêm khóa học.'); log_db_error($conn,'prepare_add_course',$sql ?? null);
      }
    } else {
      set_flash('Bảng `courses` chưa tồn tại trong cơ sở dữ liệu. Tạo bảng trước khi thêm.');
    }
    header('Location: index.php?page=courses'); exit;
  }
  elseif($action === 'edit_course'){
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $price = $_POST['price'] ?? null;
    $duration = $_POST['duration'] ?? null;
    $status = trim($_POST['status'] ?? 'active');

    if($id <= 0 || $name === ''){
      set_flash('Dữ liệu không hợp lệ.'); header('Location: index.php?page=courses'); exit;
    }
    $tblRes = @mysqli_query($conn, "SHOW TABLES LIKE 'courses'");
    if($tblRes && mysqli_num_rows($tblRes) > 0){
      $sql = "UPDATE `courses` SET `name` = ?, `level` = ?, `price` = ?, `duration` = ?, `status` = ? WHERE `id` = ?";
      $stmt = mysqli_prepare($conn, $sql);
      if($stmt){
        $price_val = is_numeric($price) ? $price : 0;
        $duration_val = is_numeric($duration) ? intval($duration) : 0;
        mysqli_stmt_bind_param($stmt, 'ssdisi', $name, $level, $price_val, $duration_val, $status, $id);
        $ok = mysqli_stmt_execute($stmt);
          if($ok){ set_flash('Cập nhật khóa học thành công.'); log_db_success($conn,'edit_course',$sql,$stmt); }
          else { set_flash('Lỗi khi cập nhật.'); log_db_error($conn,'edit_course',$sql); }
        mysqli_stmt_close($stmt);
      } else { set_flash('Không thể chuẩn bị truy vấn cập nhật.'); log_db_error($conn,'prepare_edit_course',$sql ?? null); }
    } else {
      set_flash('Bảng `courses` chưa tồn tại.');
    }
    header('Location: index.php?page=courses'); exit;
  }
  elseif($action === 'delete_course'){
    $id = intval($_POST['id'] ?? 0);
    if($id <= 0){ set_flash('ID không hợp lệ.'); header('Location: index.php?page=courses'); exit; }
    $tblRes = @mysqli_query($conn, "SHOW TABLES LIKE 'courses'");
    if($tblRes && mysqli_num_rows($tblRes) > 0){
      $sql = "DELETE FROM `courses` WHERE `id` = ?";
      $stmt = mysqli_prepare($conn, $sql);
      if($stmt){ mysqli_stmt_bind_param($stmt,'i',$id); $ok = mysqli_stmt_execute($stmt); if($ok) set_flash('Xóa khóa học thành công.'); else { set_flash('Lỗi khi xóa.'); log_db_error($conn,'delete_course',$sql); } mysqli_stmt_close($stmt); }
      else { set_flash('Không thể chuẩn bị truy vấn xóa.'); log_db_error($conn,'prepare_delete_course',$sql ?? null); }
    } else set_flash('Bảng `courses` chưa tồn tại.');
    header('Location: index.php?page=courses'); exit;
  }
  else {
    // generic handlers for add/edit/delete on entities: schedule/payment(s)/settings
    if(preg_match('/^(add|edit|delete)_(.+)$/', $action, $m)){
      $verb = $m[1]; $entityKey = $m[2];
      $map = [
        'schedule'=>['schedule','schedules','timetable'], 'schedules'=>['schedule','schedules','timetable'],
        'payment'=>['payments','payment','transactions'], 'payments'=>['payments','payment','transactions'],
        'settings'=>['settings','config','options'],
        // Add mappings for classes, students, teachers so generic CRUD handlers work
        'classes'=>['classes','class','classrooms'],
        'students'=>['students','student','learners','pupils'],
        'teachers'=>['teachers','teacher','instructors','staff']
      ];
      if(!isset($map[$entityKey])){ set_flash('Hành động không được hỗ trợ.'); header('Location: index.php'); exit; }
      $tbl = find_table_for_candidates($conn, $map[$entityKey]);
      if(!$tbl){ set_flash('Bảng cho thực thể chưa tồn tại: '.htmlspecialchars($entityKey)); header('Location: index.php'); exit; }

      $cols = get_table_columns($conn,$tbl);
      $pk = get_primary_key_from_cols($cols);
      // build field list (exclude auto_increment PK)
      $fields = [];
      foreach($cols as $c){
        if(!empty($c['Extra']) && strpos($c['Extra'],'auto_increment')!==false) continue;
        $fname = $c['Field'];
        // Only include fields that were actually submitted in POST to avoid missing NOT NULL columns
        if(array_key_exists($fname, $_POST)) $fields[] = $fname;
      }

      if($verb === 'add'){
        // build insert
        if(count($fields) === 0){ set_flash('Không có trường để chèn.'); header('Location: index.php?page='.$entityKey); exit; }
        $placeholders = implode(',', array_fill(0,count($fields),'?'));
        $sql = "INSERT INTO `".mysqli_real_escape_string($conn,$tbl)."` (`".implode('`,`',$fields)."`) VALUES ($placeholders)";
        $stmt = mysqli_prepare($conn,$sql);
        if($stmt){
          $types = str_repeat('s',count($fields));
          $values = [];
          foreach($fields as $f) $values[] = $_POST[$f] ?? null;
          $params = array_merge([$types], $values);
          // bind params by reference
          $refs = [];
          foreach($params as $k => $v) $refs[$k] = &$params[$k];
          call_user_func_array(array($stmt,'bind_param'), $refs);
          $ok = mysqli_stmt_execute($stmt);
          if($ok){ set_flash('Thêm thành công.'); log_db_success($conn,'generic_add_'.$tbl,$sql,$stmt); }
          else { set_flash('Lỗi khi thêm.'); log_db_error($conn,'generic_add_'.$tbl,$sql); }
          mysqli_stmt_close($stmt);
        } else { set_flash('Không thể chuẩn bị truy vấn thêm.'); log_db_error($conn,'prepare_generic_add_'.$tbl,$sql); }
        header('Location: index.php?page='.$entityKey); exit;
      }
      elseif($verb === 'edit'){
        $id = $_POST[$pk] ?? ($_POST['id'] ?? null);
        if(!$id){ set_flash('ID không hợp lệ.'); header('Location: index.php?page='.$entityKey); exit; }
        $setParts = [];
        $values = [];
        foreach($fields as $f){ $setParts[] = "`$f` = ?"; $values[] = $_POST[$f] ?? null; }
        $sql = "UPDATE `".mysqli_real_escape_string($conn,$tbl)."` SET ".implode(',',$setParts)." WHERE `".mysqli_real_escape_string($conn,$pk)."` = ?";
        $stmt = mysqli_prepare($conn,$sql);
        if($stmt){
          // determine bind types: default strings for fields, but use integer for PK if numeric
          $lastType = is_numeric($id) ? 'i' : 's';
          $types = str_repeat('s',count($values)) . $lastType;
          $params = array_merge([$types], $values, [ $id ]);
          $refs = [];
          foreach($params as $k=>$v) $refs[$k] = &$params[$k];
          call_user_func_array(array($stmt,'bind_param'), $refs);
          $ok = mysqli_stmt_execute($stmt);
          if($ok){ set_flash('Cập nhật thành công.'); log_db_success($conn,'generic_edit_'.$tbl,$sql,$stmt); }
          else { set_flash('Lỗi khi cập nhật.'); log_db_error($conn,'generic_edit_'.$tbl,$sql); }
          mysqli_stmt_close($stmt);
        } else { set_flash('Không thể chuẩn bị truy vấn cập nhật.'); log_db_error($conn,'prepare_generic_edit_'.$tbl,$sql); }
        header('Location: index.php?page='.$entityKey); exit;
      }
      elseif($verb === 'delete'){
        $id = $_POST['id'] ?? null;
        if(!$id){ set_flash('ID không hợp lệ.'); header('Location: index.php?page='.$entityKey); exit; }
        $stmt = mysqli_prepare($conn, "DELETE FROM `".mysqli_real_escape_string($conn,$tbl)."` WHERE `".mysqli_real_escape_string($conn,$pk)."` = ?");
        if($stmt){
          $bindType = is_numeric($id) ? 'i' : 's';
          mysqli_stmt_bind_param($stmt,$bindType,$id);
          $ok = mysqli_stmt_execute($stmt);
          if($ok){ set_flash('Xóa thành công.'); log_db_success($conn,'generic_delete_'.$tbl,$sql,$stmt); }
          else { set_flash('Lỗi khi xóa.'); log_db_error($conn,'generic_delete_'.$tbl,$sql); }
          mysqli_stmt_close($stmt);
        }
        else { set_flash('Không thể chuẩn bị truy vấn xóa.'); log_db_error($conn,'prepare_generic_delete_'.$tbl,$sql); }
        header('Location: index.php?page='.$entityKey); exit;
      }
    }
  }
}

// Chỉ giữ lại các trang chính, nhưng cho phép trang login riêng
$page = $_GET['page'] ?? 'dashboard';
$allowed = ['dashboard','courses','schedule','payments','settings','login','classes','students','teachers'];
if(!in_array($page,$allowed)) $page='dashboard';

// Tạm thời bỏ kiểm tra đăng nhập
/*
if(empty($_SESSION['user']) && $page !== 'login'){
  header('Location: index.php?page=login'); exit;
}

if($page === 'logout'){
  session_destroy();
  header('Location: index.php?page=login'); exit;
}
*/
//$page already validated above
//$q for search
$q = strtolower($_GET['q'] ?? '');
function lc_filter($rows,$q){ if(!$q) return $rows; return array_values(array_filter($rows,function($r) use($q){return strpos(strtolower(json_encode($r,JSON_UNESCAPED_UNICODE)),$q)!==false;})); }
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Language Center - Quản trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/new-main.css">
    <link rel="stylesheet" href="css/login-new.css">
    <link rel="stylesheet" href="css/settings-new.css">
    <link rel="stylesheet" href="css/dashboard-enhancements.css">
    <link rel="stylesheet" href="css/layout.css">
    <style>
    /* Critical CSS fixes */
    .header { height: 48px !important; }
    .header-inner { height: 48px !important; }
    .content { margin-top: 48px !important; padding: 0 !important; }
    .container-fluid { padding: 12px !important; }
    .dashboard-hero { margin-top: -48px !important; }
    </style>
</head>
<body <?php if($page === 'login') echo 'class="login-page"'; ?>>
  <div class="bg-animated" aria-hidden="true">
    <div class="blob a"></div>
    <div class="blob b"></div>
    <div class="blob c"></div>
  </div>
  <!-- welcome-banner removed to declutter header; keep animated blobs only -->
<?php if($page !== 'login'): ?>
  <div class="app-wrapper">
    <?php require_once __DIR__ . '/views/partials/header.php'; ?>
    <?php require_once __DIR__ . '/views/partials/sidebar.php'; ?>
<?php endif; ?>

    <!-- Main Content -->
<?php if($page !== 'login'): ?>
    <main class="main-content">
      <div class="container-fluid">
<?php else: ?>
    <main class="main-content login-main">
      <div class="container-fluid">
<?php endif; ?>
                <?php
                // Flash messages
                if($flash = get_flash()){
                    echo '<div class="alert alert-'.($flash==='Đăng nhập thành công.'?'success':'danger').'">';
                    echo htmlspecialchars($flash);
                    echo '</div>';
                }

                // Load the appropriate view
                $view_file = __DIR__ . '/views/' . $page . '.php';
                if(file_exists($view_file)) {
                    require_once $view_file;
                } else {
                    echo '<div class="alert alert-danger">Trang không tồn tại.</div>';
                }
                ?>
            </div>
  </main>
<?php if($page !== 'login'): ?>
    </div>
  </div>
<?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/sidebar.js"></script>
    <style>
      /* Sidebar toggle animations */
      .sidebar-toggle {
          transition: transform 0.3s ease;
      }
      .sidebar-toggle.rotate {
          transform: rotate(180deg);
      }
    
      /* Mobile sidebar overlay */
    @media (max-width: 768px) {
      body.sidebar-open::after {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.18);
        z-index: 30;
      }
    }
    </style>
<script>
      // restore state
      // Ensure globals used by sidebar scripts are defined to avoid console ReferenceError
      var STORAGE_KEY = 'lc_sidebar_collapsed';
      var body = document.body;
      var toggle = document.querySelector('.sidebar-toggle');

      try{
        if(localStorage.getItem(STORAGE_KEY) === '1') body.classList.add('sidebar-collapsed');
      }catch(e){ /* ignore localStorage errors */ }

      if(toggle){
        toggle.addEventListener('click', function(e){
          e.preventDefault();
          body.classList.toggle('sidebar-collapsed');
          var collapsed = body.classList.contains('sidebar-collapsed') ? '1' : '0';
          try{ localStorage.setItem(STORAGE_KEY, collapsed); }catch(err){}
        });
      }
      // small-screen: allow opening sidebar
      var mediaQuery = window.matchMedia('(max-width:900px)');
      function handleResize(){ if(mediaQuery.matches) document.body.classList.remove('sidebar-collapsed'); }
      mediaQuery.addEventListener && mediaQuery.addEventListener('change', handleResize);
      
      // adjust layout dynamically: set content top and sidebar top to header height
      function adjustLayout(){
        try{
              var header = document.querySelector('header');
              var content = document.querySelector('.main-content');
              var sidebar = document.querySelector('.sidebar');
              if(header){
                var hh = header.offsetHeight || 64; // compute from header height
                if(content) {
                  content.style.marginTop = hh + 'px';
                  content.style.paddingTop = '0';
                }
                if(sidebar) sidebar.style.top = hh + 'px';
              }
          
          // Adjust dashboard hero if present
          var hero = document.querySelector('.dashboard-hero');
          if(hero) {
            hero.style.marginTop = (-hh) + 'px';
          }
          
          // move any modal elements to document.body
          var modals = Array.from(document.querySelectorAll('.modal'));
          modals.forEach(function(m){ if(m && m.parentNode !== document.body) document.body.appendChild(m); });
        }catch(e){console.error(e)}
      }
      window.addEventListener('resize', adjustLayout);
      document.addEventListener('DOMContentLoaded', adjustLayout);
      setTimeout(adjustLayout,300);
    
  </script>
  <script>
    // Ensure mobile sidebar overlay doesn't block modals: when a modal opens, temporarily remove sidebar-open
    (function(){
      var body = document.body;
      var prevSidebarOpen = false;
      document.addEventListener('show.bs.modal', function(e){
        try{
          prevSidebarOpen = body.classList.contains('sidebar-open');
          if(prevSidebarOpen) body.classList.remove('sidebar-open');
          // If multiple backdrops present, remove duplicates leaving only one
          var b = document.querySelectorAll('.modal-backdrop');
          if(b && b.length>1){
            for(var i=0;i<b.length-1;i++) b[i].parentNode && b[i].parentNode.removeChild(b[i]);
          }
          var last = document.querySelector('.modal-backdrop');
          if(last) last.style.backgroundColor = 'rgba(2,6,23,0.35)';
        }catch(err){console && console.error && console.error(err)}
      });
      document.addEventListener('hidden.bs.modal', function(e){
        try{
          if(prevSidebarOpen) body.classList.add('sidebar-open');
          var b = document.querySelectorAll('.modal-backdrop');
          if(b && b.length>1){
            for(var i=0;i<b.length-1;i++) b[i].parentNode && b[i].parentNode.removeChild(b[i]);
          }
        }catch(err){console && console.error && console.error(err)}
      });
    })();
  </script>
</body>
</html>
