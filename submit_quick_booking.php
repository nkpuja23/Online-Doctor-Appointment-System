<?php
require_once 'db_connect.php'; 
session_start();

// --- LEAD DATA RETRIEVAL (Removed CAPTCHA variables) ---
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$preferred_date = $_POST['preferred_date'] ?? '';
$specialization_id = $_POST['specialization_id'] ?? '';

// --- VALIDATION (Only checks for required fields now) ---

if (empty($name) || empty($phone) || empty($preferred_date) || empty($specialization_id)) {
    // Redirect back to the role select page with an error
    $msg = urlencode("Error: Please fill all required fields.");
    header("Location: role_select.php?qbook_msg={$msg}#quick-booking");
    exit();
}

// --- SIMULATION SUCCESS / LEAD RECORDING ---
// NOTE: We no longer check or regenerate CAPTCHA.

$success_msg = urlencode("Thank you, {$name}! Your request has been recorded. Our team will contact you shortly.");

// CRITICAL REDIRECT: Send the user to the dedicated success page, passing the custom message
header("Location: booking_success.php?msg={$success_msg}");
exit();
?>
