<?php
// Format currency to VND
function lc_format_vnd($n) { 
    return number_format((float)$n, 0, ',', '.') . ' ₫'; 
}

// Format date/time
function lc_format_date($date) {
    return date('d/m/Y', strtotime($date));
}

function lc_format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Sanitize inputs
function lc_escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Flash messages
function lc_set_flash($message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = $message;
    }
}

function lc_get_flash() {
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}

// CSRF protection
function lc_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    return $_SESSION['csrf_token'];
}

function lc_verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Search filter
function lc_filter_rows($rows, $q) {
    if (!$q) return $rows;
    $q = mb_strtolower($q);
    return array_values(array_filter($rows, function($row) use($q) {
        $str = mb_strtolower(json_encode($row, JSON_UNESCAPED_UNICODE));
        return mb_strpos($str, $q) !== false;
    }));
}

// Database helpers
function lc_table_exists($conn, $table) {
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    return $result && mysqli_num_rows($result) > 0;
}

function lc_get_table_columns($conn, $table) {
    if (!lc_table_exists($conn, $table)) return [];
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `" . mysqli_real_escape_string($conn, $table) . "`");
    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row;
    }
    return $columns;
}
?>