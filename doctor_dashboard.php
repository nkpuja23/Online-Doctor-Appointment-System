<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php?error=access_denied");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = '';
$message = '';

// 1. Handle Appointment Completion Submission
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && isset($_GET['appt_id'])) {
    $appt_id = $_GET['appt_id'];
    $action = $_GET['action'];
    
    // Status ID 3 is 'Completed'
    if ($action == 'complete') {
        $status_id = 3; 

        // Update the Appointments table
        $stmt_update = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ? AND doctor_id = ?");
        $stmt_update->bind_param("iii", $status_id, $appt_id, $doctor_id);

        if ($stmt_update->execute()) {
            $message = "Appointment ID " . $appt_id . " marked as Completed!";
        } else {
            $message = "Error updating appointment: " . $conn->error;
        }
        $stmt_update->close();
    }
}

// 2. Fetch doctor name (We split the name for the welcome message)
$stmt_name = $conn->prepare("SELECT name FROM Users WHERE user_id = ?");
$stmt_name->bind_param("i", $doctor_id);
$stmt_name->execute();
$doctor_full_name = $stmt_name->get_result()->fetch_assoc()['name'];
$doctor_name_parts = explode(' ', $doctor_full_name);
$doctor_first_name = end($doctor_name_parts); // Get the last name or main name
$stmt_name->close();


// 3. Fetch Data for Dashboard (Schedule and Charts)
// Fetch Confirmed and Completed Appointments ONLY (for the main table list)
$sql = "
    SELECT 
        A.appointment_id, A.appointment_date, A.appointment_time, 
        U.name AS patient_name, AS_Status.status_name, 
        C.name AS clinic_name,
        A.status_id 
    FROM Appointments A
    JOIN Users U ON A.patient_id = U.user_id
    JOIN Appointment_Status AS_Status ON A.status_id = AS_Status.status_id
    JOIN Doctor_Schedules DSC ON A.schedule_id = DSC.schedule_id
    JOIN Clinics C ON DSC.clinic_id = C.clinic_id
    WHERE A.doctor_id = ? AND A.status_id IN (2, 3) 
    ORDER BY A.appointment_date ASC, A.appointment_time ASC
";
$stmt_appts = $conn->prepare($sql);
$stmt_appts->bind_param("i", $doctor_id);
$stmt_appts->execute();
$appointments = $stmt_appts->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_appts->close();


// --- DATA FOR CHARTS AND STATS ---

// A. Appointment Status Breakdown (For Donut Chart)
$status_query = "
    SELECT 
        AS_Status.status_name, 
        COUNT(A.appointment_id) AS count
    FROM Appointment_Status AS_Status
    LEFT JOIN Appointments A ON AS_Status.status_id = A.status_id AND A.doctor_id = ?
    WHERE AS_Status.status_id IN (2, 3, 4) /* Confirmed, Completed, Cancelled */
    GROUP BY AS_Status.status_name
";
$stmt_status = $conn->prepare($status_query);
$stmt_status->bind_param("i", $doctor_id);
$stmt_status->execute();
$status_counts = $stmt_status->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_status->close();

$chart_labels = [];
$chart_counts = [];
foreach ($status_counts as $status) {
    if ($status['count'] > 0) {
        $chart_labels[] = $status['status_name'];
        $chart_counts[] = $status['count'];
    }
}
$total_appts_created = array_sum($chart_counts);


