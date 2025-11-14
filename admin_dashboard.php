<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = '';

// 1. Fetch Admin Name
$stmt_name = $conn->prepare("SELECT name FROM Users WHERE user_id = ?");
$stmt_name->bind_param("i", $admin_id);
$stmt_name->execute();
$admin_name = $stmt_name->get_result()->fetch_assoc()['name'];
$stmt_name->close();


// --- 2. FETCH ALL KEY METRICS ---
$metrics = [
    'doctor_count' => $conn->query("SELECT COUNT(*) FROM Doctors")->fetch_row()[0],
    'patient_count' => $conn->query("SELECT COUNT(*) FROM Patients")->fetch_row()[0],
    'appt_count' => $conn->query("SELECT COUNT(*) FROM Appointments")->fetch_row()[0],
    'spec_count' => $conn->query("SELECT COUNT(*) FROM Specializations")->fetch_row()[0],
    'revenue_total' => $conn->query("SELECT SUM(amount) FROM Payments WHERE payment_status = 'Success'")->fetch_row()[0] ?? 0,
    'payments_total' => $conn->query("SELECT COUNT(*) FROM Payments")->fetch_row()[0],
];

// 3. FETCH DATA FOR CHARTS (Simulated Monthly Registered Users)
// In a real system, this would be complex SQL grouping by month. We simulate the data.
$monthly_users_data = [60, 55, 78, 85, 65, 75, 58, 70, 40, 15, 8, 5];
$monthly_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$current_month_index = date('n'); // 1-12
$registered_users_data_js = json_encode(array_slice($monthly_users_data, 0, $current_month_index));
$monthly_labels_js = json_encode(array_slice($monthly_labels, 0, $current_month_index));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* --- ADMIN DASHBOARD LAYOUT (Matching Advanced Template) --- */

        .page-wrapper {
            display: grid;
            grid-template-columns: 250px 1fr; /* Fixed sidebar width */
            min-height: 100vh;
        }

        /* Sidebar Styling (Static Docked Menu) */
        .sidebar {
            background-color: #f7f9fa; 
            color: #4a4a4a;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky; 
            top: 0;
            height: 100vh;
            z-index: 2000;
            overflow-y: auto; 
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
            background-color: white; 
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* --- STAT CARDS --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #e6f7ff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card .icon-box {
            font-size: 2.2em;
            padding: 10px;
            border-radius: 8px;
            color: white;
            opacity: 0.8;
        }
        .stat-card-blue .icon-box { background-color: #1e90ff; }
        .stat-card-green .icon-box { background-color: #27ae60; }
        .stat-card-rose .icon-box { background-color: #ff6b81; }
        .stat-card-yellow .icon-box { background-color: #f39c12; }

        .stat-card .data {
            text-align: right;
        }
        .stat-card .data .value {
            font-size: 1.8em;
            font-weight: 700;
            color: #0077b6;
        }
        .stat-card .data .label {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        /* --- CHARTS SECTION --- */
        .analytics-section {
            background-color: #f7f9fa;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 2fr 1fr; /* Bar chart (left), Earning Analytics (right) */
            gap: 30px;
        }
        .chart-container {
            padding-right: 20px;
        }
        .earning-analytics {
            border-left: 1px solid #eee;
            padding-left: 30px;
            text-align: center;
        }
        .earning-analytics h4 {
            margin-top: 0;
            color: #4a4a4a;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- -------------------- SIDEBAR (STATIC DOCKED MENU) -------------------- -->
        <div class="sidebar">
            <div class="sidebar-header">Super Admin</div>
            <a href="admin_dashboard.php" class="current-page-header"><i class="fas fa-home"></i> Dashboard</a>
            <a href="admin_lookups.php"><i class="fas fa-sitemap"></i> Specialties & Clinics</a>
            <a href="admin_doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a>
            <a href="admin_reports.php"><i class="fas fa-chart-line"></i> View Reports</a>
            <a href="admin_settings.php" class="current-page-header"><i class="fas fa-cog"></i> Settings</a>
            
            <a href="role_select.php" style="margin-top: 30px; color: #ff6b81;"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
        
        <!-- -------------------- MAIN CONTENT AREA -------------------- -->
        <div class="main-content">
            
            <!-- HEADER BAR -->
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h1 style="font-size: 1.8em; color: #4a4a4a; margin: 0;">Admin Dashboard</h1>
                <p style="font-weight: 500; margin: 0;">Welcome, <?php echo $admin_name; ?>!</p>
            </div>
            
            <!-- -------------------- 1. STAT CARDS (Key Metrics) -------------------- -->
            <div class="stat-grid">
                
                <div class="stat-card stat-card-blue">
                    <div class="icon-box"><i class="fas fa-hospital-alt"></i></div>
                    <div class="data">
                        <div class="value"><?php echo $metrics['spec_count']; ?></div>
                        <div class="label">Departments</div>
                    </div>
                </div>

                <div class="stat-card stat-card-green">
                    <div class="icon-box"><i class="fas fa-user-md"></i></div>
                    <div class="data">
                        <div class="value"><?php echo $metrics['doctor_count']; ?></div>
                        <div class="label">Active Doctors</div>
                    </div>
                </div>

                <div class="stat-card stat-card-rose">
                    <div class="icon-box"><i class="fas fa-user-injured"></i></div>
                    <div class="data">
                        <div class="value"><?php echo $metrics['patient_count']; ?></div>
                        <div class="label">Total Patients</div>
                    </div>
                </div>

                <div class="stat-card stat-card-yellow">
                    <div class="icon-box"><i class="fas fa-calendar-check"></i></div>
                    <div class="data">
                        <div class="value"><?php echo $metrics['appt_count']; ?></div>
                        <div class="label">Total Appointments</div>
                    </div>
                </div>
            </div>

            <!-- -------------------- 2. ANALYTICS SECTION -------------------- -->
            <div class="analytics-section">
                
                <div class="chart-container">
                    <h3 style="color: #4a4a4a; margin-top: 0;">Monthly Registered Users (Simulated)</h3>
                    <canvas id="monthlyUsersChart"></canvas>
                </div>

                <div class="earning-analytics">
                    <h4 style="color: #0077b6;">Financial Analytics</h4>
                    <p style="font-size: 0.9em; color: #666;">Total Fee Revenue</p>
                    <div class="stat-value" style="color: #27ae60;">₹<?php echo number_format($metrics['revenue_total'], 0); ?></div>
                    <p style="font-size: 0.8em; color: #ff6b81; margin-top: 10px;">
                        +12% From Previous Month (Sim)
                    </p>
                    
                    <h4 style="margin-top: 30px; color: #0077b6;">Total Payments Recorded</h4>
                    <div class="stat-value" style="color: #1e90ff;"><?php echo $metrics['payments_total']; ?></div>
                    <p style="font-size: 0.8em; color: #666; margin-top: 5px;">
                        (Cancellation Fees)
                    </p>
                </div>
            </div>
        </div>
    
    <script>
        // Data injected from PHP for the Bar Chart
        const userLabels = <?php echo $monthly_labels_js; ?>;
        const userData = <?php echo $registered_users_data_js; ?>;
        
        // --- RENDER MONTHLY USERS BAR CHART ---
        new Chart(document.getElementById('monthlyUsersChart'), {
            type: 'bar',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'New Registrations',
                    data: userData,
                    backgroundColor: 'rgba(0, 191, 255, 0.7)',
                    borderColor: '#0077b6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100, // Based on the simulated data range
                        title: {
                            display: true,
                            text: 'Users'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
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
