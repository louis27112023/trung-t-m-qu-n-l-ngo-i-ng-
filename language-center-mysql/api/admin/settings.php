<?php
require_once '../../functions/db.php';
require_once '../../functions/auth.php';

header('Content-Type: application/json');

// Verify admin access
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Load settings
    $settings = [
        'system' => [
            'maintenance_mode' => false,
            'debug_mode' => false,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'default_language' => 'vi',
            'backup_enabled' => true,
            'backup_schedule' => 'daily',
            'backup_retention' => 30,
            'log_retention' => 90
        ],
        'security' => [
            'password_policy' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_numbers' => true,
                'require_symbols' => true
            ],
            'session_timeout' => 30,
            'login_attempts' => 5,
            'lockout_duration' => 15,
            'allow_registration' => false,
            'email_verification' => true,
            '2fa_enabled' => false
        ],
        'notification' => [
            'email' => [
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_user' => '',
                'smtp_pass' => '',
                'from_email' => '',
                'from_name' => ''
            ],
            'sms' => [
                'provider' => '',
                'api_key' => '',
                'sender_id' => ''
            ],
            'telegram' => [
                'enabled' => false,
                'bot_token' => '',
                'chat_id' => ''
            ]
        ],
        'academic' => [
            'max_class_size' => 20,
            'min_class_size' => 5,
            'class_duration' => 90,
            'break_duration' => 15,
            'attendance_threshold' => 80,
            'grade_scale' => 'letter', // letter, number, percentage
            'passing_grade' => 70,
            'auto_enrollment' => true
        ],
        'financial' => [
            'currency' => 'VND',
            'tax_rate' => 10,
            'late_fee' => 5,
            'payment_methods' => [
                'cash' => true,
                'bank_transfer' => true,
                'momo' => false,
                'vnpay' => false
            ],
            'refund_policy' => [
                'allowed' => true,
                'deadline_days' => 7,
                'deduction_percent' => 10
            ],
            'installment' => [
                'enabled' => true,
                'min_amount' => 10000000,
                'max_terms' => 3
            ]
        ],
        'api' => [
            'enabled' => false,
            'rate_limit' => 1000,
            'allowed_origins' => [],
            'webhooks' => [
                'attendance' => false,
                'payment' => false,
                'enrollment' => false
            ]
        ],
        'reports' => [
            'auto_generate' => true,
            'schedule' => 'weekly',
            'recipients' => [],
            'include_financials' => true,
            'include_attendance' => true,
            'include_grades' => true
        ],
        'permissions' => [
            'roles' => [
                'admin' => [
                    'all' => true
                ],
                'manager' => [
                    'view_reports' => true,
                    'manage_courses' => true,
                    'manage_students' => true,
                    'manage_teachers' => true,
                    'view_financials' => true
                ],
                'teacher' => [
                    'view_classes' => true,
                    'manage_attendance' => true,
                    'manage_grades' => true,
                    'view_students' => true
                ],
                'student' => [
                    'view_schedule' => true,
                    'view_grades' => true,
                    'view_attendance' => true
                ]
            ]
        ],
        'customization' => [
            'theme' => [
                'primary_color' => '#4361ee',
                'secondary_color' => '#3f37c9',
                'success_color' => '#059669',
                'danger_color' => '#dc2626'
            ],
            'logo' => [
                'header' => '',
                'favicon' => '',
                'email' => ''
            ],
            'dashboard' => [
                'widgets' => [
                    'quick_stats' => true,
                    'recent_activities' => true,
                    'calendar' => true,
                    'announcements' => true
                ]
            ]
        ]
    ];

    // Load from database if exists
    $sql = "SELECT * FROM settings";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $value = json_decode($row['value'], true);
            $path = explode('.', $row['key_name']);
            $current = &$settings;
            foreach ($path as $key) {
                if (!isset($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
            if ($value !== null) {
                $current = $value;
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $settings]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    // Begin transaction
    mysqli_begin_transaction($conn);
    try {
        // Clear existing settings
        mysqli_query($conn, "DELETE FROM settings");

        // Flatten and save new settings
        function flattenArray($array, $prefix = '') {
            $result = [];
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $result = array_merge($result, flattenArray($value, $prefix . $key . '.'));
                } else {
                    $result[$prefix . $key] = $value;
                }
            }
            return $result;
        }

        $flat = flattenArray($input);
        foreach ($flat as $key => $value) {
            $key_esc = mysqli_real_escape_string($conn, $key);
            $value_json = json_encode($value);
            $value_esc = mysqli_real_escape_string($conn, $value_json);
            
            $sql = "INSERT INTO settings (key_name, value) VALUES ('$key_esc', '$value_esc')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Error saving setting: $key");
            }
        }

        // Log the change
        $user_id = $_SESSION['user']['id'];
        $timestamp = date('Y-m-d H:i:s');
        $log_sql = "INSERT INTO activity_log (user_id, action, details, created_at) 
                    VALUES ($user_id, 'update_settings', 'Updated system settings', '$timestamp')";
        mysqli_query($conn, $log_sql);

        mysqli_commit($conn);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}