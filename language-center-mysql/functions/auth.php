<?php
require_once __DIR__ . '/db.php';

// Admin user helpers
function create_admin($conn, $username, $password, $name = null, $role = 'admin'){
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO admins (username, password_hash, name, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return false;
    mysqli_stmt_bind_param($stmt,'ssss',$username,$hash,$name,$role);
    $ok = mysqli_stmt_execute($stmt);
    $id = $ok ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $id;
}

function find_admin_by_username($conn, $username){
    $sql = "SELECT * FROM admins WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn,$sql);
    if(!$stmt) return null;
    mysqli_stmt_bind_param($stmt,'s',$username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function verify_admin_credentials($conn, $username, $password){
    $user = find_admin_by_username($conn, $username);
    if(!$user) return false;
    if(!isset($user['password_hash'])) return false;
    if(password_verify($password, $user['password_hash'])){
        // successful
        unset($user['password_hash']);
        return $user;
    }
    return false;
}

?>