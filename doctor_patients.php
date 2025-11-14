<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php?error=access_denied");
    exit();
}
$doctor_id = $_SESSION['user_id'];

// Fetch all unique patients who have booked with this doctor
$sql = "
    SELECT DISTINCT
        U.name AS patient_name, 
        U.phone_number,
        U.email,
        P.date_of_birth
    FROM Appointments A
    JOIN Users U ON A.patient_id = U.user_id
    JOIN Patients P ON U.user_id = P.patient_id
    WHERE A.doctor_id = ? 
    ORDER BY U.name ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Patients</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .page-wrapper { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .sidebar { background-color: #f7f9fa; color: #4a4a4a; padding: 20px 0; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); position: sticky; top: 0; height: 100vh; z-index: 2000; }
        .sidebar a { color: #4a4a4a; padding: 12px 30px; display: flex; align-items: center; font-weight: 500; transition: background-color 0.2s; border-left: 3px solid transparent; }
        .sidebar a:hover, .current-page-header { background-color: #e6f7ff; border-left: 3px solid #0077b6; color: #0077b6; }
        .sidebar i { margin-right: 15px; font-size: 1.1em; }
        .main-content { padding: 30px 40px; }
        .patient-list-card {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="sidebar">
            <div class="sidebar-header">NK Hospitals</div>
            <a href="doctor_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="doctor_prescriptions.php"><i class="fas fa-clipboard-list"></i> Prescriptions</a>
            <a href="doctor_patients.php" class="current-page-header"><i class="fas fa-user-injured"></i> Patients</a>
            <a href="doctor_calendar.php"><i class="fas fa-calendar-alt"></i> Schedule & Events</a>
            <a href="doctor_settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="role_select.php" style="margin-top: 30px; color: #ff6b81;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="main-content">
            <h2 style="color: #4a4a4a;">Patients You Have Consulted</h2>
            <p>This list includes all patients with a history of booking appointments with you.</p>

            <div class="patient-list-card">
                <?php if (empty($patients)): ?>
                    <p style="text-align: center; color: #666;">No patient history found yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 25%;">Name</th>
                                <th style="width: 25%;">Email</th>
                                <th style="width: 20%;">Phone</th>
                                <th style="width: 20%;">Date of Birth</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($p['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['email']); ?></td>
                                <td><?php echo htmlspecialchars($p['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($p['date_of_birth']); ?></td>
                                <td><a href="#" style="color: #0077b6;">View History</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>