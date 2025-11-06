<?php
$result = @mysqli_query($conn,"SELECT * FROM courses ORDER BY id DESC");
$rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
$rows = lc_filter($rows,$q);

// Debug helper: visit this view with ?debug=1 to print fetched row info
if(isset($_GET['debug'])){
  echo '<div class="alert alert-info"><strong>DEBUG - courses</strong><br/>';
  $dbName = 'unknown';
  $rdb = @mysqli_query($conn, "SELECT DATABASE() as db"); if($rdb){ $rd = mysqli_fetch_assoc($rdb); $dbName = $rd['db'] ?? $dbName; }
  echo 'Connected DB: '.htmlspecialchars($dbName)."<br/>";
  echo 'Query used: SELECT * FROM courses ORDER BY id DESC<br/>';
  echo 'Rows fetched by PHP: '.htmlspecialchars(count($rows))."<br/>";
  if(count($rows) > 0){
    echo '<table class="table table-sm"><thead><tr><th>id</th><th>name</th></tr></thead><tbody>';
    foreach($rows as $rr){ echo '<tr><td>'.htmlspecialchars($rr['id'] ?? '').'</td><td>'.htmlspecialchars($rr['name'] ?? '').'</td></tr>'; }
    echo '</tbody></table>';
  } else {
    echo '<pre>No rows returned. MySQL error: '.htmlspecialchars(mysqli_error($conn)).'</pre>';
  }
  echo '</div>';
}
?>
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="card-title mb-0">Quản lý khóa học</h3>
      <div class="d-flex align-items-center">
        <form class="d-flex me-2" method="get" action="index.php">
          <input type="hidden" name="page" value="courses">
          <input class="form-control form-control-sm me-2" type="search" placeholder="Tìm kiếm..." aria-label="Search" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button class="btn btn-sm btn-outline-primary" type="submit">Tìm</button>
        </form>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">Thêm khóa học</button>
      </div>
    </div>

    <?php if(function_exists('get_flash')){ $f=get_flash(); if($f): ?>
      <div class="alert alert-success"><?= htmlspecialchars($f) ?></div>
    <?php endif; } ?>

    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th scope="col" class="text-center" style="width: 80px">Mã</th>
        <th scope="col" style="width: 25%">Tên khóa học</th>
        <th scope="col" style="width: 15%">Trình độ</th>
        <th scope="col" class="text-end" style="width: 15%">Học phí</th>
        <th scope="col" class="text-center" style="width: 100px">Số buổi</th>
        <th scope="col" class="text-center" style="width: 120px">Trạng thái</th>
        <th scope="col" class="text-end" style="width: 150px">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td class="text-center"><?= htmlspecialchars($r['id']) ?></td>
        <td class="fw-medium"><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['level']) ?></td>
        <td class="text-end"><?= number_format($r['price'] ?? 0, 0, ',', '.') ?> ₫</td>
        <td class="text-center"><?= $r['duration'] ?? 0 ?></td>
        <td class="text-center">
          <span class="badge <?= ($r['status'] == 'active' ? 'bg-success' : 'bg-secondary') ?>">
            <?= ($r['status'] == 'active' ? 'Đang mở' : 'Đã đóng') ?>
          </span>
        </td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" 
            data-bs-toggle="modal" 
            data-bs-target="#editCourseModal"
            data-id="<?= htmlspecialchars($r['id']) ?>"
            data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>"
            data-level="<?= htmlspecialchars($r['level'], ENT_QUOTES) ?>"
            data-price="<?= htmlspecialchars($r['price'] ?? 0, ENT_QUOTES) ?>"
            data-duration="<?= htmlspecialchars($r['duration'] ?? 0, ENT_QUOTES) ?>"
            data-status="<?= htmlspecialchars($r['status'], ENT_QUOTES) ?>"
          >
            <i class="bi bi-pencil-square"></i> Sửa
          </button>

          <form method="post" action="index.php?page=courses" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa khóa học này?');">
            <input type="hidden" name="action" value="delete_course">
            <input type="hidden" name="id" value="<?= htmlspecialchars($r['id']) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <button class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash"></i> Xóa
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    </div>
  </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="index.php?page=courses" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="add_course">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="modal-header">
          <h5 class="modal-title">Thêm khóa học mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">Tên khóa học</label>
            <input name="name" class="form-control" required maxlength="255">
            <div class="invalid-feedback">Vui lòng nhập tên khóa học</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Trình độ</label>
            <select name="level" class="form-select">
              <option value="">Chọn trình độ</option>
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Advanced">Advanced</option>
            </select>
          </div>
          <div class="mb-3 row">
            <div class="col-6">
              <label class="form-label required">Học phí</label>
              <div class="input-group">
                <input name="price" type="number" min="0" step="1000" class="form-control" required value="0">
                <span class="input-group-text">₫</span>
              </div>
              <div class="invalid-feedback">Vui lòng nhập học phí</div>
            </div>
            <div class="col-6">
              <label class="form-label required">Số buổi</label>
              <input name="duration" type="number" min="1" class="form-control" required value="1">
              <div class="invalid-feedback">Vui lòng nhập số buổi học</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
              <option value="active">Đang mở</option>
              <option value="inactive">Đã đóng</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Hủy
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Lưu khóa học
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="index.php?page=courses">
        <input type="hidden" name="action" value="edit_course">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" id="edit-course-id" value="">
        <div class="modal-header">
          <h5 class="modal-title">Sửa khóa học</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tên khóa học</label>
            <input name="name" id="edit-course-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Trình độ</label>
            <input name="level" id="edit-course-level" class="form-control">
          </div>
          <div class="mb-3 row">
            <div class="col-6">
              <label class="form-label">Học phí</label>
              <input name="price" id="edit-course-price" type="number" step="0.01" class="form-control" value="0">
            </div>
            <div class="col-6">
              <label class="form-label">Số buổi</label>
              <input name="duration" id="edit-course-duration" type="number" class="form-control">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" id="edit-course-status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // populate edit modal when triggered
  var editModal = document.getElementById('editCourseModal');
  if(editModal){
    editModal.addEventListener('show.bs.modal', function(event){
      var button = event.relatedTarget; // Button that triggered the modal
      if(!button) return;
      var id = button.getAttribute('data-id');
      var name = button.getAttribute('data-name');
      var level = button.getAttribute('data-level');
      var price = button.getAttribute('data-price');
      var duration = button.getAttribute('data-duration');
      var status = button.getAttribute('data-status');
      document.getElementById('edit-course-id').value = id || '';
      document.getElementById('edit-course-name').value = name || '';
      document.getElementById('edit-course-level').value = level || '';
      document.getElementById('edit-course-price').value = price || '';
      document.getElementById('edit-course-duration').value = duration || '';
      document.getElementById('edit-course-status').value = status || 'active';
    });
  }
</script>

<script>
// Move modals to document.body so Bootstrap's backdrop and z-index work reliably
;(function(){
  try{
    ['addCourseModal','editCourseModal'].forEach(function(id){
      var el = document.getElementById(id);
      if(el && el.parentNode !== document.body){
        document.body.appendChild(el);
      }
    });
  }catch(e){console && console.error && console.error(e)}
})();
</script>
