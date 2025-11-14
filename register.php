<?php
require_once 'db_connect.php'; 

$message = '';
$registration_failed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Retrieve and sanitize input (Expanded to include all new fields)
    $salutation = trim($_POST['salutation'] ?? '');
    $name_first = trim($_POST['name_first'] ?? '');
    $name_middle = trim($_POST['name_middle'] ?? '');
    $name_last = trim($_POST['name_last'] ?? '');
    $name = trim($name_first . ' ' . $name_last); // Combine names for Users.name
    
    $email = trim($_POST['email'] ?? '');
    $password_plain = $_POST['password'] ?? ''; 
    $phone = trim($_POST['phone_number'] ?? '');
    $dob = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    // NEW FIELDS
    $nationality = $_POST['nationality'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $address_street = $_POST['address'] ?? ''; // Using 'address' field for street info
    $address_state = $_POST['state'] ?? '';
    $address_city = $_POST['city'] ?? '';
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
    $emergency_contact_number = $_POST['emergency_contact_number'] ?? '';

    $role_id = 3; // Patient Role ID

    // ----------------------------------------------------------------------
    // START CORE VALIDATION (Unchanged from previous version)
    // ----------------------------------------------------------------------
    if (empty($name_first) || empty($name_last) || empty($email) || empty($phone) || empty($dob) || empty($password_plain)) {
        $message = "Error: Please fill all required fields.";
        $registration_failed = true;
    }
    
    // Password Validation (Unchanged)
    if (!$registration_failed && strlen($password_plain) < 8) {
        $message = "Error: Password must be at least 8 characters long.";
        $registration_failed = true;
    } 
    // ... (rest of password validation)
    elseif (!$registration_failed && !preg_match('/[^a-zA-Z0-9\s]/', $password_plain)) {
        $message = "Error: Password must contain at least one special character.";
        $registration_failed = true;
    }
    // Check Consent
    elseif (!$registration_failed && (!isset($_POST['consent']) || $_POST['consent'] !== 'accept')) {
        $message = "Error: You must accept the Consent and Declaration.";
        $registration_failed = true;
    }
    
    // ----------------------------------------------------------------------
    // END CORE VALIDATION
    // ----------------------------------------------------------------------
    
    if (!$registration_failed) {
        // If validation PASSED, proceed with database checks and insert

        // 1. Check if email already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $message = "Error: This email is already registered.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            $conn->begin_transaction();
            try {
                // Hashing the password
                $password_hash = hash('sha256', $password_plain);
                
                // 2. Insert into Users table (SAVING CORE DATA)
                $stmt_user = $conn->prepare("INSERT INTO Users (email, password_hash, role_id, name, phone_number) VALUES (?, ?, ?, ?, ?)");
                $stmt_user->bind_param("ssiss", $email, $password_hash, $role_id, $name, $phone);
                $stmt_user->execute();
                
                $new_user_id = $conn->insert_id; 
                
                // 3. Insert into Patients table (SAVING CORE PROFILE DATA)
                $stmt_patient = $conn->prepare("INSERT INTO Patients (patient_id, date_of_birth, gender) VALUES (?, ?, ?)");
                $stmt_patient->bind_param("iss", $new_user_id, $dob, $gender);
                $stmt_patient->execute();

                // 4. NEW: Insert into Patient_Demographics table
                $stmt_demo = $conn->prepare("
                    INSERT INTO Patient_Demographics 
                    (patient_id, salutation, name_middle, nationality, marital_status, address_street, address_state, address_city, emergency_contact_name, emergency_contact_number) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt_demo->bind_param("isssssssss", 
                    $new_user_id, 
                    $salutation, 
                    $name_middle, 
                    $nationality, 
                    $marital_status, 
                    $address_street, 
                    $address_state, 
                    $address_city, 
                    $emergency_contact_name, 
                    $emergency_contact_number
                );
                $stmt_demo->execute();
                
                $conn->commit();
                header("Location: index.php?success=register");
                exit();

            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $message = "Registration failed due to a database error.";
                // For debugging: $message = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Registration</title>
    <link rel="stylesheet" href="style.css">
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
    </script>
    <style>
        /* Styles to support the 4-column grid layout */
        .registration-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px 20px;
            margin-bottom: 30px;
            align-items: center;
        }
        .registration-grid input,
        .registration-grid select {
            margin-bottom: 0 !important; /* Remove standard input margin for tight grid */
        }
        .full-row {
            grid-column: 1 / -1; /* Spans all columns */
        }
        .consent-section {
            background-color: #f8f8f8;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 8px;
            font-size: 0.9em;
            text-align: left;
            margin-top: 20px;
        }
        .consent-text {
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 15px;
            padding-right: 10px;
        }
        .form-title {
            font-size: 1.6em;
            font-weight: 700;
            color: #0077b6;
            margin-bottom: 25px;
        }
        
        /* CRITICAL FIX: Password input container */
        .password-input-group {
            width: 100%; 
            margin-top: 10px; 
        }
        
        .password-input-group .password-requirements {
            margin-top: 5px; 
            width: 100%; 
        }
        
        .password-input-group .password-requirements ul {
            padding-left: 20px;
            margin: 0;
            font-size: 0.85em;
            list-style-type: disc;
            text-align: left;
            color: #0077b6;
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
                Patient Registration
            </h1>
            
            <div id="live-clock-display" class="live-clock" style="grid-column: 3 / 4; justify-self: end;"></div>
        </div>
        
        <div class="centered-content" style="max-width: 1000px; text-align: left;">
            <div style="text-align: center;">
                <p class="form-title">Self Registration Details</p>
                <p><a href="index.php">Back to Login</a></p>
                <?php if ($message): ?>
                    <p class="status-cancelled" style="text-align: center;"><?php echo $message; ?></p>
                <?php endif; ?>
            </div>

            <form method="post" action="register.php">
                
                <!-- --- PERSONAL DETAILS GRID --- -->
                <div class="registration-grid">
                    
                    <select name="salutation" required>
                        <option value="">Select Salutation</option>
                        <option value="Mr.">Mr.</option>
                        <option value="Ms.">Ms.</option>
                        <option value="Mrs.">Mrs.</option>
                        <option value="Dr.">Dr.</option>
                    </select>

                    <input type="text" name="name_first" placeholder="First Name*" required>
                    <input type="text" name="name_middle" placeholder="Middle Name">
                    <input type="text" name="name_last" placeholder="Last Name*" required>

                    <select name="gender" required>
                        <option value="">Select Gender*</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>

                    <input type="text" name="nationality" placeholder="Nationality">
                    
                    <select name="marital_status" required>
                        <option value="">Marital Status*</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
                
                <!-- --- CONTACT & LOGIN DETAILS GRID --- -->
                <div class="registration-grid">
                    <input type="text" name="phone_number" placeholder="Mobile Number*" required>
                    <input type="email" name="email" placeholder="Email ID*" required>
                    <input type="date" name="date_of_birth" placeholder="Date of Birth*" required>
                    
                    <!-- PASSWORD INPUT GROUP (Fixed Overlap) -->
                    <div class="password-input-group">
                        <input type="password" id="reg_password" name="password" placeholder="Password*" required>
                        
                        <div class="password-requirements">
                            <ul style="color: #0077b6;">
                                <li>Min 8 chars, 1 alphanumeric, 1 special</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- --- ADDRESS & EMERGENCY CONTACT --- -->
                <div class="registration-grid">
                    <input type="text" name="address" placeholder="Address*" class="full-row" required>

                    <input type="text" name="emergency_contact_name" placeholder="Emergency Contact Name*" required>
                    <input type="text" name="emergency_contact_number" placeholder="Emergency Contact Number*" required>
                    
                    <!-- Simulating the State/City/Pincode fields as simple inputs -->
                    <input type="text" name="state" placeholder="State/Province">
                    <input type="text" name="city" placeholder="City">
                </div>

                <!-- --- CONSENT AND DECLARATION --- -->
                <div class="consent-section">
                    <p style="font-weight: 700; color: #333;">Consent and Declaration</p>
                    <div class="consent-text">
                        I, the undersigned, declare that the above information provided by me are true to the best of my knowledge and hereby provide my consent to the Medical Staff at NK Hospitals to provide Medical Care, Treatment, Conduct Investigations and Diagnostic Procedures necessary for the above mentioned individual. I also understand that NK Hospital will not be responsible for any loss, damage or theft of any Personal Property/Belongings of Mine/Patient/Visitors within the Hospital Premises. I agree to follow all the rules and regulations of the Hospital and clear all the expenses incurred for my treatment on time.
                    </div>
                    
                    <div style="margin-top: 15px; display: flex; align-items: center;">
                        <input type="checkbox" id="consent" name="consent" value="accept" required>
                        <label for="consent" style="margin-left: 10px; font-weight: 500;">I accept the terms and conditions and privacy policy.</label>
                    </div>
                </div>

                <button type="submit" style="margin-top: 30px;">Register Me</button>
            </form>
            <p style="text-align: center; margin-top: 20px;"><a href="role_select.php">Go Back to Main</a></p>
        </div>
    </div>
</body>
</html>