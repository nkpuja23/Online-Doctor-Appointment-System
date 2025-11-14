<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Patient
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: index.php?error=access_denied");
    exit();
}

$patient_id = $_SESSION['user_id'];
$message = '';

// Fetch current user and patient data
$stmt = $conn->prepare("
    SELECT 
        U.email, U.name, U.phone_number, 
        P.date_of_birth, P.gender
    FROM Users U
    JOIN Patients P ON U.user_id = P.patient_id
    WHERE U.user_id = ?
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- SIMULATED UPDATE LOGIC ---
    $new_name = $_POST['name'];
    $new_phone = $_POST['phone_number'];
    $new_dob = $_POST['date_of_birth'];
    $new_gender = $_POST['gender'];
    
    $conn->begin_transaction();
    try {
        // Update Users table
        $stmt_u = $conn->prepare("UPDATE Users SET name = ?, phone_number = ? WHERE user_id = ?");
        $stmt_u->bind_param("ssi", $new_name, $new_phone, $patient_id);
        $stmt_u->execute();

        // Update Patients table
        $stmt_p = $conn->prepare("UPDATE Patients SET date_of_birth = ?, gender = ? WHERE patient_id = ?");
        $stmt_p->bind_param("ssi", $new_dob, $new_gender, $patient_id);
        $stmt_p->execute();

        $conn->commit();
        $message = "Profile updated successfully!";
        // Refresh data after update
        $user_data['name'] = $new_name;
        $user_data['phone_number'] = $new_phone;
        $user_data['date_of_birth'] = $new_dob;
        $user_data['gender'] = $new_gender;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div id="sidebar" class="sidebar">
            <div class="sidebar-header">Patient Portal</div>
            <a href="patient_dashboard.php"><i class="fas fa-calendar-alt"></i> My Appointments</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book New Slot</a>
            <a href="patient_account.php" class="current-page-header"><i class="fas fa-user-edit"></i> Account & Profile</a>
            <a href="patient_payments.php"><i class="fas fa-wallet"></i> Payments History</a>
            <a href="#"><i class="fas fa-clipboard-list"></i> Medical Records (Simulated)</a>
            <a href="role_select.php" style="margin-top: 30px; color: #f44336;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div id="main-content" class="main-content">
            <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i> Menu
            </button>
            <div class="container full-screen" style="padding-top: 80px;">
                <div class="header-bar" style="padding: 20px 40px; position: relative;">
                    <span class="logo-circle">NK</span>
                    <h1>Account Settings</h1>
                </div>

                <div class="centered-content">
                    <h2>Edit Profile Details</h2>
                    
                    <?php if ($message): ?>
                        <p class="<?php echo strpos($message, 'Error') !== false ? 'status-cancelled' : 'status-confirmed'; ?>" style="font-weight: bold; padding: 10px; border-radius: 5px;"><?php echo $message; ?></p>
                    <?php endif; ?>

                    <form method="post" action="patient_account.php">
                        <label for="email">Email (Login ID):</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly disabled style="background-color: #f8f8f8;">
                        
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>

                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>" required>
                        
                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user_data['date_of_birth']); ?>" required>

                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="Male" <?php echo ($user_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($user_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($user_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>

                        <button type="submit" style="margin-top: 20px;">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
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