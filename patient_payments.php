<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Patient
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: index.php?error=access_denied");
    exit();
}

$patient_id = $_SESSION['user_id'];

// Fetch all payments made by the patient for cancellation fees
$sql = "
    SELECT 
        P.payment_id, P.amount, P.payment_date, P.payment_method, P.payment_status,
        A.appointment_id,
        U.name AS doctor_name,
        A.appointment_date AS appt_date_original
    FROM Payments P
    JOIN Appointments A ON P.appointment_id = A.appointment_id
    JOIN Users U ON A.doctor_id = U.user_id
    WHERE A.patient_id = ?
    ORDER BY P.payment_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payments History</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Table Status Colors */
        .payment-status-success { color: #28a745; font-weight: bold; }
        .payment-status-failed { color: #dc3545; font-weight: bold; }

        /* --- CRITICAL STATIC SIDEBAR FIXES --- */
        .page-wrapper {
            /* Use CSS Grid to define two distinct columns: sidebar and main content */
            display: grid;
            grid-template-columns: 250px 1fr; /* 250px for sidebar, rest for content */
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            /* CRITICAL: Removed position: sticky and used height: 100vh for a simple dock */
            height: 100vh; 
            z-index: 2000;
        }
        
        .main-content {
            /* The second grid column automatically fills the remaining space */
            margin-left: 0; 
            flex-grow: 1;
            padding-top: 0; 
        }
        
        /* Header Fix: Ensure the header aligns neatly with content */
        .header-bar {
             padding: 20px 40px; 
             margin-left: 0;
             position: sticky; 
             top: 0; 
             z-index: 100;
             background-color: #f5faff; 
             box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-bar h1 {
            margin-left: 20px; 
            font-size: 1.6em;
            color: #0077b6; 
        }

        /* Adjust content wrapper padding */
        .centered-content {
            padding-top: 20px; 
            padding-left: 40px;
            padding-right: 40px;
            padding-bottom: 20px;
            max-width: 100%; /* Allows content to use all available space */
        }

        /* Table specific styles for large column count */
        table {
            font-size: 0.9em;
            table-layout: fixed; 
            width: 100%;
        }
        th, td {
            word-wrap: break-word; 
            padding: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- SIDEBAR (Always Visible and Static/Sticky) -->
        <div id="sidebar" class="sidebar">
            <a href="patient_dashboard.php"><i class="fas fa-calendar-alt"></i> My Appointments</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book New Slot</a>
            <a href="patient_account.php"><i class="fas fa-user-edit"></i> Account & Profile</a>
            <a href="patient_payments.php" class="current-page-header"><i class="fas fa-wallet"></i> Payments History</a>
            <a href="#"><i class="fas fa-clipboard-list"></i> Medical Records (Simulated)</a>
            <a href="role_select.php" style="margin-top: 30px; color: #f44336;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <!-- MAIN CONTENT AREA (Occupies remaining space) -->
        <div id="main-content" class="main-content">
            
            <!-- HEADER BAR (Sticky Top) -->
            <div class="header-bar">
                 <!-- Logo is now inside the main content header for styling consistency -->
                <span class="logo-circle" style="color: #0077b6;">NK</span> 
                <h1>Payment History</h1>
            </div>
            
            <div class="container full-screen" style="padding-top: 0; padding-left: 0; padding-right: 0;">

                <div class="centered-content" style="max-width: 100%; padding-left: 40px; padding-right: 40px;">
                    <h2>Cancellation Fee Transactions</h2>
                    
                    <?php if (empty($payments)): ?>
                        <p>No cancellation fee payments found in your history.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 10%;">ID</th>
                                    <th style="width: 15%;">Amount</th>
                                    <th style="width: 20%;">Date Paid</th>
                                    <th style="width: 15%;">Method</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Doctor</th>
                                    <th style="width: 10%;">Appt Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payments as $payment): 
                                $status_class = strtolower($payment['payment_status']);
                                if ($status_class == 'success') $status_class = 'payment-status-success';
                                if ($status_class == 'failed') $status_class = 'payment-status-failed';
                            ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td>₹<?php echo number_format($payment['amount'], 0); ?></td>
                                <td><?php echo date('Y-m-d h:i A', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($payment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['appt_date_original']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // The toggleSidebar function is no longer actively used for this static layout
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            sidebar.classList.toggle('open');
            
            if (sidebar.classList.contains('open')) {
                mainContent.style.marginLeft = '250px';
                mainContent.style.paddingLeft = '20px'; 
            } else {
                mainContent.style.marginLeft = '0';
                mainContent.style.paddingLeft = '0';
            }
        }
    </script>
</body>
</html>