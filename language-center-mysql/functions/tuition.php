<?php
require_once('db.php');

function getTuitionFees($filters = []) {
    global $conn;
    
    $sql = "SELECT tf.*, s.name as student_name, c.name as course_name 
            FROM tuition_fees tf
            JOIN students s ON tf.student_id = s.id
            JOIN courses c ON tf.course_id = c.id
            WHERE 1=1";
    
    // Add filters
    if (!empty($filters['student_id'])) {
        $sql .= " AND tf.student_id = " . intval($filters['student_id']);
    }
    if (!empty($filters['course_id'])) {
        $sql .= " AND tf.course_id = " . intval($filters['course_id']);
    }
    if (!empty($filters['payment_status'])) {
        $sql .= " AND tf.payment_status = '" . $conn->real_escape_string($filters['payment_status']) . "'";
    }
    if (!empty($filters['date_from'])) {
        $sql .= " AND tf.due_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND tf.due_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $sql .= " ORDER BY tf.due_date DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $fees = [];
    while ($row = $result->fetch_assoc()) {
        $fees[] = $row;
    }
    
    return $fees;
}

function addTuitionFee($data) {
    global $conn;
    
    $sql = "INSERT INTO tuition_fees (student_id, course_id, amount, due_date, payment_status, notes) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("iidsss", 
        $data['student_id'],
        $data['course_id'],
        $data['amount'],
        $data['due_date'],
        $data['payment_status'],
        $data['notes']
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    // Send notification
    $notification = [
        'type' => 'tuition_fee',
        'title' => 'New Tuition Fee Added',
        'message' => sprintf(
            'A new tuition fee of %s has been added for your course. Due date: %s',
            number_format($data['amount'], 2),
            date('d/m/Y', strtotime($data['due_date']))
        ),
        'recipient_type' => 'student',
        'recipient_id' => $data['student_id']
    ];
    addNotification($notification);
    
    return ['success' => true, 'id' => $stmt->insert_id];
}

function updateTuitionFee($id, $data) {
    global $conn;
    
    $sql = "UPDATE tuition_fees SET 
            amount = ?,
            due_date = ?,
            payment_status = ?,
            paid_amount = ?,
            payment_date = ?,
            payment_method = ?,
            notes = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("dssdsssi",
        $data['amount'],
        $data['due_date'],
        $data['payment_status'],
        $data['paid_amount'],
        $data['payment_date'],
        $data['payment_method'],
        $data['notes'],
        $id
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    // If payment status changed to 'paid', send notification
    if ($data['payment_status'] === 'paid') {
        $notification = [
            'type' => 'payment_confirmation',
            'title' => 'Payment Received',
            'message' => sprintf(
                'We have received your payment of %s. Thank you!',
                number_format($data['paid_amount'], 2)
            ),
            'recipient_type' => 'student',
            'recipient_id' => $data['student_id']
        ];
        addNotification($notification);
    }
    
    return ['success' => true];
}

function deleteTuitionFee($id) {
    global $conn;
    
    $sql = "DELETE FROM tuition_fees WHERE id = ?";
    
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

function getTuitionReport($filters = []) {
    global $conn;
    
    $sql = "SELECT 
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN paid_amount ELSE 0 END), 0) as total_paid,
                COALESCE(SUM(CASE WHEN payment_status != 'paid' THEN amount - paid_amount ELSE 0 END), 0) as total_pending,
                COUNT(*) as total_fees,
                COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_fees,
                COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_fees,
                COUNT(CASE WHEN payment_status = 'partial' THEN 1 END) as partial_fees
            FROM tuition_fees
            WHERE 1=1";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND due_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND due_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    return $result->fetch_assoc();
}

// Helper function to send reminders for pending payments
function sendPaymentReminders() {
    global $conn;
    
    $sql = "SELECT tf.*, s.name as student_name, c.name as course_name
            FROM tuition_fees tf
            JOIN students s ON tf.student_id = s.id
            JOIN courses c ON tf.course_id = c.id
            WHERE tf.payment_status != 'paid'
            AND tf.due_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    while ($row = $result->fetch_assoc()) {
        $notification = [
            'type' => 'payment_reminder',
            'title' => 'Payment Reminder',
            'message' => sprintf(
                'Reminder: Your payment of %s for %s is due on %s',
                number_format($row['amount'] - $row['paid_amount'], 2),
                $row['course_name'],
                date('d/m/Y', strtotime($row['due_date']))
            ),
            'recipient_type' => 'student',
            'recipient_id' => $row['student_id']
        ];
        addNotification($notification);
    }
    
    return ['success' => true];
}