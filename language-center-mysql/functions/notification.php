<?php
require_once('db.php');

function addNotification($data) {
    global $conn;
    
    $sql = "INSERT INTO notifications (type, title, message, recipient_type, recipient_id, scheduled_at) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $scheduledAt = $data['scheduled_at'] ?? null;
    
    $stmt->bind_param("ssssis", 
        $data['type'],
        $data['title'],
        $data['message'],
        $data['recipient_type'],
        $data['recipient_id'],
        $scheduledAt
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true, 'id' => $stmt->insert_id];
}

function getNotifications($filters = []) {
    global $conn;
    
    $sql = "SELECT n.*, 
                CASE 
                    WHEN n.recipient_type = 'student' THEN s.name
                    WHEN n.recipient_type = 'teacher' THEN t.name
                    ELSE 'Admin'
                END as recipient_name
            FROM notifications n
            LEFT JOIN students s ON n.recipient_type = 'student' AND n.recipient_id = s.id
            LEFT JOIN teachers t ON n.recipient_type = 'teacher' AND n.recipient_id = t.id
            WHERE 1=1";
    
    if (!empty($filters['recipient_type'])) {
        $sql .= " AND n.recipient_type = '" . $conn->real_escape_string($filters['recipient_type']) . "'";
    }
    if (!empty($filters['recipient_id'])) {
        $sql .= " AND n.recipient_id = " . intval($filters['recipient_id']);
    }
    if (!empty($filters['status'])) {
        $sql .= " AND n.status = '" . $conn->real_escape_string($filters['status']) . "'";
    }
    
    $sql .= " ORDER BY n.created_at DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

function updateNotificationStatus($id, $status) {
    global $conn;
    
    $sql = "UPDATE notifications SET status = ?, ";
    if ($status === 'sent') {
        $sql .= "sent_at = CURRENT_TIMESTAMP, ";
    }
    $sql .= "updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true];
}

function deleteNotification($id) {
    global $conn;
    
    $sql = "DELETE FROM notifications WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true];
}

// Send pending notifications that are scheduled
function sendPendingNotifications() {
    global $conn;
    
    $sql = "SELECT * FROM notifications 
            WHERE status = 'pending' 
            AND (scheduled_at IS NULL OR scheduled_at <= CURRENT_TIMESTAMP)";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $sent = 0;
    while ($notification = $result->fetch_assoc()) {
        // Here you would implement your actual notification sending logic
        // For example, sending emails, SMS, or push notifications
        
        // For now, we'll just mark them as sent
        $result = updateNotificationStatus($notification['id'], 'sent');
        if (!isset($result['error'])) {
            $sent++;
        }
    }
    
    return ['success' => true, 'sent' => $sent];
}