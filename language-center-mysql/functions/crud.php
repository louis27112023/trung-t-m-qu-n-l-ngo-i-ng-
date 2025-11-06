<?php
// Generic CRUD helpers for the language-center project
// Usage: require_once __DIR__ . '/db.php'; require_once __DIR__ . '/crud.php';
// $conn = getDbConnection();

// --- Courses ---
function add_course($conn, $name, $level = null, $price = 0.0, $duration = 0, $status = 'active'){
    $sql = "INSERT INTO courses (name, level, price, duration, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if(!$stmt) return false;
    $price = is_numeric($price) ? (float)$price : 0.0;
    $duration = is_numeric($duration) ? intval($duration) : 0;
    mysqli_stmt_bind_param($stmt, 'ssdis', $name, $level, $price, $duration, $status);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function get_course($conn, $id){
    $sql = "SELECT * FROM courses WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return null;
    mysqli_stmt_bind_param($stmt,'i',$id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function get_courses($conn, $limit = 500){
    $sql = "SELECT * FROM courses ORDER BY id DESC LIMIT " . intval($limit);
    $res = mysqli_query($conn,$sql);
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function update_course($conn, $id, $name, $level = null, $price = 0.0, $duration = 0, $status = 'active'){
    $sql = "UPDATE courses SET name = ?, level = ?, price = ?, duration = ?, status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    
    // Ensure price is a valid number
    $price = empty($price) ? 0.0 : (is_numeric($price) ? (float)$price : 0.0);
    $duration = empty($duration) ? 0 : (is_numeric($duration) ? intval($duration) : 0);
    
    mysqli_stmt_bind_param($stmt,'ssdisi',$name,$level,$price,$duration,$status,$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_course($conn, $id){
    $sql = "DELETE FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'i',$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// --- Schedules ---
function add_schedule($conn, $course_id, $teacher, $room, $start_at, $end_at = null, $note = null, $status = 'scheduled'){
    $sql = "INSERT INTO schedules (course_id, teacher, room, start_at, end_at, note, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    $course_id = is_numeric($course_id) ? intval($course_id) : null;
    mysqli_stmt_bind_param($stmt,'issssss', $course_id, $teacher, $room, $start_at, $end_at, $note, $status);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function get_schedule($conn,$id){
    $sql = "SELECT * FROM schedules WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return null;
    mysqli_stmt_bind_param($stmt,'i',$id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function get_schedules($conn,$limit = 500){
    $sql = "SELECT * FROM schedules ORDER BY start_at DESC LIMIT " . intval($limit);
    $res = mysqli_query($conn,$sql);
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function update_schedule($conn, $id, $course_id, $teacher, $room, $start_at, $end_at = null, $note = null, $status = 'scheduled'){
    $sql = "UPDATE schedules SET course_id=?, teacher=?, room=?, start_at=?, end_at=?, note=?, status=? WHERE id=?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    $course_id = is_numeric($course_id) ? intval($course_id) : null;
    mysqli_stmt_bind_param($stmt,'issssssi',$course_id,$teacher,$room,$start_at,$end_at,$note,$status,$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_schedule($conn,$id){
    $sql = "DELETE FROM schedules WHERE id = ?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'i',$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// --- Payments ---
function add_payment($conn, $student_id = null, $course_id = null, $amount = 0.0, $payment_date = null, $method = null, $note = null){
    $payment_date = $payment_date ?? date('Y-m-d H:i:s');
    $sql = "INSERT INTO payments (student_id, course_id, amount, payment_date, method, note) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    $student_id = is_numeric($student_id) ? intval($student_id) : null;
    $course_id = is_numeric($course_id) ? intval($course_id) : null;
    $amount = is_numeric($amount) ? (float)$amount : 0.0;
    mysqli_stmt_bind_param($stmt,'iidsis',$student_id,$course_id,$amount,$payment_date,$method,$note);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function get_payment($conn,$id){
    $sql = "SELECT * FROM payments WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return null;
    mysqli_stmt_bind_param($stmt,'i',$id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function get_payments($conn,$limit = 500){
    $sql = "SELECT * FROM payments ORDER BY payment_date DESC LIMIT " . intval($limit);
    $res = mysqli_query($conn,$sql);
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function update_payment($conn,$id,$student_id=null,$course_id=null,$amount=0.0,$payment_date=null,$method=null,$note=null){
    $sql = "UPDATE payments SET student_id=?, course_id=?, amount=?, payment_date=?, method=?, note=? WHERE id=?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    $student_id = is_numeric($student_id) ? intval($student_id) : null;
    $course_id = is_numeric($course_id) ? intval($course_id) : null;
    $amount = is_numeric($amount) ? (float)$amount : 0.0;
    mysqli_stmt_bind_param($stmt,'iidsisi',$student_id,$course_id,$amount,$payment_date,$method,$note,$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_payment($conn,$id){
    $sql = "DELETE FROM payments WHERE id = ?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'i',$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// --- Settings ---
function set_setting($conn, $key, $value, $description = null){
    $sql = "INSERT INTO settings (key_name, value, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value), description = VALUES(description), updated_at = CURRENT_TIMESTAMP";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'sss',$key,$value,$description);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function get_setting($conn, $key){
    $sql = "SELECT * FROM settings WHERE key_name = ? LIMIT 1";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return null;
    mysqli_stmt_bind_param($stmt,'s',$key);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function add_setting($conn,$key,$value,$description=null){
    $sql = "INSERT INTO settings (key_name,value,description) VALUES (?,?,?)";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'sss',$key,$value,$description);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function update_setting($conn,$id,$key,$value,$description=null){
    $sql = "UPDATE settings SET key_name=?, value=?, description=? WHERE id=?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'sssi',$key,$value,$description,$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delete_setting($conn,$id){
    $sql = "DELETE FROM settings WHERE id = ?";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'i',$id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// --- Transaction helper ---
function run_transaction($conn, callable $fn){
    mysqli_begin_transaction($conn);
    try{
        $res = $fn($conn);
        mysqli_commit($conn);
        return $res;
    } catch(Exception $e){
        mysqli_rollback($conn);
        throw $e;
    }
}

?>