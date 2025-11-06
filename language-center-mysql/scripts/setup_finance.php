<?php
require_once('../functions/db.php');

function executeSQLFile($conn, $file) {
    echo "Executing SQL file: $file\n";
    
    $sql = file_get_contents($file);
    
    // Split SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = true;
    $errors = [];
    
    foreach ($queries as $query) {
        if (empty($query)) continue;
        
        echo "Executing query:\n$query\n";
        if (!$conn->query($query)) {
            $success = false;
            $errors[] = "Error executing query: " . $conn->error;
            echo "Error: " . $conn->error . "\n";
        } else {
            echo "Success!\n";
        }
        echo "\n";
    }
    
    return ['success' => $success, 'errors' => $errors];
}

// Step 1: Create tables
echo "Step 1: Creating tables...\n";
$result = executeSQLFile($conn, __DIR__ . '/create_tables.sql');

if ($result['success']) {
    echo "All tables created successfully!\n";
} else {
    echo "Error creating tables:\n";
    foreach ($result['errors'] as $error) {
        echo "- $error\n";
    }
}

// Step 2: Add foreign keys
echo "\nStep 2: Adding foreign keys...\n";
$result = executeSQLFile($conn, __DIR__ . '/create_foreign_keys.sql');

if ($result['success']) {
    echo "All foreign keys added successfully!\n";
} else {
    echo "Error adding foreign keys:\n";
    foreach ($result['errors'] as $error) {
        echo "- $error\n";
    }
}

// Insert default settings
echo "\nStep 3: Adding default settings...\n";
$defaultSettings = [
    // Học phí
    ['category' => 'tuition', 'setting_key' => 'payment_methods', 'setting_value' => json_encode(['cash', 'transfer', 'card']), 'description' => 'Phương thức thanh toán học phí'],
    ['category' => 'tuition', 'setting_key' => 'reminder_days', 'setting_value' => '7', 'description' => 'Số ngày trước hạn để gửi nhắc nhở'],
    
    // Chi phí
    ['category' => 'expense', 'setting_key' => 'categories', 'setting_value' => json_encode([
        'utilities' => 'Tiện ích',
        'rent' => 'Tiền thuê',
        'salary' => 'Lương',
        'supplies' => 'Văn phòng phẩm',
        'equipment' => 'Thiết bị',
        'marketing' => 'Marketing',
        'maintenance' => 'Bảo trì',
        'others' => 'Khác'
    ]), 'description' => 'Danh mục chi phí'],
    
    // Lương
    ['category' => 'salary', 'setting_key' => 'default_hourly_rate', 'setting_value' => '200000', 'description' => 'Lương giờ mặc định'],
    ['category' => 'salary', 'setting_key' => 'payment_day', 'setting_value' => '5', 'description' => 'Ngày trả lương hàng tháng'],
    
    // Thông báo
    ['category' => 'notification', 'setting_key' => 'email_enabled', 'setting_value' => 'true', 'description' => 'Bật/tắt gửi email'],
    ['category' => 'notification', 'setting_key' => 'sms_enabled', 'setting_value' => 'false', 'description' => 'Bật/tắt gửi SMS']
];

foreach ($defaultSettings as $setting) {
    $sql = "INSERT INTO settings (category, setting_key, setting_value, description) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            description = VALUES(description)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", 
            $setting['category'],
            $setting['setting_key'],
            $setting['setting_value'],
            $setting['description']
        );
        
        if ($stmt->execute()) {
            echo "Added setting: {$setting['category']}.{$setting['setting_key']}\n";
        } else {
            echo "Error adding setting: " . $stmt->error . "\n";
        }
        
        $stmt->close();
    }
}

echo "\nSetup completed!\n";

$conn->close();