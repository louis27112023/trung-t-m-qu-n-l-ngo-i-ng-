<?php
header('Content-Type: application/json');

// Include database connection
require_once '../functions/db.php';

// Verify admin permission
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'update':
                if (!isset($data['key']) || !isset($data['value'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                    exit;
                }
                
                $key = $data['key'];
                $value = is_array($data['value']) ? json_encode($data['value']) : $data['value'];
                
                // Update setting
                $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                        
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
                mysqli_stmt_close($stmt);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        // Handle form data
        $category = $_POST['category'] ?? '';
        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($category) || empty($key) || empty($value)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        // Insert new setting
        $sql = "INSERT INTO settings (category, setting_key, setting_value, description) 
                VALUES (?, ?, ?, ?)";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $category, $key, $value, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        mysqli_stmt_close($stmt);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

mysqli_close($conn);
?>