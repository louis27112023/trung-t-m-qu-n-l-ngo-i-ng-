<?php
require_once('db.php');

function getFinancialReport($filters = []) {
    global $conn;
    
    $report = [
        'income' => [
            'tuition' => getTuitionIncome($filters),
            'total' => 0
        ],
        'expenses' => [
            'salary' => getSalaryExpense($filters),
            'operation' => getOperationExpense($filters),
            'total' => 0
        ],
        'profit' => 0,
        'statistics' => [
            'students' => getActiveStudents($filters),
            'teachers' => getActiveTeachers($filters),
            'classes' => getActiveClasses($filters)
        ],
        'trends' => getMonthlyTrends($filters)
    ];
    
    // Calculate totals
    $report['income']['total'] = $report['income']['tuition'];
    $report['expenses']['total'] = $report['expenses']['salary'] + $report['expenses']['operation'];
    $report['profit'] = $report['income']['total'] - $report['expenses']['total'];
    
    return $report;
}

function getTuitionIncome($filters) {
    global $conn;
    
    $sql = "SELECT COALESCE(SUM(paid_amount), 0) as total 
            FROM tuition_fees 
            WHERE payment_status IN ('partial', 'paid')";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND payment_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND payment_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return floatval($row['total']);
}

function getSalaryExpense($filters) {
    global $conn;
    
    $sql = "SELECT COALESCE(SUM(total_salary), 0) as total 
            FROM salaries 
            WHERE payment_status = 'paid'";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND payment_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND payment_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return floatval($row['total']);
}

function getOperationExpense($filters) {
    global $conn;
    
    $sql = "SELECT COALESCE(SUM(amount), 0) as total 
            FROM expenses";
    
    if (!empty($filters['date_from'])) {
        $sql .= " WHERE expense_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND expense_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return floatval($row['total']);
}

function getActiveStudents($filters) {
    global $conn;
    
    $sql = "SELECT COUNT(DISTINCT student_id) as total 
            FROM tuition_fees 
            WHERE payment_status IN ('partial', 'paid')";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND payment_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND payment_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return intval($row['total']);
}

function getActiveTeachers($filters) {
    global $conn;
    
    $sql = "SELECT COUNT(DISTINCT teacher_id) as total 
            FROM salaries 
            WHERE payment_status = 'paid'";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND payment_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND payment_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return intval($row['total']);
}

function getActiveClasses($filters) {
    global $conn;
    
    $sql = "SELECT COUNT(DISTINCT course_id) as total 
            FROM tuition_fees 
            WHERE payment_status IN ('partial', 'paid')";
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND payment_date >= '" . $conn->real_escape_string($filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND payment_date <= '" . $conn->real_escape_string($filters['date_to']) . "'";
    }
    
    $result = $conn->query($sql);
    if ($result === false) {
        return 0;
    }
    
    $row = $result->fetch_assoc();
    return intval($row['total']);
}

function getMonthlyTrends($filters) {
    global $conn;
    
    $months = [];
    $currentDate = new DateTime($filters['date_from'] ?? '-12 months');
    $endDate = new DateTime($filters['date_to'] ?? 'now');
    
    while ($currentDate <= $endDate) {
        $month = $currentDate->format('Y-m');
        $months[$month] = [
            'income' => 0,
            'expenses' => 0,
            'profit' => 0
        ];
        $currentDate->modify('+1 month');
    }
    
    // Get monthly income
    $sql = "SELECT 
                DATE_FORMAT(payment_date, '%Y-%m') as month,
                SUM(paid_amount) as income
            FROM tuition_fees 
            WHERE payment_status IN ('partial', 'paid')
            AND payment_date >= '" . array_key_first($months) . "-01'
            AND payment_date <= '" . array_key_last($months) . "-31'
            GROUP BY month";
    
    $result = $conn->query($sql);
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            if (isset($months[$row['month']])) {
                $months[$row['month']]['income'] = floatval($row['income']);
            }
        }
    }
    
    // Get monthly expenses (salaries + operation)
    $sql = "SELECT 
                DATE_FORMAT(payment_date, '%Y-%m') as month,
                SUM(total_salary) as expenses
            FROM salaries 
            WHERE payment_status = 'paid'
            AND payment_date >= '" . array_key_first($months) . "-01'
            AND payment_date <= '" . array_key_last($months) . "-31'
            GROUP BY month";
    
    $result = $conn->query($sql);
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            if (isset($months[$row['month']])) {
                $months[$row['month']]['expenses'] += floatval($row['expenses']);
            }
        }
    }
    
    $sql = "SELECT 
                DATE_FORMAT(expense_date, '%Y-%m') as month,
                SUM(amount) as expenses
            FROM expenses
            WHERE expense_date >= '" . array_key_first($months) . "-01'
            AND expense_date <= '" . array_key_last($months) . "-31'
            GROUP BY month";
    
    $result = $conn->query($sql);
    if ($result !== false) {
        while ($row = $result->fetch_assoc()) {
            if (isset($months[$row['month']])) {
                $months[$row['month']]['expenses'] += floatval($row['expenses']);
            }
        }
    }
    
    // Calculate profit for each month
    foreach ($months as $month => $data) {
        $months[$month]['profit'] = $data['income'] - $data['expenses'];
    }
    
    return $months;
}

function saveFinancialReport($data) {
    global $conn;
    
    $sql = "INSERT INTO financial_reports (
                report_type, month, year,
                total_amount, details,
                generated_by, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $details = json_encode($data['details']);
    
    $stmt->bind_param("siidsss",
        $data['report_type'],
        $data['month'],
        $data['year'],
        $data['total_amount'],
        $details,
        $data['generated_by'],
        $data['notes']
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    return ['success' => true, 'id' => $stmt->insert_id];
}

function getFinancialReports($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM financial_reports WHERE 1=1";
    
    if (!empty($filters['report_type'])) {
        $sql .= " AND report_type = '" . $conn->real_escape_string($filters['report_type']) . "'";
    }
    if (!empty($filters['year'])) {
        $sql .= " AND year = " . intval($filters['year']);
    }
    if (!empty($filters['month'])) {
        $sql .= " AND month = " . intval($filters['month']);
    }
    
    $sql .= " ORDER BY generated_at DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return [];
    }
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $row['details'] = json_decode($row['details'], true);
        $reports[] = $row;
    }
    
    return $reports;
}