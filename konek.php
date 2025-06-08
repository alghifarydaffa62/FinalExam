<?php
require_once __DIR__ . '/../vendor/autoload.php'; // naik 1 level ke root

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$host = $_ENV["MYSQLHOST"] ?? getenv("MYSQLHOST");
$user = $_ENV["MYSQLUSER"] ?? getenv("MYSQLUSER");
$pass = $_ENV["MYSQLPASSWORD"] ?? getenv("MYSQLPASSWORD");
$db   = $_ENV["MYSQLDATABASE"] ?? getenv("MYSQLDATABASE");
$port = $_ENV["MYSQLPORT"] ?? getenv("MYSQLPORT");

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi gagal! " . mysqli_connect_error());
}
?>