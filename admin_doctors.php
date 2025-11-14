<?php
require_once 'db_connect.php'; 
session_start(); // Ensure session is started

// Security Check: Must be logged in as Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Fetch all doctors, their specialization, and email
$sql = "
    SELECT 
        U.user_id, U.name, U.email, D.licence_number, S.specialization_name
    FROM Users U
    JOIN Doctors D ON U.user_id = D.doctor_id
    JOIN Specializations S ON D.specialization_id = S.specialization_id
    WHERE U.role_id = 2
    ORDER BY U.name ASC
";
$doctors = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Doctors</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Professional button styles */
        .action-button {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Back button (Blue) */
        .back-btn {
            background-color: #0077b6;
            color: white;
        }
        .back-btn:hover {
            background-color: #005a96;
        }

        /* Add Doctor button (Green) */
        .add-doctor-btn {
            background-color: #e33d69ff; 
            color: white;
        }
        .add-doctor-btn:hover {
            background-color: #1e7e34;
        }

        /* Group the navigation buttons together */
        .button-group {
            display: flex;
            gap: 10px; /* Space between buttons */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1 style="color: white;">Doctor Profiles Management</h1>
        </div>
        <h2>All Registered Doctors (<?php echo count($doctors); ?>)</h2>

        <!-- NEW PROFESSIONAL BUTTON GROUP -->
        <div class="button-group">
            <a href="admin_dashboard.php" class="action-button back-btn">&larr; Back to Dashboard</a>
            <a href="admin_add_doctor.php" class="action-button add-doctor-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                </svg>
                Add New Doctor
            </a>
        </div>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>License No.</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($doctors as $doc): ?>
            <tr>
                <td><?php echo $doc['user_id']; ?></td>
                <td><?php echo $doc['name']; ?></td>
                <td><?php echo $doc['email']; ?></td>
                <td><?php echo $doc['specialization_name']; ?></td>
                <td><?php echo $doc['licence_number']; ?></td>
                <td>
                    <!-- These actions remain placeholders for future functionality -->
                    <a href="#" style="color: #ff9800; text-decoration: none;">Edit</a> | 
                    <a href="#" style="color: red; text-decoration: none;">Reset Pass</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
