<?php
session_start(); 

$servername = "localhost";
$username = "root";
$password = ""; // <-- VERIFY YOUR PASSWORD
$dbname = "doctor_appointment_db";
$port = 3306; // <-- VERIFY YOUR PORT (e.g., 3307)

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function redirect_to_dashboard($role_id) {
    if ($role_id == 1) { // Admin
        header("Location: admin_dashboard.php");
    } elseif ($role_id == 2) { // Doctor
        header("Location: doctor_dashboard.php");
    } elseif ($role_id == 3) { // Patient
        header("Location: patient_dashboard.php");
    } else {
        header("Location: index.php?error=invalid_role");
    }
    exit();
}
?>