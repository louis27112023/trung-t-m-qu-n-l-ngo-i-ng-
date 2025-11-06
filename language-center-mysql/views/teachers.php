<?php
// Dynamic CRUD UI for teachers-like tables.
$candidates = ['teachers','teacher','instructors','staff'];
$tbl = find_table_for_candidates($conn, $candidates);
// Debug helper: visit this view with ?debug=1 to print database/table/row diagnostics
if(isset($_GET['debug'])){
  echo '<div class="alert alert-warning"><strong>DEBUG</strong><br/>';
  $dbName = 'unknown';
  if(isset($conn) && $conn instanceof mysqli){
    $r = mysqli_query($conn, "SELECT DATABASE()");
    if($r){ $v = mysqli_fetch_row($r); if($v) $dbName = $v[0]; }
  }
  echo 'Connected DB: '.htmlspecialchars($dbName)."<br/>";
  echo 'Found table: '.htmlspecialchars($tbl ?: 'none')."<br/>";
  if($tbl){
    $cnt = 'error';
    $cres = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM `".mysqli_real_escape_string($conn,$tbl)."`");
    if($cres){ $ca = mysqli_fetch_assoc($cres); $cnt = $ca['c']; }
    echo 'Row count in '.htmlspecialchars($tbl).': '.htmlspecialchars($cnt)."<br/>";
  } else {
    $list = [];
    $tr = @mysqli_query($conn, "SHOW TABLES");
    if($tr){ while($rr = mysqli_fetch_row($tr)){ $list[] = $rr[0]; } }
    echo 'Tables (sample): '.htmlspecialchars(implode(', ', array_slice($list,0,20)))."<br/>";
  }
  echo 'Last MySQL error: '.htmlspecialchars(mysqli_error($conn))."</div>";
}
if(!$tbl){ ?>
  <div class="card mb-4"><div class="card-body">
    <h3 class="card-title">Giáo viên</h3>
    <p class="card-text">Chưa tìm thấy bảng giáo viên trong DB. Tạo bảng `teachers` để sử dụng giao diện quản lý.</p>
  </div></div>
<?php } else {
  $cols = get_table_columns($conn,$tbl);
  $rows = lc_filter(get_table_rows($conn,$tbl), $q);
?>
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="card-title mb-0">Quản lý Giáo viên (<?= htmlspecialchars($tbl) ?>)</h3>
      <div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">Thêm giáo viên</button>
      </div>
    </div>

    <?php if(function_exists('get_flash')){ $f=get_flash(); if($f): ?><div class="alert alert-success"><?= htmlspecialchars($f) ?></div><?php endif; } ?>

    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr><?php foreach($cols as $c) echo '<th>'.htmlspecialchars($c['Field']).'</th>'; ?><th>Hành động</th></tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?><tr>
            <?php foreach($cols as $c){ $f=$c['Field']; echo '<td>'.htmlspecialchars($r[$f] ?? '').'</td>'; } ?>
            <td>
              <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editItemModal"
                <?php foreach($cols as $c){ $n=$c['Field']; echo ' data-'.htmlspecialchars($n).'="'.htmlspecialchars($r[$n] ?? '', ENT_QUOTES).'"'; } ?>>Sửa</button>

              <form method="post" action="index.php?page=teachers" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                <input type="hidden" name="action" value="delete_teachers">
                <input type="hidden" name="id" value="<?= htmlspecialchars($r[get_primary_key_from_cols($cols)]) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button class="btn btn-sm btn-outline-danger">Xóa</button>
              </form>
            </td>
          </tr><?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <form method="post" action="index.php?page=teachers">
    <input type="hidden" name="action" value="add_teachers">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
    <div class="modal-header"><h5 class="modal-title">Thêm giáo viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <?php foreach($cols as $c){ if(strpos($c['Extra'],'auto_increment')!==false) continue; $name=$c['Field']; $type='text'; if(stripos($name,'date')!==false) $type='date'; elseif(stripos($name,'time')!==false) $type='datetime-local'; elseif(stripos($c['Type'],'int')!==false) $type='number'; ?>
        <div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
        <input name="<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
      <?php } ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
  </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <form method="post" action="index.php?page=teachers">
    <input type="hidden" name="action" value="edit_teachers">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="<?= htmlspecialchars(get_primary_key_from_cols($cols)) ?>" id="edit-item-pk" value="">
    <div class="modal-header"><h5 class="modal-title">Sửa giáo viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <?php foreach($cols as $c){ if($c['Field']===get_primary_key_from_cols($cols)) continue; $name=$c['Field']; $type='text'; if(stripos($name,'date')!==false) $type='date'; elseif(stripos($name,'time')!==false) $type='datetime-local'; elseif(stripos($c['Type'],'int')!==false) $type='number'; ?>
        <div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
        <input name="<?= htmlspecialchars($name) ?>" id="edit-<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
      <?php } ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
  </form>
</div></div></div>

<script>
var em = document.getElementById('editItemModal');
if(em) em.addEventListener('show.bs.modal', function(ev){
  var btn = ev.relatedTarget; if(!btn) return;
  <?php foreach($cols as $c){ $n=$c['Field']; if($n===get_primary_key_from_cols($cols)) continue; ?>
    var v = btn.getAttribute('data-<?= htmlspecialchars($n) ?>') || '';
    var el = document.getElementById('edit-<?= htmlspecialchars($n) ?>'); if(el) el.value = v;
  <?php } ?>
  document.getElementById('edit-item-pk').value = btn.getAttribute('data-<?= htmlspecialchars(get_primary_key_from_cols($cols)) ?>') || '';
});
</script>

<?php } ?>