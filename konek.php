<?php
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Panggil function untuk load
loadEnv(__DIR__ . '/.env');

// Ambil nilai
$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi gagal!" . mysqli_connect_error());
}

// echo "Koneksi berhasil!";
?>