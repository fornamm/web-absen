<?php
// $db_host = 'localhost';
// $db_user = 'root';
// $db_pass = '';
// $db_name = 'db_absensi';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>