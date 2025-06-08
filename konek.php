<?php
// Versi simple tanpa dotenv - untuk Railway deployment
$host = getenv("MYSQLHOST") ?: "localhost";
$user = getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: "";
$db   = getenv("MYSQLDATABASE") ?: "railway";
$port = getenv("MYSQLPORT") ?: "3306";

// Debug info (hapus setelah berhasil)
echo "<!-- DB Connection Debug: Host=$host, User=$user, DB=$db, Port=$port -->";

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi database gagal! " . mysqli_connect_error());
}

// Success message (hapus setelah berhasil)
echo "<!-- Database connected successfully! -->";
?>