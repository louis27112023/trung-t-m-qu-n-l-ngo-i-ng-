<div class="settings-page">
    <div class="settings-header">
        <div class="container-fluid">
            <h1 class="settings-title">Thiết lập hệ thống</h1>
            <div class="settings-actions">
                <button class="btn btn-light me-2" id="btnReload">
                    <i class='bx bx-refresh'></i> Làm mới
                </button>
                <button class="btn btn-primary" id="btnSaveAll">
                    <i class='bx bx-save'></i> Lưu tất cả
                </button>
            </div>
        </div>
    </div>

    <div class="settings-container">
        <!-- Settings Navigation -->
        <div class="settings-nav">
            <div class="nav-section">
                <div class="nav-section-title">QUẢN LÝ HỆ THỐNG</div>
                <a href="#system" class="nav-item active">
                    <i class='bx bx-cog'></i>
                    <span>Cấu hình chung</span>
                </a>
                <a href="#security" class="nav-item">
                    <i class='bx bx-shield-quarter'></i>
                    <span>Bảo mật</span>
                </a>
                <a href="#backup" class="nav-item">
                    <i class='bx bx-server'></i>
                    <span>Sao lưu & Phục hồi</span>
                </a>
                <a href="#logs" class="nav-item">
                    <i class='bx bx-history'></i>
                    <span>Nhật ký hệ thống</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">QUẢN LÝ NGHIỆP VỤ</div>
                <a href="#academic" class="nav-item">
                    <i class='bx bx-book-open'></i>
                    <span>Chương trình học</span>
                </a>
                <a href="#financial" class="nav-item">
                    <i class='bx bx-dollar'></i>
                    <span>Tài chính</span>
                </a>
                <a href="#reports" class="nav-item">
                    <i class='bx bx-line-chart'></i>
                    <span>Báo cáo & Thống kê</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">QUẢN LÝ HỌC TẬP</div>
                <a href="#courses" class="nav-item">
                    <i class='bx bx-book-content'></i>
                    <span>Khóa học</span>
                </a>
                <a href="#classes" class="nav-item">
                    <i class='bx bx-chalkboard'></i>
                    <span>Lớp học</span>
                </a>
                <a href="#schedule" class="nav-item">
                    <i class='bx bx-calendar'></i>
                    <span>Lịch học</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">QUẢN LÝ NGƯỜI DÙNG</div>
                <a href="#teachers" class="nav-item">
                    <i class='bx bx-user-voice'></i>
                    <span>Giáo viên</span>
                </a>
                <a href="#students" class="nav-item">
                    <i class='bx bx-user'></i>
                    <span>Học viên</span>
                </a>
                <a href="#roles" class="nav-item">
                    <i class='bx bx-shield'></i>
                    <span>Phân quyền</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">TÍCH HỢP & THÔNG BÁO</div>
                <a href="#notification" class="nav-item">
                    <i class='bx bx-bell'></i>
                    <span>Thông báo</span>
                </a>
                <a href="#payment" class="nav-item">
                    <i class='bx bx-credit-card'></i>
                    <span>Cổng thanh toán</span>
                </a>
                <a href="#api" class="nav-item">
                    <i class='bx bx-code-block'></i>
                    <span>API & Webhook</span>
                </a>
                <a href="#telegram" class="nav-item">
                    <i class='bx bx-paper-plane'></i>
                    <span>Bot Telegram</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">TÙY CHỈNH GIAO DIỆN</div>
                <a href="#theme" class="nav-item">
                    <i class='bx bx-palette'></i>
                    <span>Giao diện</span>
                </a>
                <a href="#widgets" class="nav-item">
                    <i class='bx bx-grid-alt'></i>
                    <span>Widget & Tiện ích</span>
                </a>
                <a href="#branding" class="nav-item">
                    <i class='bx bx-images'></i>
                    <span>Logo & Thương hiệu</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">QUẢN TRỊ HỆ THỐNG</div>
                <a href="#maintenance" class="nav-item">
                    <i class='bx bx-wrench'></i>
                    <span>Bảo trì hệ thống</span>
                </a>
                <a href="#cleanup" class="nav-item">
                    <i class='bx bx-trash'></i>
                    <span>Dọn dẹp dữ liệu</span>
                </a>
                <a href="#import" class="nav-item">
                    <i class='bx bx-import'></i>
                    <span>Nhập/Xuất dữ liệu</span>
                </a>
                <a href="#license" class="nav-item">
                    <i class='bx bx-key'></i>
                    <span>Giấy phép & Bản quyền</span>
                </a>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="settings-content">
            <!-- System Settings -->
            <div class="settings-section active" id="system">
                <div class="section-header">
                    <h2>Cấu hình chung</h2>
                    <p>Thiết lập thông tin cơ bản của trung tâm</p>
                </div>

                <div class="settings-grid">
                    <!-- Thông tin trung tâm -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h3 class="settings-card-title">
                                <i class='bx bx-building-house'></i>
                                Thông tin trung tâm
                            </h3>
                        </div>
                <div class="settings-card-body">
                    <div class="setting-group">
                        <label class="setting-label">Logo trung tâm</label>
                        <div class="file-upload">
                            <label for="logo-upload" class="file-upload-btn">
                                <i class='bx bx-upload'></i> Tải lên logo
                            </label>
                            <input type="file" id="logo-upload" accept="image/*">
                        </div>
                        <p class="setting-hint">Kích thước đề xuất: 200x60px, định dạng PNG</p>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Màu sắc chủ đạo</label>
                        <div class="color-picker">
                            <div class="color-option active" style="background: #4361ee"></div>
                            <div class="color-option" style="background: #2563eb"></div>
                            <div class="color-option" style="background: #7c3aed"></div>
                            <div class="color-option" style="background: #db2777"></div>
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Tên trung tâm</label>
                        <input type="text" class="form-control" value="Language Center" placeholder="Nhập tên trung tâm">
                    </div>
                </div>
            </div>

            <!-- Cấu hình lớp học -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h2 class="settings-card-title">
                        <i class='bx bx-book-reader'></i>
                        Cấu hình lớp học
                    </h2>
                </div>
                <div class="settings-card-body">
                    <div class="setting-group">
                        <label class="setting-label">Sĩ số tối đa mỗi lớp</label>
                        <select class="form-select">
                            <option>10 học viên</option>
                            <option>15 học viên</option>
                            <option>20 học viên</option>
                            <option>25 học viên</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Thời lượng tiết học</label>
                        <select class="form-select">
                            <option>45 phút</option>
                            <option>60 phút</option>
                            <option>90 phút</option>
                            <option>120 phút</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Tự động chia lớp</label>
                        <label class="switch-control">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                        <p class="setting-hint">Tự động phân bổ học viên vào lớp phù hợp</p>
                    </div>
                </div>
            </div>

            <!-- Học phí & Thanh toán -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h2 class="settings-card-title">
                        <i class='bx bx-credit-card'></i>
                        Học phí & Thanh toán
                    </h2>
                </div>
                <div class="settings-card-body">
                    <div class="setting-group">
                        <label class="setting-label">Phương thức thanh toán</label>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox" checked>
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">Tiền mặt</span>
                        </div>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox" checked>
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">Chuyển khoản</span>
                        </div>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">Thanh toán online</span>
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Chính sách học phí</label>
                        <select class="form-select mb-2">
                            <option>Thu theo tháng</option>
                            <option>Thu theo khóa</option>
                            <option>Thu theo kỳ</option>
                        </select>
                        <label class="switch-control">
                            <input type="checkbox" checked>
                            <span class="switch-slider"></span>
                        </label>
                        <span class="ms-2">Cho phép trả góp</span>
                    </div>
                </div>
            </div>

            <!-- Thông báo & Liên lạc -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h2 class="settings-card-title">
                        <i class='bx bx-bell'></i>
                        Thông báo & Liên lạc
                    </h2>
                </div>
                <div class="settings-card-body">
                    <div class="setting-group">
                        <label class="setting-label">Kênh thông báo</label>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox" checked>
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">Email</span>
                        </div>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox" checked>
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">SMS</span>
                        </div>
                        <div class="mb-2">
                            <label class="switch-control">
                                <input type="checkbox">
                                <span class="switch-slider"></span>
                            </label>
                            <span class="ms-2">Zalo</span>
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Tần suất thông báo</label>
                        <select class="form-select">
                            <option>Ngay lập tức</option>
                            <option>Mỗi giờ</option>
                            <option>Mỗi ngày</option>
                            <option>Mỗi tuần</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="button" class="btn btn-outline me-2">Đặt lại mặc định</button>
            <button type="button" class="btn btn-primary">
                <i class='bx bx-save me-1'></i> Lưu thay đổi
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý chọn màu
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            colorOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            // Lưu màu đã chọn
            const color = this.style.backgroundColor;
            document.documentElement.style.setProperty('--primary-color', color);
        });
    });

    // Preview logo khi upload
    const logoInput = document.getElementById('logo-upload');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Cập nhật preview logo
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.maxHeight = '60px';
                    const container = document.querySelector('.file-upload');
                    container.innerHTML = '';
                    container.appendChild(preview);
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Lưu cài đặt
    const saveButton = document.querySelector('.btn-primary');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            // Animation khi lưu
            this.classList.add('saving');
            setTimeout(() => {
                this.classList.remove('saving');
                // Hiển thị thông báo thành công
                const alert = document.createElement('div');
                alert.className = 'alert alert-success position-fixed top-0 end-0 m-3';
                alert.innerHTML = 'Đã lưu thay đổi thành công!';
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 3000);
            }, 1000);
        });
    }

    // Hiệu ứng hover cho cards
    const cards = document.querySelectorAll('.settings-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
.settings-container {
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.nav-tabs {
    border-bottom: 2px solid #eee;
}

.nav-tabs .nav-link {
    color: #666;
    border: none;
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: #333;
    background: rgba(0,0,0,0.02);
}

.nav-tabs .nav-link.active {
    color: #4361ee;
    border-bottom: 2px solid #4361ee;
    background: transparent;
}

.nav-tabs .nav-link i {
    margin-right: 8px;
}

.tab-content {
    padding-top: 20px;
}

.card {
    border: 1px solid #eee;
    box-shadow: none;
}

.card-title {
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.form-label {
    color: #555;
    font-weight: 500;
}

.form-control:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
}
</style>
<?php
// Generic CRUD UI for settings-like tables
$candidates = ['settings','config','options'];
$tbl = find_table_for_candidates($conn,$candidates);
if(!$tbl){ ?>
	<div class="card mb-4"><div class="card-body">
		<h3 class="card-title">Cài đặt</h3>
		<p class="card-text">Chưa có bảng cài đặt trong DB. Tạo bảng `settings` (id, key_name, value) để sử dụng chức năng CRUD.</p>
	</div></div>
<?php } else {
	$cols = get_table_columns($conn,$tbl);
	$rows = get_table_rows($conn,$tbl);
	$pk = get_primary_key_from_cols($cols);
?>
	<div class="card mb-4">
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h3 class="card-title mb-0">Cài đặt (<?= htmlspecialchars($tbl) ?>)</h3>
				<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">Thêm cài đặt</button>
			</div>
			<?php if(function_exists('get_flash')){ $f=get_flash(); if($f): ?><div class="alert alert-success"><?= htmlspecialchars($f) ?></div><?php endif; } ?>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead><tr><?php foreach($cols as $c) echo '<th>'.htmlspecialchars($c['Field']).'</th>'; ?><th>Hành động</th></tr></thead>
					<tbody>
						<?php foreach($rows as $r): ?><tr>
							<?php foreach($cols as $c){ $f=$c['Field']; echo '<td>'.htmlspecialchars($r[$f] ?? '').'</td>'; } ?>
							<td>
								<button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editSettingModal" <?php foreach($cols as $c){ $n=$c['Field']; echo ' data-'.htmlspecialchars($n).'="'.htmlspecialchars($r[$n] ?? '').'"'; } ?>>Sửa</button>
								<form method="post" action="index.php?page=settings" class="d-inline" onsubmit="return confirm('Xóa bản ghi này?');">
									<input type="hidden" name="action" value="delete_settings">
									<input type="hidden" name="id" value="<?= htmlspecialchars($r[$pk]) ?>">
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
	<div class="modal fade" id="addSettingModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
		<form method="post" action="index.php?page=settings">
			<input type="hidden" name="action" value="add_settings">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
			<div class="modal-header"><h5 class="modal-title">Thêm cài đặt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<?php foreach($cols as $c){ if(strpos($c['Extra'],'auto_increment')!==false) continue; $name=$c['Field']; $type='text'; if(stripos($c['Type'],'int')!==false) $type='number'; ?>
					<div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
						<input name="<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
				<?php } ?>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
		</form>
	</div></div></div>

	<!-- Edit Modal -->
	<div class="modal fade" id="editSettingModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
		<form method="post" action="index.php?page=settings">
			<input type="hidden" name="action" value="edit_settings">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
			<input type="hidden" name="<?= htmlspecialchars($pk) ?>" id="edit-setting-pk" value="">
			<div class="modal-header"><h5 class="modal-title">Sửa cài đặt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<?php foreach($cols as $c){ if($c['Field']===$pk) continue; $name=$c['Field']; $type='text'; if(stripos($c['Type'],'int')!==false) $type='number'; ?>
					<div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
						<input name="<?= htmlspecialchars($name) ?>" id="edit-<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
				<?php } ?>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
		</form>
	</div></div></div>

	<script>
		var em = document.getElementById('editSettingModal');
		if(em) em.addEventListener('show.bs.modal', function(ev){
			var btn = ev.relatedTarget; if(!btn) return;
			<?php foreach($cols as $c){ $n=$c['Field']; if($n===$pk) continue; ?>
				var v = btn.getAttribute('data-<?= htmlspecialchars($n) ?>') || '';
				var el = document.getElementById('edit-<?= htmlspecialchars($n) ?>'); if(el) el.value = v;
			<?php } ?>
			document.getElementById('edit-setting-pk').value = btn.getAttribute('data-<?= htmlspecialchars($pk) ?>') || '';
		});
	</script>
<?php } ?>