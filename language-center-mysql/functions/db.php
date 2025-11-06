<?php
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "09012005";
    $dbname = "nna";
    $port = 3306;

    $conn = mysqli_connect($servername, $username, $password, $dbname, $port);
    if (!$conn) {
        die("Kết nối database thất bại: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8");
    return $conn;
}
?>