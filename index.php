<?php
require_once 'db_connect.php'; 

// Generate a random 6-digit CAPTCHA number and store it in the session
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(100000, 999999);
}

if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    redirect_to_dashboard($_SESSION['role_id']);
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_captcha = $_POST['captcha'];
    $stored_captcha = $_SESSION['captcha'];

    // --- CAPTCHA VALIDATION ---
    if (empty($user_captcha) || $user_captcha != $stored_captcha) {
        $error_message = "Invalid CAPTCHA code. Please try again.";
        // Regenerate CAPTCHA on failure
        $_SESSION['captcha'] = rand(100000, 999999); 
        goto end_login;
    }
    
    // CAPTCHA passed: Regenerate for the next attempt (good practice)
    $_SESSION['captcha'] = rand(100000, 999999); 

    // --- CREDENTIAL VALIDATION ---
    $stmt = $conn->prepare("SELECT user_id, password_hash, role_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // SHA2 VERIFICATION FIX
        $input_hash = hash('sha256', $password); 
        
        if ($input_hash === $user['password_hash']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];
            redirect_to_dashboard($user['role_id']);
        } else {
            $error_message = "Invalid email or password. Please check your spelling.";
        }
    } else {
        $error_message = "Invalid email or password. User not found.";
    }

    end_login:
    // Close statement if it was successfully prepared
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Doctor Appointment System</title>
    <link rel="stylesheet" href="style.css">
    <!-- CRITICAL: Ensure Font Awesome is linked for the icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function togglePasswordVisibility(id) {
            const passwordField = document.getElementById(id);
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
        function reloadCaptcha() {
            // Simple way to regenerate CAPTCHA by forcing a page reload
            window.location.href = window.location.pathname + window.location.search; 
        }
    </script>
    <style>
        .captcha-box {
            background-color: #f0f7fa;
            border: 1px solid #b3e5fc;
            padding: 8px 15px;
            font-size: 1.2em;
            font-weight: 700;
            color: #0077b6;
            border-radius: 4px;
            letter-spacing: 2px;
            user-select: none; /* Prevent selection */
            display: inline-block;
            line-height: 24px; /* Ensure vertical centering */
        }
        /* New button style for better visual appearance */
        .regenerate-btn {
            padding: 10px 15px; 
            font-size: 14px; 
            background-color: #0077b6; /* Match primary theme color */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: 500;
            white-space: nowrap; /* Prevent wrapping */
            transition: background-color 0.2s;
        }
        .regenerate-btn:hover {
            background-color: #005f93;
        }
    </style>
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
                Online Doctor Appointment System
            </h1>
            
            <div id="live-clock-display" class="live-clock" style="grid-column: 3 / 4; justify-self: end;"></div>
        </div>
        
        <div class="centered-content"> 
            <h2>User Login</h2>

            <?php 
            if (isset($_GET['role'])) {
                echo "<p style='font-weight: bold; color: #0077b6;'>Logging in as: " . ucfirst(htmlspecialchars($_GET['role'])) . "</p>";
            }
            ?> 
            <?php if ($error_message): ?>
                <p class="status-cancelled"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <p class="status-confirmed">Registration successful! Please log in.</p>
            <?php endif; ?>

            <form method="post" action="index.php?role=<?php echo htmlspecialchars($_GET['role'] ?? ''); ?>">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="login_password">Password:</label>
                <input type="password" id="login_password" name="password" required>
                
                <!-- Show Password Checkbox -->
                <div style="margin-bottom: 20px; font-size: 0.9em;">
                    <input type="checkbox" id="show_login_password" onclick="togglePasswordVisibility('login_password')">
                    <label for="show_login_password" style="display: inline;">Show Password</label>
                </div>
                
                <label for="captcha">Security Check:</label>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <!-- Display CAPTCHA -->
                    <span class="captcha-box" id="captcha_display"><?php echo $_SESSION['captcha']; ?></span>
                    
                    <!-- Input Field -->
                    <input type="text" id="captcha" name="captcha" placeholder="Enter code" required style="width: 100%; margin: 0;">
                </div>
                
                <button type="submit">Log In</button>
            </form>

            <p style="margin-top: 30px;"><a href="register.php">Register as a Patient</a> | <a href="role_select.php">Go Back to Main</a></p>
        </div>
    </div>
</body>
</html>