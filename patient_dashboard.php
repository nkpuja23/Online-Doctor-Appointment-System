<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Patients
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: index.php?error=access_denied");
    exit();
}

$patient_id = $_SESSION['user_id'];
$patient_name = '';
$appointments = [];
// Message handling: receives cancellation success/error message
$message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; 

// 1. Fetch patient name for greeting
$stmt_name = $conn->prepare("SELECT name FROM Users WHERE user_id = ?");
$stmt_name->bind_param("i", $patient_id);
$stmt_name->execute();
$patient_name = $stmt_name->get_result()->fetch_assoc()['name'];
$stmt_name->close();

// 2. Fetch all appointments using complex JOINs
$sql = "
    SELECT 
        A.appointment_id, A.appointment_date, A.appointment_time, 
        D_User.name AS doctor_name, S.specialization_name, 
        C.name AS clinic_name, AS_Status.status_name
    FROM Appointments A
    JOIN Users D_User ON A.doctor_id = D_User.user_id
    JOIN Doctors DR ON A.doctor_id = DR.doctor_id
    JOIN Specializations S ON DR.specialization_id = S.specialization_id
    JOIN Doctor_Schedules DSC ON A.schedule_id = DSC.schedule_id
    JOIN Clinics C ON DSC.clinic_id = C.clinic_id
    JOIN Appointment_Status AS_Status ON A.status_id = AS_Status.status_id
    WHERE A.patient_id = ?
    ORDER BY A.appointment_date ASC, A.appointment_time ASC
";
$stmt_appts = $conn->prepare($sql);
$stmt_appts->bind_param("i", $patient_id);
$stmt_appts->execute();
$appointments = $stmt_appts->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_appts->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Dashboard Container Fix (Ensures the content uses full width) */
        .dashboard-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
        }
        
        /* Table specific styles */
        table {
            font-size: 0.9em;
            table-layout: fixed; 
            width: 100%;
        }
        th, td {
            word-wrap: break-word; 
            padding: 10px 5px;
        }
        
        /* Button Group Styling */
        .button-group {
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        /* Revert header bar to standard centered logo display */
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
        }
    </style>
</head>
<body>
    <div class="container full-screen">
        
        <!-- HEADER BAR (Standard Full Width) -->
        <div class="header-bar">
            
            <div style="display: flex; align-items: center;">
                <span class="logo-circle">NK</span>
                <h1>Patient Portal</h1>
            </div>
            
            <div style="display: flex; gap: 25px; align-items: center;">
                <div id="live-clock-display" class="live-clock"></div>
            </div>
        </div>

        <div class="dashboard-wrapper">
            <h2 style="color: #0077b6;">Payments & Account Management</h2>

            <!-- NAVIGATION LINKS -->
            <div class="button-group">
                <a href="patient_dashboard.php" class="button" style="background-color: #0077b6;"><i class="fas fa-calendar-alt"></i> My Appointments</a>
                <a href="book_appointment.php" class="button"><i class="fas fa-calendar-plus"></i> Book New Slot</a>
                <a href="patient_account.php" class="button"><i class="fas fa-user-edit"></i> Account & Profile</a>
                <a href="patient_payments.php" class="button"><i class="fas fa-wallet"></i> Payments History</a>
                <a href="role_select.php" class="button" style="background-color:#f44336;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <?php if ($message): 
                // Display success/error messages from the cancellation handler
                $class = strpos($message, 'Error') !== false || strpos($message, 'WARNING') !== false ? 'status-cancelled' : 'status-confirmed';
            ?>
                <p class="<?php echo $class; ?>" style="font-weight: bold; padding: 10px; border-radius: 5px;"><?php echo $message; ?></p>
            <?php endif; ?>

            <div class="centered-content" style="max-width: 100%; padding: 0;">
                <h2>Your Appointments History</h2>
                
                <?php if (empty($appointments)): ?>
                    <p>You have no appointments scheduled.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 10%;">Time</th>
                                <th style="width: 15%;">Doctor</th>
                                <th style="width: 15%;">Specialization</th>
                                <th style="width: 15%;">Clinic</th>
                                <th style="width: 15%;">Status</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($appointments as $appt): 
                            $status_class = strtolower(str_replace(' ', '-', $appt['status_name']));
                            
                            // Determine if the appointment is in the future and Confirmed (Status ID 2)
                            $appointment_datetime_str = $appt['appointment_date'] . ' ' . $appt['appointment_time'];
                            $appointment_timestamp = strtotime($appointment_datetime_str);
                            $is_future_confirmed = ($appointment_timestamp > time() && $status_class === 'confirmed');
                            
                            // Check if appointment is in the PAST or has been Cancelled/Completed
                            $is_history_item = ($appointment_timestamp < time() || $status_class === 'cancelled' || $status_class === 'completed');
                        ?>
                        <tr>
                            <td><?php echo $appt['appointment_date']; ?></td>
                            <td><?php echo date("h:i A", strtotime($appt['appointment_time'])); ?></td>
                            <td><?php echo $appt['doctor_name']; ?></td>
                            <td><?php echo $appt['specialization_name']; ?></td>
                            <td><?php echo $appt['clinic_name']; ?></td>
                            <td class="status-<?php echo $status_class; ?>"><strong><?php echo $appt['status_name']; ?></strong></td>
                            <td>
                                <?php if ($is_future_confirmed): ?>
                                    <!-- Link to the cancellation handler in book_appointment.php -->
                                    <a href="book_appointment.php?action=cancel&appt_id=<?php echo $appt['appointment_id']; ?>" 
                                       style="color: #dc3545;"
                                       onclick="return confirm('Are you sure you want to attempt to cancel this appointment?');">
                                        Cancel
                                    </a>
                                <?php elseif ($is_history_item): ?>
                                    <!-- Link for Past/Cancelled/Completed Items (Simple Deletion from view) -->
                                    <a href="book_appointment.php?action=remove_history&appt_id=<?php echo $appt['appointment_id']; ?>" 
                                       style="color: #6c757d;"
                                       onclick="return confirm('Remove this item from your history view? This action cannot be undone.');">
                                        Remove
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
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