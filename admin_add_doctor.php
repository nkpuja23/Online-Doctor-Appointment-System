<?php
require_once 'db_connect.php'; 
session_start(); // Ensure session is started

// Security Check: Must be logged in as Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}

$error = '';
$success = '';

// 1. Fetch Specializations for the dropdown
$specializations_sql = "SELECT specialization_id, specialization_name FROM Specializations ORDER BY specialization_name ASC";
$specializations = $conn->query($specializations_sql)->fetch_all(MYSQLI_ASSOC);

// 2. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $licence_number = trim($_POST['licence_number']);
    $specialization_id = (int)$_POST['specialization_id'];

    // Basic Validation
    if (empty($name) || empty($email) || empty($password) || empty($licence_number) || $specialization_id <= 0) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM Users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Start Transaction for atomic insertion (inserting into two tables)
            $conn->begin_transaction();
            try {
                // A. Insert into Users table (role_id 2 for Doctor)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user_sql = "INSERT INTO Users (name, email, password_hash, role_id) VALUES (?, ?, ?, 2)";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("sss", $name, $email, $hashed_password);
                $user_stmt->execute();
                
                // Get the last inserted ID (which will be the doctor_id)
                $new_doctor_id = $conn->insert_id;

                // B. Insert into Doctors table
                $doctor_sql = "INSERT INTO Doctors (doctor_id, specialization_id, licence_number) VALUES (?, ?, ?)";
                $doctor_stmt = $conn->prepare($doctor_sql);
                $doctor_stmt->bind_param("iis", $new_doctor_id, $specialization_id, $licence_number);
                $doctor_stmt->execute();

                // Commit the transaction
                $conn->commit();
                $success = "Doctor '{$name}' added successfully!";

                // Clear fields after success
                $name = $email = $password = $licence_number = '';

            } catch (mysqli_sql_exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Database error: Could not add doctor. " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Add New Doctor</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles specific to the form */
        .form-card {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            max-width: 500px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Important for padding not to affect width */
        }
        .submit-btn {
            background-color: #0077b6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #005a96;
        }
        .error-message {
            color: #d9534f;
            background-color: #fbe5e4;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success-message {
            color: #28a745;
            background-color: #e6ffed;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1 style="color: white;">Add New Doctor</h1>
        </div>

        <p><a href="admin_doctors.php" class="button" style="background-color: #5cb85c; display: inline-block; margin-top: 10px;">&larr; Back to Manage Doctors</a></p>

        <div class="form-card">
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="admin_add_doctor.php">
                
                <div class="form-group">
                    <label for="name">Doctor Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Initial Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="licence_number">License Number:</label>
                    <input type="text" id="licence_number" name="licence_number" value="<?php echo htmlspecialchars($licence_number ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="specialization_id">Specialization:</label>
                    <select id="specialization_id" name="specialization_id" required>
                        <option value="">-- Select Specialization --</option>
                        <?php foreach ($specializations as $spec): ?>
                            <option value="<?php echo $spec['specialization_id']; ?>"
                                <?php echo (isset($specialization_id) && $specialization_id == $spec['specialization_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['specialization_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Add Doctor</button>
            </form>
        </div>
    </div>
</body>
</html>