// Today's Appointments (Confirmed only)
$todays_appts_query = $conn->prepare("
    SELECT 
        U.name AS patient_name, 
        A.appointment_time,
        A.status_id
    FROM Appointments A
    JOIN Users U ON A.patient_id = U.user_id
    WHERE A.doctor_id = ? 
    AND A.appointment_date = CURDATE()
    AND A.status_id = 2 
    ORDER BY A.appointment_time ASC
");
$todays_appts_query->bind_param("i", $doctor_id);
$todays_appts_query->execute();
$todays_appts = $todays_appts_query->get_result()->fetch_all(MYSQLI_ASSOC);
$todays_appts_query->close();

$patients_remaining_today = count($todays_appts);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* --- DASHBOARD LAYOUT STYLING (Matching REMEDICA template) --- */

        .page-wrapper {
            /* CRITICAL FIX: Use CSS Grid to define two permanent columns: sidebar and main content */
            display: grid;
            grid-template-columns: 250px 1fr; 
            min-height: 100vh;
        }

        /* Sidebar Styling (Static Docked Menu) */
        .sidebar {
            background-color: #f7f9fa; /* Light background */
            color: #4a4a4a;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky; 
            top: 0;
            height: 100vh;
            z-index: 2000;
            overflow-y: auto; /* Allows menu content to scroll if too long */
        }
        .sidebar-header {
            font-size: 1.2em;
            font-weight: 700;
            color: #0077b6;
            padding: 10px 20px 25px;
            text-align: center;
        }
        .sidebar a {
            color: #4a4a4a;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: background-color 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .current-page-header {
            background-color: #e6f7ff;
            border-left: 3px solid #0077b6;
            color: #0077b6;
        }
        .sidebar i {
            margin-right: 15px;
            font-size: 1.1em;
        }
        
        /* Main Content Area Grid */
        .main-content {
            padding: 30px 40px;
            background-color: white; /* Main working area */
            display: grid;
            grid-template-columns: 2fr 1fr; /* Two columns: Dashboard (2 parts) and Profile/Alerts (1 part) */
            gap: 30px;
            width: 100%;
        }

        /* DASHBOARD SECTION */
        .dashboard-main {
            grid-column: 1 / 2;
        }
        
        /* PROFILE / ALERTS SECTION */
        .profile-alerts {
            grid-column: 2 / 3;
            padding: 20px;
            background-color: #f7f9fa;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            height: fit-content; /* Ensure the column shrinks to content size */
        }

        .welcome-box {
            background-color: #e6f7ff;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            text-align: left;
        }
        .welcome-box h2 {
            font-size: 2.2em;
            color: #0077b6;
            border-bottom: none;
            margin: 0;
        }
        .appointment-list-card {
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            max-height: 400px; /* Constrain height for scrolling if needed */
            overflow-y: auto;
        }
        .appt-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }
        .appt-item:last-child {
            border-bottom: none;
        }
        .appt-details {
            text-align: left;
        }
        .appt-time {
            font-weight: 700;
            color: #1e90ff;
        }

        /* Profile Panel Styling */
        .profile-card {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e6eb;
            margin-bottom: 20px;
        }
        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .profile-card p {
            margin: 0;
            font-size: 1.1em;
            font-weight: 600;
            color: #0077b6;
        }
        .profile-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 15px;
        }
        .profile-stats div {
            padding: 5px;
        }
        .profile-stats .stat-value {
            font-size: 1.4em;
            font-weight: 700;
            color: #ff6b81;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- -------------------- SIDEBAR (STATIC DOCKED MENU) -------------------- -->
        <div class="sidebar">
            <div class="sidebar-header">NK Hospitals</div>
            <a href="doctor_dashboard.php" class="current-page-header"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#"><i class="fas fa-prescription-bottle-alt"></i> Prescriptions (Sim)</a>
            <a href="doctor_patients.php"><i class="fas fa-user-injured"></i> Patients</a>
            <a href="doctor_calendar.php"><i class="fas fa-calendar-alt"></i> Schedule & Events</a>
            <a href="doctor_settings.php"><i class="fas fa-cog"></i> Settings</a>
            
            <a href="role_select.php" style="margin-top: 30px; color: #ff6b81;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <!-- -------------------- MAIN CONTENT AREA -------------------- -->
        <div class="main-content">
            
            <!-- HEADER BAR (Visually separating Dashboard title and Date) -->
            <div style="grid-column: 1 / 3; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h1 style="font-size: 1.8em; color: #4a4a4a; margin: 0;">Dashboard</h1>
                <p style="font-weight: 500; margin: 0;"><?php echo date("l, F j, Y"); ?></p>
            </div>
            
            <!-- -------------------- LEFT COLUMN (DASHBOARD) -------------------- -->
            <div class="dashboard-main">
                
                <!-- WELCOME AND STATS -->
                <div class="welcome-box">
                    <h2>Welcome, <?php echo $doctor_first_name; ?>!</h2>
                    <p style="font-size: 1.1em; color: #4a4a4a; margin-top: 10px;">
                        You have **<?php echo $patients_remaining_today; ?>** confirmed patients remaining today.
                    </p>
                    <p style="color: #ff6b81; font-weight: 500;">
                        Remember to check documentation before call.
                    </p>
                </div>
                
                <!-- TODAY'S APPOINTMENTS LIST -->
                <h3 style="color: #4a4a4a; font-weight: 600; margin-top: 25px;">Today's Appointments</h3>
                <div class="appointment-list-card">
                    <?php if (empty($todays_appts)): ?>
                         <p style="text-align: center; color: #666;">No confirmed appointments scheduled for today.</p>
                    <?php endif; ?>
                    
                    <?php foreach ($todays_appts as $appt): ?>
                        <div class="appt-item">
                            <div class="appt-details">
                                <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($appt['patient_name']); ?></p>
                                <p style="margin: 0; font-size: 0.9em; color: #666;">Consultation</p>
                            </div>
                            <div class="appt-time">
                                <?php echo date("g:i A", strtotime($appt['appointment_time'])); ?>
                            </div>
                            <!-- Mark Complete button added here to complete the flow -->
                            <a href="?action=complete&appt_id=<?php echo $appt['appointment_id']; ?>" style="color: #0077b6; font-size: 0.9em;">
                                Done
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ANALYTICS (Placeholder for Charts) -->
                <h3 style="color: #4a4a4a; font-weight: 600; margin-top: 40px;">Practice Analytics</h3>
                <div class="appointment-list-card" style="padding: 30px;">
                    <canvas id="statusChart" style="max-height: 250px;"></canvas>
                </div>
            </div>

            <!-- -------------------- RIGHT COLUMN (PROFILE & ALERTS) -------------------- -->
            <div class="profile-alerts">
                
                <!-- PROFILE CARD -->
                <div class="profile-card">
                    <!-- Placeholder Profile Image -->
                    <img src="https://placehold.co/100x100/90c7f7/ffffff?text=Dr" alt="Doctor Profile Image">
                    <p>Dr. <?php echo htmlspecialchars($doctor_full_name); ?></p>
                    <p style="font-size: 0.9em; color: #ff6b81; margin-top: 5px;">Specialist in Internal Medicine (Example)</p>
                    
                    <div class="profile-stats">
                        <div>
                            <p style="font-size: 0.8em; color: #666;">Total Patients</p>
                            <p class="stat-value">129</p>
                        </div>
                        <div>
                            <p style="font-size: 0.8em; color: #666;">Total Appts</p>
                            <p class="stat-value"><?php echo $total_appts_created; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- ALERTS PANEL -->
                <h4 style="color: #0077b6; margin-top: 0;">Alerts</h4>
                <div style="font-size: 0.9em;">
                    <div class="appt-item" style="padding: 8px 0; color: #ff8c00;">
                        <i class="fas fa-bell"></i> Appointment Cancelled (1 hr ago)
                    </div>
                    <div class="appt-item" style="padding: 8px 0; color: #4caf50;">
                        <i class="fas fa-star"></i> New Review Received
                    </div>
                    <div class="appt-item" style="padding: 8px 0;">
                        <i class="fas fa-calendar-alt"></i> New Appointment Booked
                    </div>
                </div>
            </div>
        </div>
    
    <script>
        // Data injected from PHP for the Donut Chart
        const statusLabels = <?php echo json_encode($chart_labels); ?>;
        const statusCounts = <?php echo json_encode($chart_counts); ?>;
        
        // Define colors matching the Aqua theme and status types
        const chartColors = {
            'Confirmed': 'rgba(0, 191, 255, 0.8)', // Bright Blue/Aqua
            'Completed': 'rgba(30, 144, 255, 0.8)', // Deep Blue
            'Cancelled': 'rgba(255, 107, 129, 0.8)', // Rose/Red
        };
        
        const backgroundColors = statusLabels.map(label => chartColors[label] || 'rgba(150, 150, 150, 0.6)');

        // --- RENDER STATUS DONUT CHART ---
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Appointments by Status',
                    data: statusCounts,
                    backgroundColor: backgroundColors,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
