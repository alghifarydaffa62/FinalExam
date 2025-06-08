<?php

$host = "sql105.infinityfree.com";
$user = "if0_39172627";
$pass = "PkZPflF6lpOfLl";
$db   = "if0_39172627_perpustakaan";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn) {
    die("Koneksi database gagal! " . mysqli_connect_error());
}

?>