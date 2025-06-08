<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("/Final/Exam");
$dotenv->load();

$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi gagal! " . mysqli_connect_error());
}

?>