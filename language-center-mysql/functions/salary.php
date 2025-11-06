<?php
require_once('db.php');
require_once('notification.php');

function getSalaries($filters = []) {
    global $conn;
    
    $sql = "SELECT s.*, t.name as teacher_name 
            FROM salaries s
            JOIN teachers t ON s.teacher_id = t.id
            WHERE 1=1";
    
    if (!empty($filters['teacher_id'])) {
        $sql .= " AND s.teacher_id = " . intval($filters['teacher_id']);
    }
    if (!empty($filters['month'])) {
        $sql .= " AND s.month = " . intval($filters['month']);
    }
    if (!empty($filters['year'])) {
        $sql .= " AND s.year = " . intval($filters['year']);
    }
    if (!empty($filters['payment_status'])) {
        $sql .= " AND s.payment_status = '" . $conn->real_escape_string($filters['payment_status']) . "'";
    }
    
    $sql .= " ORDER BY s.year DESC, s.month DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $salaries = [];
    while ($row = $result->fetch_assoc()) {
        $salaries[] = $row;
    }
    
    return $salaries;
}

function calculateSalary($teacherId, $month, $year) {
    global $conn;
    
    // Get teacher's base rate and hourly rate
    $sql = "SELECT base_salary, hourly_rate FROM teachers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    
    // Calculate teaching hours for the month
    $sql = "SELECT SUM(duration) as total_hours 
            FROM schedule 
            WHERE teacher_id = ? 
            AND MONTH(date) = ? 
            AND YEAR(date) = ?
            AND status = 'completed'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $teacherId, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $hours = $result->fetch_assoc();
    
    // Calculate salary components
    $baseSalary = $teacher['base_salary'];
    $teachingHours = $hours['total_hours'] ?? 0;
    $hourlyRate = $teacher['hourly_rate'];
    $teachingPay = $teachingHours * $hourlyRate;
    
    // Default bonus and deductions to 0
    $bonus = 0;
    $deductions = 0;
    
    // Calculate total
    $totalSalary = $baseSalary + $teachingPay + $bonus - $deductions;
    
    return [
        'base_salary' => $baseSalary,
        'teaching_hours' => $teachingHours,
        'hourly_rate' => $hourlyRate,
        'bonus' => $bonus,
        'deductions' => $deductions,
        'total_salary' => $totalSalary
    ];
}

function addSalary($data) {
    global $conn;
    
    // First calculate the salary if not provided
    if (empty($data['total_salary'])) {
        $calculated = calculateSalary($data['teacher_id'], $data['month'], $data['year']);
        $data = array_merge($data, $calculated);
    }
    
    $sql = "INSERT INTO salaries (
                teacher_id, month, year, 
                base_salary, teaching_hours, hourly_rate,
                bonus, deductions, total_salary,
                payment_status, payment_date, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    $paymentStatus = $data['payment_status'] ?? 'pending';
    $paymentDate = $data['payment_date'] ?? null;
    
    $stmt->bind_param("iiidddddsss", 
        $data['teacher_id'],
        $data['month'],
        $data['year'],
        $data['base_salary'],
        $data['teaching_hours'],
        $data['hourly_rate'],
        $data['bonus'],
        $data['deductions'],
        $data['total_salary'],
        $paymentStatus,
        $paymentDate,
        $data['notes']
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    // Send notification to teacher
    $notification = [
        'type' => 'salary',
        'title' => 'Salary Statement Available',
        'message' => sprintf(
            'Your salary statement for %s/%s is now available. Total amount: %s',
            $data['month'],
            $data['year'],
            number_format($data['total_salary'], 0)
        ),
        'recipient_type' => 'teacher',
        'recipient_id' => $data['teacher_id']
    ];
    addNotification($notification);
    
    return ['success' => true, 'id' => $stmt->insert_id];
}

function updateSalary($id, $data) {
    global $conn;
    
    $sql = "UPDATE salaries SET 
            base_salary = ?,
            teaching_hours = ?,
            hourly_rate = ?,
            bonus = ?,
            deductions = ?,
            total_salary = ?,
            payment_status = ?,
            payment_date = ?,
            notes = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => $conn->error];
    }
    
    // Update total salary
    $data['total_salary'] = $data['base_salary'] + 
                           ($data['teaching_hours'] * $data['hourly_rate']) + 
                           $data['bonus'] - 
                           $data['deductions'];
    
    $stmt->bind_param("ddddddssi",
        $data['base_salary'],
        $data['teaching_hours'],
        $data['hourly_rate'],
        $data['bonus'],
        $data['deductions'],
        $data['total_salary'],
        $data['payment_status'],
        $data['payment_date'],
        $data['notes'],
        $id
    );
    
    if (!$stmt->execute()) {
        return ['error' => $stmt->error];
    }
    
    // If payment status changed to 'paid', send notification
    if ($data['payment_status'] === 'paid') {
        $notification = [
            'type' => 'salary_payment',
            'title' => 'Salary Payment Confirmation',
            'message' => sprintf(
                'Your salary payment of %s has been processed.',
                number_format($data['total_salary'], 0)
            ),
            'recipient_type' => 'teacher',
            'recipient_id' => $data['teacher_id']
        ];
        addNotification($notification);
    }
    
    return ['success' => true];
}

function deleteSalary($id) {
    global $conn;
    
    $sql = "DELETE FROM salaries WHERE id = ?";
    
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

function getSalaryReport($filters = []) {
    global $conn;
    
    $sql = "SELECT 
                t.name as teacher_name,
                COUNT(*) as payment_count,
                SUM(s.total_salary) as total_paid,
                AVG(s.total_salary) as average_salary,
                SUM(s.teaching_hours) as total_hours
            FROM salaries s
            JOIN teachers t ON s.teacher_id = t.id
            WHERE 1=1";
    
    if (!empty($filters['year'])) {
        $sql .= " AND s.year = " . intval($filters['year']);
    }
    
    $sql .= " GROUP BY s.teacher_id ORDER BY total_paid DESC";
    
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    
    $report = [
        'by_teacher' => [],
        'total_paid' => 0,
        'total_hours' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $report['by_teacher'][] = $row;
        $report['total_paid'] += $row['total_paid'];
        $report['total_hours'] += $row['total_hours'];
    }
    
    return $report;
}