<?php
require_once('db.php');

function getExpenses($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM expenses WHERE 1=1";
    
    if (!empty($filters['category'])) {
        $sql .= " AND category = '" . $conn->real_escape_string($filters['category']) . "'";
    }
    if (!empty($filters['date_from'])) {
        $sql .= " AND expense_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND expense_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $sql .= " ORDER BY expense_date DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
    
    return $expenses;
}

function addExpense($data) {
    global $conn;
    
    $sql = "INSERT INTO expenses (category, description, amount, expense_date, payment_method, paid_by, receipt_no, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("ssdsssss", 
        $data['category'],
        $data['description'],
        $data['amount'],
        $data['expense_date'],
        $data['payment_method'],
        $data['paid_by'],
        $data['receipt_no'],
        $data['notes']
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true, 'id' => $stmt->insert_id];
}

function updateExpense($id, $data) {
    global $conn;
    
    $sql = "UPDATE expenses SET 
            category = ?,
            description = ?,
            amount = ?,
            expense_date = ?,
            payment_method = ?,
            paid_by = ?,
            receipt_no = ?,
            notes = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $stmt->bind_param("ssdsssssi",
        $data['category'],
        $data['description'],
        $data['amount'],
        $data['expense_date'],
        $data['payment_method'],
        $data['paid_by'],
        $data['receipt_no'],
        $data['notes'],
        $id
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true];
}

function deleteExpense($id) {
    global $conn;
    
    $sql = "DELETE FROM expenses WHERE id = ?";
    
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

function getExpenseCategories() {
    return [
        'utilities' => 'Tiện ích',
        'rent' => 'Tiền thuê',
        'salary' => 'Lương',
        'supplies' => 'Văn phòng phẩm',
        'equipment' => 'Thiết bị',
        'marketing' => 'Marketing',
        'maintenance' => 'Bảo trì',
        'others' => 'Khác'
    ];
}

function getExpenseReport($filters = []) {
    global $conn;
    
    $sql = "SELECT 
                category,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                MIN(expense_date) as first_expense,
                MAX(expense_date) as last_expense
            FROM expenses
            WHERE 1=1";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND expense_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND expense_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $sql .= " GROUP BY category ORDER BY total_amount DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $report = [
        'by_category' => [],
        'total_amount' => 0,
        'total_count' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $report['by_category'][] = $row;
        $report['total_amount'] += $row['total_amount'];
        $report['total_count'] += $row['count'];
    }
    
    return $report;
}