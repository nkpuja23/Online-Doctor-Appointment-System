<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: index.php?error=access_denied");
    exit();
}

$message = '';

// --- 1. HANDLE CRUD ACTIONS (ADD/DELETE) ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $type = $_POST['type'];
    $name = trim($_POST['name']);
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    if ($type === 'specialization' && !empty($name)) {
        // Add New Specialization
        $stmt = $conn->prepare("INSERT INTO Specializations (specialization_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $message = "Specialization added successfully!";
        } else {
            $message = "Error: Could not add specialization. It may already exist.";
        }
        $stmt->close();
    } elseif ($type === 'clinic' && !empty($name) && !empty($address)) {
        // Add New Clinic
        $stmt = $conn->prepare("INSERT INTO Clinics (name, address) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $address);
        if ($stmt->execute()) {
            $message = "Clinic added successfully!";
        } else {
            $message = "Error: Could not add clinic. Check data.";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $type = $_GET['type'];
    $delete_id = $_GET['id'];
    $table_name = ($type === 'specialization') ? 'Specializations' : 'Clinics';
    $pk_column = ($type === 'specialization') ? 'specialization_id' : 'clinic_id';
    $fk_error_msg = ($type === 'specialization') 
        ? 'Error: Cannot delete specialization. It is linked to existing Doctors.' 
        : 'Error: Cannot delete clinic. It is linked to existing Doctor Schedules.';

    $stmt = $conn->prepare("DELETE FROM {$table_name} WHERE {$pk_column} = ?");
    $stmt->bind_param("i", $delete_id);
    
    // Execute with error handling for Foreign Key violations
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = ucfirst($type) . " deleted successfully!";
        } else {
            $message = "Error: " . ucfirst($type) . " not found.";
        }
    } else {
        $message = $fk_error_msg;
    }
    $stmt->close();
    
    // Redirect back to clean URL
    header("Location: admin_lookups.php?msg=" . urlencode($message));
    exit();
}

// Check for redirect message (from DELETE action)
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// --- 2. FETCH CURRENT DATA ---
$specializations = $conn->query("SELECT * FROM Specializations ORDER BY specialization_name ASC")->fetch_all(MYSQLI_ASSOC);
$clinics = $conn->query("SELECT * FROM Clinics ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Lookups</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1 style="color: white;">System Lookups Management</h1>
        </div>
        <h2>Administrative Tools</h2>

        <p><a href="admin_dashboard.php" class="button" style="background-color: #0077b6;">&larr; Back to Dashboard</a></p>

        <?php if ($message): ?>
            <p class="<?php echo strpos($message, 'Error') !== false ? 'status-cancelled' : 'status-confirmed'; ?>" style="font-weight: bold; padding: 10px; border-radius: 5px;">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <!-- Specialization Management -->
        <h3 style="margin-top: 40px;">Manage Specializations (<?php echo count($specializations); ?> Total)</h3>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($specializations as $spec): ?>
            <tr>
                <td><?php echo $spec['specialization_id']; ?></td>
                <td><?php echo $spec['specialization_name']; ?></td>
                <td>
                    <a href="admin_lookups.php?action=delete&type=specialization&id=<?php echo $spec['specialization_id']; ?>" 
                       style="color: red;"
                       onclick="return confirm('WARNING: Deleting this may disrupt linked Doctor data. Are you sure?');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h4 style="margin-top: 25px;">Add New Specialization</h4>
        <form method="post" action="admin_lookups.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="type" value="specialization">
            <label for="new_spec_name">Specialization Name:</label>
            <input type="text" id="new_spec_name" name="name" required>
            <button type="submit">Add Specialization</button>
        </form>

        <!-- Clinic Management -->
        <h3 style="margin-top: 50px;">Manage Clinics (<?php echo count($clinics); ?> Total)</h3>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
            <?php foreach ($clinics as $clinic): ?>
            <tr>
                <td><?php echo $clinic['clinic_id']; ?></td>
                <td><?php echo $clinic['name']; ?></td>
                <td><?php echo $clinic['address']; ?></td>
                <td>
                    <a href="admin_lookups.php?action=delete&type=clinic&id=<?php echo $clinic['clinic_id']; ?>" 
                       style="color: red;"
                       onclick="return confirm('WARNING: Deleting this may disrupt linked Schedule data. Are you sure?');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h4 style="margin-top: 25px;">Add New Clinic</h4>
        <form method="post" action="admin_lookups.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="type" value="clinic">
            <label for="new_clinic_name">Clinic Name:</label>
            <input type="text" id="new_clinic_name" name="name" required>
            <label for="new_clinic_address">Address:</label>
            <input type="text" id="new_clinic_address" name="address" required>
            <button type="submit">Add Clinic</button>
        </form>

    </div>
</body>
</html>