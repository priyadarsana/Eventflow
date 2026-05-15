<?php
// includes/db.php - Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "eventflow";

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("<h2 style='color:red;padding:20px'>❌ DB Error: " . mysqli_connect_error() . " — Start MySQL in XAMPP!</h2>");
}
mysqli_set_charset($conn, "utf8");
?>
