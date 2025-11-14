<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}

// --- Fetch Report Data ---
// Total Appointments Ever
$total_appts = $conn->query("SELECT COUNT(*) FROM Appointments")->fetch_row()[0];

// Total Completed Appointments
$completed_appts = $conn->query("SELECT COUNT(*) FROM Appointments WHERE status_id = 3")->fetch_row()[0];

// Total Pending Appointments (Used for quick overview)
$total_pending = $conn->query("SELECT COUNT(*) FROM Appointments WHERE status_id = 1")->fetch_row()[0];

// Total Revenue from Payments (Assuming a price column in Payments table)
$total_revenue = $conn->query("SELECT SUM(amount) FROM Payments WHERE payment_status = 'Success'")->fetch_row()[0] ?? 0;

// NEW: Detailed Status Counts for Appointment Breakdown (Data for Donut Chart)
$status_counts_query = "
    SELECT 
        AS_Status.status_name, 
        COUNT(A.appointment_id) AS count
    FROM Appointment_Status AS_Status
    LEFT JOIN Appointments A ON AS_Status.status_id = A.status_id
    GROUP BY AS_Status.status_name
    ORDER BY AS_Status.status_id
";
$status_counts_data = $conn->query($status_counts_query)->fetch_all(MYSQLI_ASSOC);

// Prepare PHP data for JavaScript injection
$chart_labels = [];
$chart_counts = [];
foreach ($status_counts_data as $status) {
    // Only include statuses with counts greater than zero for the chart
    if ($status['count'] > 0) {
        $chart_labels[] = $status['status_name'];
        $chart_counts[] = $status['count'];
    }
}

// Data for Bar Chart
$doctor_count = $conn->query("SELECT COUNT(*) FROM Doctors")->fetch_row()[0];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - System Reports</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="container full-screen">
        <div class="header-bar" style="
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 40px 20px;
            ">
            
            <div style="grid-column: 1 / 2; justify-self: start;">
                <span class="logo-circle" style="font-weight: 600;">NK</span>
            </div>
            
            <h1 style="
                color: white; 
                font-weight: 600; 
                margin: 0;
                grid-column: 2 / 3;
                ">
                System Reports and Analytics
            </h1>
            
            <div id="live-clock-display" class="live-clock" style="grid-column: 3 / 4; justify-self: end;"></div>
        </div>
        
        <div class="centered-content">
            <h2>System Performance Overview</h2>

            <p><a href="admin_dashboard.php" class="button" style="background-color: #0077b6;">&larr; Back to Dashboard</a></p>

            <!-- General Financial Data Table -->
            <table style="width: 100%; margin-bottom: 30px;">
                <thead>
                    <tr>
                        <th colspan="2">Key System Metrics</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><th>Total Doctors Registered</th><td><?php echo $doctor_count; ?></td></tr>
                    <tr><th>Total Appointments Created</th><td><?php echo $total_appts; ?></td></tr>
                    <tr><th>Total Completed Consultations</th><td><?php echo $completed_appts; ?></td></tr>
                    <tr><th>Total Revenue (Fees & Payments)</th><td>₹<?php echo number_format($total_revenue, 0); ?></td></tr>
                </tbody>
            </table>

            <!-- Graph Section -->
            <div style="display: flex; gap: 30px; margin-top: 40px; justify-content: space-around; flex-wrap: wrap;">
                
                <!-- 1. APPOINTMENT STATUS DONUT CHART -->
                <div style="width: 100%; max-width: 350px;">
                    <h3>Appointment Status Breakdown</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                
                <!-- 2. REVENUE / APPOINTMENT BAR CHART -->
                <div style="width: 100%; max-width: 400px;">
                    <h3>Financial & Activity Summary</h3>
                    <canvas id="summaryChart"></canvas>
                </div>
            </div>

            <p style="margin-top: 40px; font-size: 0.9em; color: #777;">Data is generated live from the database tables.</p>
        </div>
    </div>

    <script>
        // Data injected from PHP for the Donut Chart
        const statusLabels = <?php echo json_encode($chart_labels); ?>;
        const statusCounts = <?php echo json_encode($chart_counts); ?>;
        
        // Define colors matching the Aqua theme and status types
        const chartColors = {
            'Confirmed': 'rgba(0, 150, 136, 0.8)', // Teal
            'Completed': 'rgba(39, 174, 96, 0.8)', // Green
            'Cancelled': 'rgba(231, 76, 60, 0.8)', // Red
            'Pending': 'rgba(243, 156, 18, 0.8)'  // Orange/Amber
        };
        
        const backgroundColors = statusLabels.map(label => chartColors[label] || 'rgba(150, 150, 150, 0.6)');

        // --- 1. RENDER STATUS DONUT CHART ---
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
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Current Appointment Distribution'
                    }
                }
            }
        });


        // --- 2. RENDER SUMMARY BAR CHART (Revenue vs. Appointments) ---
        new Chart(document.getElementById('summaryChart'), {
            type: 'bar',
            data: {
                labels: ['Total Appointments', 'Total Revenue (INR)'],
                datasets: [
                    {
                        label: 'Metrics',
                        data: [<?php echo $total_appts; ?>, <?php echo $total_revenue; ?>],
                        backgroundColor: [
                            'rgba(30, 144, 255, 0.8)', // Blue for Appointments
                            'rgba(46, 204, 113, 0.8)' // Emerald Green for Revenue
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Total Activity vs. Revenue'
                    }
                }
            }
        });
    </script>
</body>
</html>