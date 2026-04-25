<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aral_site";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Ulanishda xatolik: " . $conn->connect_error);
}
?>
