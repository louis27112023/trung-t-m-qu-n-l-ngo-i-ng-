<?php
// Check admin permission
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_notifications') {
        // Update notification settings
        $settings = [
            'enable_email' => isset($_POST['enable_email']),
            'enable_sms' => isset($_POST['enable_sms']),
            'email_template' => $_POST['email_template'],
            'sms_template' => $_POST['sms_template'],
            'notification_days' => intval($_POST['notification_days'])
        ];
        
        // Save to JSON file
        file_put_contents(__DIR__ . '/../../config/notification_settings.json', json_encode($settings, JSON_PRETTY_PRINT));
        set_flash('Cập nhật cài đặt thông báo thành công!');
    }
}

// Load current settings
$settings = [];
if (file_exists(__DIR__ . '/../../config/notification_settings.json')) {
    $settings = json_decode(file_get_contents(__DIR__ . '/../../config/notification_settings.json'), true);
}

// Default values
$settings = array_merge([
    'enable_email' => true,
    'enable_sms' => false,
    'email_template' => 'Xin chào {student_name},\n\nĐây là email nhắc nhở học phí khóa học {course_name}...',
    'sms_template' => 'TB: HV {student_name} có học phí đến hạn thanh toán...',
    'notification_days' => 7
], $settings);
?>

<div class="module-wrapper">
    <!-- Module Header -->
    <div class="module-header">
        <h2 class="module-title">
            <i class="bx bx-bell-ring"></i>
            Cài đặt thông báo
        </h2>
        <p class="module-subtitle">Quản lý cấu hình thông báo qua Email và SMS</p>
    </div>

    <!-- Main Content -->
    <div class="module-content">
        <div class="row">
            <!-- Email Settings -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bx bx-envelope me-2"></i>Cấu hình Email</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_notifications">
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_email" 
                                           name="enable_email" <?= $settings['enable_email'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_email">
                                        Bật thông báo qua Email
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mẫu Email</label>
                                <textarea class="form-control" name="email_template" rows="6"
                                    ><?= htmlspecialchars($settings['email_template']) ?></textarea>
                                <div class="form-text">
                                    Sử dụng: {student_name}, {course_name}, {amount}, {due_date}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cấu hình SMTP</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Host</span>
                                    <input type="text" class="form-control" name="smtp_host" value="smtp.gmail.com">
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Port</span>
                                    <input type="text" class="form-control" name="smtp_port" value="587">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Email</span>
                                    <input type="email" class="form-control" name="smtp_user">
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary" onclick="testEmail()">
                                <i class="bx bx-send me-1"></i>Gửi email test
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SMS Settings -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bx bx-message me-2"></i>Cấu hình SMS</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_notifications">

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_sms" 
                                           name="enable_sms" <?= $settings['enable_sms'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_sms">
                                        Bật thông báo qua SMS
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mẫu tin nhắn</label>
                                <textarea class="form-control" name="sms_template" rows="4"
                                    ><?= htmlspecialchars($settings['sms_template']) ?></textarea>
                                <div class="form-text">
                                    Sử dụng: {student_name}, {amount}, {due_date}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">API Key SMS</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="sms_api_key">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword(this)">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary" onclick="testSMS()">
                                <i class="bx bx-message-square-dots me-1"></i>Gửi SMS test
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- General Notification Settings -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bx bx-cog me-2"></i>Cài đặt chung</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" class="row">
                            <input type="hidden" name="action" value="update_notifications">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số ngày gửi nhắc trước</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="notification_days"
                                               value="<?= htmlspecialchars($settings['notification_days']) ?>" min="1" max="30">
                                        <span class="input-group-text">ngày</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testEmail() {
    // Implement email test functionality
    alert('Tính năng test email đang được phát triển');
}

function testSMS() {
    // Implement SMS test functionality
    alert('Tính năng test SMS đang được phát triển');
}

function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = '<i class="bx bx-hide"></i>';
    } else {
        input.type = 'password';
        button.innerHTML = '<i class="bx bx-show"></i>';
    }
}
</script>

<style>
.module-wrapper {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.module-header {
    margin-bottom: 2rem;
}

.module-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.module-subtitle {
    color: #6c757d;
    margin: 0;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    border-radius: 0.5rem;
}

.card-header {
    background: none;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    padding: 1.25rem;
}

.card-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
}

.card-body {
    padding: 1.25rem;
}

.form-check-input {
    width: 3rem !important;
    height: 1.5rem !important;
}

.form-switch .form-check-input {
    margin-left: -3.5rem;
}

.form-switch .form-check-label {
    margin-left: 0.5rem;
}
</style>