<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}
$admin_id = $_SESSION['user_id'];
$message = '';

// --- 1. HANDLE FORM SUBMISSION (UPDATE CONFIG) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $new_fee = $_POST['cancellation_fee'];
    $new_max_days = $_POST['max_booking_days'];
    $new_min_lead = $_POST['min_booking_lead'];

    $conn->begin_transaction();
    try {
        // Update Cancellation Fee
        $stmt_fee = $conn->prepare("UPDATE System_Config SET config_value = ? WHERE config_name = 'CANCELLATION_FEE_INR'");
        $stmt_fee->bind_param("s", $new_fee);
        $stmt_fee->execute();

        // Update Max Booking Days
        $stmt_max = $conn->prepare("UPDATE System_Config SET config_value = ? WHERE config_name = 'MAX_BOOKING_DAYS'");
        $stmt_max->bind_param("s", $new_max_days);
        $stmt_max->execute();
        
        // Update Min Booking Lead Days (This controls the start date)
        $stmt_min = $conn->prepare("UPDATE System_Config SET config_value = ? WHERE config_name = 'MIN_BOOKING_LEAD_DAYS'");
        $stmt_min->bind_param("s", $new_min_lead);
        $stmt_min->execute();

        $conn->commit();
        $message = "System settings updated successfully!";
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = "Error: Failed to update settings.";
    }
}

// --- 2. FETCH CURRENT CONFIGURATION ---
$config_data = [];
$config_result = $conn->query("SELECT config_name, config_value FROM System_Config");
if ($config_result) {
    while ($row = $config_result->fetch_assoc()) {
        $config_data[$row['config_name']] = $row['config_value'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings</title>
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
            background-color: #f8fcff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 600px;
        }
        .settings-card input, .settings-card select {
            width: 100%;
        }
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .settings-grid .input-group {
            text-align: left;
        }
        .settings-grid label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #0077b6;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="sidebar">
            <div class="sidebar-header">Super Admin</div>
            <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="admin_lookups.php"><i class="fas fa-sitemap"></i> Specialties & Clinics</a>
            <a href="admin_doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a>
            <a href="admin_reports.php"><i class="fas fa-chart-line"></i> View Reports</a>
            <a href="admin_settings.php" class="current-page-header"><i class="fas fa-cog"></i> Settings</a>
            
            <a href="role_select.php" style="margin-top: 30px; color: #ff6b81;"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
        
        <div class="main-content">
            <h2 style="color: #4a4a4a;">System Configuration</h2>
            <p>Adjust core business rules that affect booking and finances.</p>

            <div class="settings-card">
                <h3 style="color: #0077b6; border-bottom: 1px solid #e0e6eb; padding-bottom: 10px; margin-bottom: 20px;">
                    Booking and Financial Rules
                </h3>
                
                <?php if (!empty($message)): ?>
                    <p class="<?php echo strpos($message, 'Error') !== false ? 'status-cancelled' : 'status-confirmed'; ?>" style="font-weight: bold; margin-bottom: 20px;"><?php echo $message; ?></p>
                <?php endif; ?>

                <form method="post" action="admin_settings.php">
                    <input type="hidden" name="update_settings" value="1">
                    
                    <div class="settings-grid">
                        
                        <div class="input-group">
                            <label for="cancellation_fee">Cancellation Fee (INR)</label>
                            <input type="number" step="0.01" id="cancellation_fee" name="cancellation_fee" 
                                value="<?php echo htmlspecialchars($config_data['CANCELLATION_FEE_INR'] ?? '500'); ?>" required>
                            <p style="font-size: 0.8em; color: #666; margin-top: 5px;">Current Fee: ₹<?php echo htmlspecialchars($config_data['CANCELLATION_FEE_INR'] ?? '500'); ?></p>
                        </div>
                        
                        <div class="input-group">
                            <label for="min_advance_hours">Cancellation Cutoff (Hours)</label>
                            <input type="number" id="min_advance_hours" value="<?php echo htmlspecialchars($config_data['MIN_ADVANCE_HOURS'] ?? '24'); ?>" readonly disabled>
                            <p style="font-size: 0.8em; color: #f39c12; margin-top: 5px;">*Hardcoded to 24 hours for integrity.</p>
                        </div>
                        
                        <div class="input-group">
                            <label for="max_booking_days">Max Booking Window (Days)</label>
                            <input type="number" id="max_booking_days" name="max_booking_days" 
                                value="<?php echo htmlspecialchars($config_data['MAX_BOOKING_DAYS'] ?? '30'); ?>" required>
                            <p style="font-size: 0.8em; color: #666; margin-top: 5px;">Affects future date selection limit.</p>
                        </div>
                        
                        <div class="input-group">
                            <label for="min_booking_lead">Min Booking Lead (Days)</label>
                            <select id="min_booking_lead" name="min_booking_lead" required>
                                <option value="0" <?php echo ($config_data['MIN_BOOKING_LEAD_DAYS'] == '0') ? 'selected' : ''; ?>>0 (Same Day)</option>
                                <option value="1" <?php echo ($config_data['MIN_BOOKING_LEAD_DAYS'] == '1') ? 'selected' : ''; ?>>1 (Next Day Only)</option>
                            </select>
                            <p style="font-size: 0.8em; color: #666; margin-top: 5px;">Affects minimum selectable date.</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="button" style="background-color: #27ae60; margin-top: 20px;">Save Configuration</button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>