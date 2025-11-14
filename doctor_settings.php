<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php?error=access_denied");
    exit();
}
$doctor_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .page-wrapper { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .sidebar { background-color: #f7f9fa; color: #4a4a4a; padding: 20px 0; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); position: sticky; top: 0; height: 100vh; z-index: 2000; }
        .sidebar a { color: #4a4a4a; padding: 12px 30px; display: flex; align-items: center; font-weight: 500; transition: background-color 0.2s; border-left: 3px solid transparent; }
        .sidebar a:hover, .current-page-header { background-color: #e6f7ff; border-left: 3px solid #0077b6; color: #0077b6; }
        .sidebar i { margin-right: 15px; font-size: 1.1em; }
        .main-content { padding: 30px 40px; }
        .settings-card {
            background-color: #e6f7ff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="sidebar">
            <div class="sidebar-header">NK Hospitals</div>
            <a href="doctor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="doctor_prescriptions.php"><i class="fas fa-clipboard-list"></i> Prescriptions</a>
            <a href="doctor_patients.php"><i class="fas fa-user-injured"></i> Patients</a>
            <a href="doctor_calendar.php"><i class="fas fa-calendar-alt"></i> Schedule & Events</a>
            <a href="doctor_settings.php" class="current-page-header"><i class="fas fa-cog"></i> Settings</a>
            <a href="role_select.php" style="margin-top: 30px; color: #ff6b81;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="main-content">
            <h2 style="color: #4a4a4a;">Account Settings</h2>
            <p>Manage your account preferences and availability settings here.</p>

            <div class="settings-card">
                <h3 style="color: #0077b6;">General Preferences</h3>
                <p>Features to be implemented:</p>
                <ul>
                    <li>Change Password (Requires security checks)</li>
                    <li>Update Clinic Affiliation</li>
                    <li>Set Profile Visibility</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
