<?php
// Load .env file manually if exists
if (file_exists(__DIR__ . '/.env')) {
    $env_content = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $env_content);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Get database config - try multiple variable names
$host = getenv("MYSQLHOST") ?: $_ENV["MYSQLHOST"] ?? "localhost";
$user = getenv("MYSQLUSER") ?: $_ENV["MYSQLUSER"] ?? "root";
$pass = getenv("MYSQL_ROOT_PASSWORD") ?: getenv("MYSQLPASSWORD") ?: $_ENV["MYSQL_ROOT_PASSWORD"] ?? $_ENV["MYSQLPASSWORD"] ?? "";
$db   = getenv("MYSQL_DATABASE") ?: getenv("MYSQLDATABASE") ?: $_ENV["MYSQL_DATABASE"] ?? $_ENV["MYSQLDATABASE"] ?? "railway";
$port = getenv("MYSQLPORT") ?: $_ENV["MYSQLPORT"] ?? "3306";

// Debug info (hapus setelah berhasil)
echo "<br>DB Connection Debug:<br>";
echo "Host: " . ($host ?: 'NOT SET') . "<br>";
echo "User: " . ($user ?: 'NOT SET') . "<br>";  
echo "Database: " . ($db ?: 'NOT SET') . "<br>";
echo "Port: " . ($port ?: 'NOT SET') . "<br>";
echo "Password: " . (empty($pass) ? 'NOT SET' : 'SET') . "<br><br>";

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if(!$conn) {
    die("Koneksi database gagal! " . mysqli_connect_error());
}

// Success message (hapus setelah berhasil)
echo "<!-- Database connected successfully! -->";
?>