<?php
// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Xử lý khi có form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf)) {
        set_flash('Invalid CSRF token');
        redirect('index.php?page=settings&tab=courses');
    }

    switch ($action) {
        case 'update_course_settings':
            // Cập nhật cài đặt khóa học
            $settings = [
                'allow_online_registration' => isset($_POST['allow_online_registration']),
                'max_students_per_class' => intval($_POST['max_students_per_class']),
                'default_course_duration' => intval($_POST['default_course_duration']),
                'course_levels' => explode(',', $_POST['course_levels']),
                'course_status_options' => explode(',', $_POST['course_status_options'])
            ];
            
            // Lưu settings vào file
            $json = json_encode($settings, JSON_PRETTY_PRINT);
            file_put_contents(__DIR__ . '/../../config/course_settings.json', $json);
            
            set_flash('Cài đặt khóa học đã được cập nhật');
            break;
    }
}

// Load current settings
$settings = [];
if (file_exists(__DIR__ . '/../../config/course_settings.json')) {
    $settings = json_decode(file_get_contents(__DIR__ . '/../../config/course_settings.json'), true);
}

// Default values
$settings = array_merge([
    'allow_online_registration' => false,
    'max_students_per_class' => 20,
    'default_course_duration' => 12,
    'course_levels' => ['Beginner', 'Intermediate', 'Advanced'],
    'course_status_options' => ['active', 'inactive', 'coming_soon']
], $settings);

?>

<div class="settings-wrapper">
    <div class="settings-sidebar">
        <h5 class="settings-nav-title">Cài đặt hệ thống</h5>
        <div class="list-group">
            <a href="?page=settings&tab=general" class="list-group-item list-group-item-action">
                <i class="bx bx-cog me-2"></i>Cài đặt chung
            </a>
            <a href="?page=settings&tab=courses" class="list-group-item list-group-item-action active">
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
            <h4>Cài đặt khóa học</h4>
            <p class="text-muted">Quản lý các cài đặt liên quan đến khóa học</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" action="index.php?page=settings&tab=courses" class="settings-form">
                    <input type="hidden" name="action" value="update_course_settings">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="allow_online_registration" 
                                   name="allow_online_registration" <?= $settings['allow_online_registration'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="allow_online_registration">
                                Cho phép đăng ký online
                            </label>
                        </div>
                        <div class="form-text">Học viên có thể đăng ký khóa học trực tiếp trên website</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Số học viên tối đa mỗi lớp</label>
                        <input type="number" class="form-control" name="max_students_per_class" 
                               value="<?= htmlspecialchars($settings['max_students_per_class']) ?>" min="1" max="100">
                        <div class="form-text">Giới hạn số lượng học viên trong một lớp</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Số buổi học mặc định</label>
                        <input type="number" class="form-control" name="default_course_duration" 
                               value="<?= htmlspecialchars($settings['default_course_duration']) ?>" min="1">
                        <div class="form-text">Số buổi học mặc định khi tạo khóa học mới</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Trình độ khóa học</label>
                        <input type="text" class="form-control" name="course_levels" 
                               value="<?= htmlspecialchars(implode(',', $settings['course_levels'])) ?>">
                        <div class="form-text">Danh sách trình độ, phân cách bằng dấu phẩy</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Trạng thái khóa học</label>
                        <input type="text" class="form-control" name="course_status_options" 
                               value="<?= htmlspecialchars(implode(',', $settings['course_status_options'])) ?>">
                        <div class="form-text">Danh sách trạng thái, phân cách bằng dấu phẩy</div>
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