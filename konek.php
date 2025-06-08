<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ambil nilai dari .env
$host = $_ENV["MYSQLHOST"];
$user = $_ENV["MYSQLUSER"];
$pass = $_ENV["MYSQLPASSWORD"];
$db   = $_ENV["MYSQLDATABASE"];
$port = $_ENV["MYSQLPORT"];

// Koneksi ke DB
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi gagal! " . mysqli_connect_error());
}
