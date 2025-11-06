<?php
// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}
?>

<div class="settings-wrapper">
    <div class="settings-sidebar">
        <h5 class="settings-nav-title">Cài đặt hệ thống</h5>
        <div class="list-group">
            <a href="?page=settings&tab=general" class="list-group-item list-group-item-action active">
                <i class="bx bx-cog me-2"></i>Cài đặt chung
            </a>
            <a href="?page=settings&tab=courses" class="list-group-item list-group-item-action">
                <i class="bx bx-book me-2"></i>Khóa học
            </a>
            <a href="?page=settings&tab=schedule" class="list-group-item list-group-item-action">
                <i class="bx bx-calendar me-2"></i>Lịch học
            </a>
            <a href="?page=settings&tab=payments" class="list-group-item list-group-item-action">
                <i class="bx bx-money me-2"></i>Học phí & Thanh toán
            </a>
            <a href="?page=settings&tab=users" class="list-group-item list-group-item-action">
                <i class="bx bx-user me-2"></i>Người dùng
            </a>
            <a href="?page=settings&tab=notifications" class="list-group-item list-group-item-action">
                <i class="bx bx-bell me-2"></i>Thông báo
            </a>
        </div>
    </div>

    <div class="settings-content">
        <div class="settings-header">
            <h4>Cài đặt chung</h4>
            <p class="text-muted">Quản lý các cài đặt cơ bản của hệ thống</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" action="index.php?page=settings&action=update_general" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tên trung tâm</label>
                        <input type="text" class="form-control" name="center_name" value="Language Center">
                        <div class="form-text">Tên hiển thị trên hệ thống</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Thông tin liên hệ</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" placeholder="Email">
                            </div>
                            <div class="col-md-6">
                                <input type="tel" class="form-control" name="phone" placeholder="Số điện thoại">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Múi giờ</label>
                        <select class="form-select" name="timezone">
                            <option value="Asia/Ho_Chi_Minh">Asia/Ho Chi Minh (GMT+7)</option>
                            <option value="Asia/Bangkok">Asia/Bangkok (GMT+7)</option>
                            <option value="Asia/Singapore">Asia/Singapore (GMT+8)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <div class="form-text">Kích thước khuyến nghị: 200x60px</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Favicon</label>
                        <input type="file" class="form-control" name="favicon" accept="image/*">
                        <div class="form-text">Kích thước khuyến nghị: 32x32px</div>
                    </div>

                    <hr>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>