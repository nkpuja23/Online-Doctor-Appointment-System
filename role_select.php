<?php
session_start();
require_once 'db_connect.php'; // *** ADDED: Assuming this file contains your $conn mysqli connection object ***

// --- DATA CORRELATION FROM SQL TABLES ---
$specialties = [
    ['id' => 1, 'name' => 'Cardiology', 'icon' => 'fas fa-heart', 'desc' => 'Diagnosis and treatment of heart, blood vessel, and circulatory system disorders.'],
    ['id' => 3, 'name' => 'Dermatology', 'icon' => 'fas fa-shield-alt', 'desc' => 'Focuses on the health of skin, hair, and nails.'],
    ['id' => 6, 'name' => 'Gastroenterology', 'icon' => 'fas fa-lungs', 'desc' => 'Specializes in the digestive system and its disorders, from esophagus to rectum.'],
    ['id' => 8, 'name' => 'Nephrology', 'icon' => 'fas fa-kidneys', 'desc' => 'Diagnosis and management of kidney disease, hypertension, and electrolyte disorders.'],
    ['id' => 5, 'name' => 'Neurology', 'icon' => 'fas fa-brain', 'desc' => 'Focuses on disorders of the nervous system, including the brain, spinal cord, and nerves.'],
    ['id' => 7, 'name' => 'Oncology', 'icon' => 'fas fa-radiation', 'desc' => 'Dedicated to the diagnosis and treatment of cancer.'],
    ['id' => 4, 'name' => 'Orthopedics', 'icon' => 'fas fa-bone', 'desc' => 'Treats musculoskeletal system issues, including bones, joints, ligaments, and muscles.'],
    ['id' => 2, 'name' => 'Pediatrics', 'icon' => 'fas fa-child', 'desc' => 'Comprehensive medical care for infants, children, and adolescents.'],
    ['id' => 9, 'name' => 'Urology', 'icon' => 'fas fa-bladder', 'desc' => 'Focuses on urinary tract health and male reproductive organs.'],
];

// Quick Booking Form Logic (hardcoded captcha 17998)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quick_book_submit'])) {
    $name = $_POST['name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $location = $_POST['location'] ?? ''; // Added location capture
    $captcha_input = $_POST['captcha_input'] ?? '';

    if (!empty($name) && !empty($phone_number) && !empty($location) && $captcha_input == '17998') { 
        
        // --- DATABASE INSERTION LOGIC ---
        $insert_sql = "INSERT INTO QuickBookingRequests (patient_name, phone_number, clinic_location) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($insert_sql)) {
            $stmt->bind_param("sss", $name, $phone_number, $location);
            
            if ($stmt->execute()) {
                // Success: Redirect with a custom success message
                header('Location: booking_success.php?msg=' . urlencode("We've successfully logged your request for the **{$location}** clinic. We'll call you at **{$phone_number}** shortly!"));
                exit();
            } else {
                // Database execution failure
                $booking_error = "Booking failed due to a server error. Please try again later.";
            }
            $stmt->close();
        } else {
            // Prepared statement failure
            $booking_error = "A system error occurred while preparing the request.";
        }
        
    } else {
        $booking_error = "Booking failed: Check your name, phone number, clinic selection, and captcha (must be 17998).";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>NK Hospitals - Select Your Access Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ==================================== */
        /* BASE STYLES & LAYOUT                 */
        /* ==================================== */

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f2f5; 
            color: #333;
            line-height: 1.6;
            scroll-behavior: smooth;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* --- Global Gradient & Color Scheme (Rose to Light Aqua) --- */
        .gradient-bg {
            background: linear-gradient(135deg, #FFD1DC 0%, #A3E4D7 100%);
        }
        .rose-color { color: #f15474ff; }
        .aqua-color { color: #A3E4D7; }
        .dark-rose-color { color: #DC143C; }

        /* --- Header Navigation Bar --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background-color: #fff; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            width: 100%;
            box-sizing: border-box;
            z-index: 1000;
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            font-size: 1.5em;
            font-weight: 700;
            color: #444;
        }
        .navbar .logo i {
            margin-right: 10px;
            color: #dc4662ff;
        }

        .navbar .nav-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex; 
            align-items: center;
        }

        .navbar .nav-links li {
            margin-left: 30px;
        }

        .navbar .nav-links a {
            color: #555;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .navbar .nav-links a:hover {
            background: linear-gradient(to right, #FFD1DC, #A3E4D7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }

        /* Contact Phone Number Styling */
        .navbar .contact-phone {
            color: #555;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-left: 30px;
            cursor: pointer;
        }
        .navbar .contact-phone i {
            color: #e25063ff;
            margin-right: 8px;
            font-size: 1.1em;
        }
        .navbar .contact-phone:hover {
             color: #dc465cff;
        }

        .navbar .action-buttons {
            display: flex;
            align-items: center;
            margin-left: 30px;
        }

        .navbar .action-buttons .btn {
            background: linear-gradient(90deg, #e5466bff, #A3E4D7); 
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }
        .navbar .action-buttons .btn:hover {
            opacity: 0.8;
        }

        /* --- Hero Section --- */
        .hero-section {
            background: linear-gradient(135deg, #FFD1DC 0%, #A3E4D7 100%);
            position: relative;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #333;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23add8e6' fill-opacity='0.15' fill-rule='evenodd'%3E%3Cpath d='M0 0h30v30H0V0zm30 30h30v30H30V30z'/%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 0;
        }

        .hero-content .tagline {
            font-family: 'Playfair Display', serif;
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 10px;
            color: #e93c61ff;
            letter-spacing: 1px;
        }
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.8em;
            margin: 10px 0 20px;
            font-weight: 700;
            line-height: 1.1;
            color: #333;
        }
        .hero-content .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.15em;
            max-width: 600px;
            margin: 0 auto 30px;
            color: #555;
            font-weight: 500;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4); 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }


        /* --- Action Cards Section (Role Selection & Emergency) --- */
        .action-cards-section {
            width: 90%;
            max-width: 1200px;
            margin: -80px auto 50px; 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            z-index: 500;
            position: relative;
        }

        .action-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .action-card .card-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #f24f62ff;
        }
        .action-card .card-title {
            font-size: 1.4em;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .action-card .card-description {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        .action-card .arrow-link {
            display: flex;
            align-items: center;
            color: #A3E4D7;
            font-weight: 600;
            font-size: 1.1em;
            text-decoration: none;
        }
        .action-card .arrow-link i {
            margin-left: 10px;
            transition: transform 0.3s ease;
        }
        .action-card:hover .arrow-link i {
            transform: translateX(5px);
        }

        .action-card.emergency-card {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        .action-card.emergency-card .card-icon,
        .action-card.emergency-card .card-title,
        .action-card.emergency-card .card-description,
        .action-card.emergency-card .arrow-link {
            color: white;
        }


        /* --- Why Choose Us Section --- */
        #why-choose-us {
            padding: 80px 5%;
            background-color: #fcebeb; 
            text-align: center;
        }

        #why-choose-us h2 {
            color: #333;
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 50px;
            font-family: 'Playfair Display', serif;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-item {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .feature-item:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            transform: translateY(-5px);
        }

        .feature-item .icon {
            font-size: 2.8em;
            color: #A3E4D7;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #FFD1DC, #A3E4D7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }

        .feature-item h4 {
            font-size: 1.4em;
            font-weight: 700;
            color: #ec4f5cff;
            margin-bottom: 10px;
        }

        .feature-item p {
            color: #555;
            font-size: 0.95em;
            max-width: 280px;
        }


        /* --- Specializations Section --- */
        #specializations {
            padding: 80px 5%;
            background-color: #f7feff;
            text-align: center;
        }
        #specializations h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        #specializations p {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1em;
            color: #666;
            margin-bottom: 50px;
        }

        .specializations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .spec-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
            cursor: default;
        }
        .spec-card .icon-circle {
            background-color: #e64b60ff;
        }
        .spec-card:hover .icon-circle {
            background-color: #A3E4D7;
        }

        .spec-description {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(246, 87, 98, 0.95), rgba(163,228,215,0.95));
            color: white;
            padding: 25px;
            box-sizing: border-box;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
            pointer-events: none;
        }

        .spec-card:hover .spec-description {
            opacity: 1;
            visibility: visible;
        }

        /* --- Quick Booking Form Section --- */
        .quick-booking-form-section {
            background: linear-gradient(135deg, #FFD1DC 0%, #A3E4D7 100%);
            padding: 60px 5%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .booking-card-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            max-width: 900px;
            width: 100%;
            text-align: left;
            z-index: 10;
        }

        .booking-card-container h2 {
            color: #333;
            border-bottom: 2px solid #A3E4D7;
            padding-bottom: 10px;
            font-family: 'Playfair Display', serif;
            font-size: 2em;
            font-weight: 700;
        }

        .booking-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
            justify-content: space-between;
        }

        .form-submit-button {
            background-color: #f7e09f;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            align-self: flex-end;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
        }

        /* --- Error/Success styling for form messages */
        .booking-error, .booking-success {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .booking-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .booking-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }


        /* --- Other Role Cards --- */
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            width: 90%;
            max-width: 900px;
            margin: 50px auto;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .role-card {
            background-color: #fcf4f8;
            border-radius: 12px;
            padding: 30px 20px;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .role-card .icon {
            font-size: 3.5em;
            color: #e3426aff;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #FFD1DC, #A3E4D7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .role-card .role-title {
            font-size: 1.4em;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        .role-card .role-description {
            font-size: 0.9em;
            color: #666;
            line-height: 1.5;
            padding: 0 10px;
            font-family: 'Montserrat', sans-serif;
        }


        /* --- New Patient Register Button --- */
        .register-cta-section {
            text-align: center;
            padding: 30px 5%;
            background-color: #f0f2f5;
        }

        .register-cta-section .btn-register {
            background: linear-gradient(90deg, #DC143C, #4cbbb8ff);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: opacity 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        /* --- Footer (FINAL SHORTENED & HORIZONTAL VERSION) --- */
        .footer {
            background-color: #333;
            color: #ccc;
            padding: 15px 5% 10px;
            font-size: 0.85em;
            font-family: 'Montserrat', sans-serif;
        }

        .footer .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer .col {
            flex: 1;
            min-width: 200px;
            margin-bottom: 5px;
        }
        @media (min-width: 769px) {
            .footer .col {
                min-width: 150px;
                flex-basis: 25%;
            }
        }

        .footer h5 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 0.95em; 
            border-bottom: 1px solid #A3E4D7;
            padding-bottom: 2px;
            display: inline-block;
            font-family: 'Playfair Display', serif;
        }

        .footer ul li {
            margin-bottom: 3px;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 8px;
            margin-top: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar .nav-links {
                display: none;
            }
            .footer .col {
                min-width: 100%;
                text-align: center;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-clinic-medical"></i> NK Hospitals
        </div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#why-choose-us">About</a></li>
            <li><a href="#specializations">Services</a></li>
            <li class="contact-phone">
                <a href="tel:06364421819">
                    <i class="fas fa-phone"></i>
                    0636 442 1819
                </a>
            </li>
        </ul>
        <div class="action-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="index.php?role=patient" class="btn">Login</a> 
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-overlay">
            <div class="hero-content">
                <div class="tagline">Healthcare Clinics Partnership Organizations</div>
                <h1>Our expertise at your service</h1>
                <p class="subtitle">Providing compassionate and advanced healthcare solutions for a healthier community.</p>
            </div>
        </div>
    </div>
 
    <div class="action-cards-section">
        <a href="doctor_info.php" class="action-card">
            <i class="fas fa-user-md card-icon"></i>
            <h3 class="card-title">Our Service Providers</h3>
            <p class="card-description">Find the right specialist for your needs from our expert team.</p>
            <span class="arrow-link">View Doctors <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="book_appointment.php" class="action-card">
            <i class="fas fa-calendar-check card-icon"></i>
            <h3 class="card-title">Book an Appointment</h3>
            <p class="card-description">Schedule your consultation online with ease and convenience.</p>
            <span class="arrow-link">Book Now <i class="fas fa-arrow-right"></i></span>
        </a>

        <!-- MODIFICATION 1: Change href to tel: protocol for calling -->
        <a href="tel:06364421819" class="action-card emergency-card">
            <i class="fas fa-phone-alt card-icon"></i>
            <h3 class="card-title">Have an Emergency?</h3>
            <p class="card-description">Click here to call our emergency line instantly: 0636 442 1819.</p>
            <span class="arrow-link">EMERGENCY CALL <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>

    <div id="why-choose-us">
        <h2>Why Choose NK Hospitals?</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="icon"><i class="fas fa-award"></i></div>
                <h4>Certified Experts</h4>
                <p>Access highly-qualified, board-certified specialists across all major medical fields.</p>
            </div>
            <div class="feature-item">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <h4>24/7 Availability</h4>
                <p>Book appointments and access support anytime, ensuring you get care when you need it most.</p>
            </div>
            <div class="feature-item">
                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                <h4>Data Security</h4>
                <p>We prioritize your privacy with industry-leading encryption and HIPAA-compliant data management.</p>
            </div>
        </div>
    </div>

    <div id="specializations">
        <h2>Find a Specialist</h2>
        <p>Explore our key departments and services.</p>

        <div class="specializations-grid">
            <?php foreach ($specialties as $spec): ?>
                <div class="spec-card">
                    <div class="icon-circle"><i class="<?php echo $spec['icon']; ?>"></i></div>
                    <h4><?php echo $spec['name']; ?></h4>
                    <div class="spec-description">
                        <p><?php echo $spec['desc']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="quick-booking-form-section">
        <div class="booking-card-container">
            <h2>Book an Appointment</h2>
            <!-- Check for error/success messages here -->
            <?php if (isset($booking_error)): ?>
                <div class="booking-error"><?php echo $booking_error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="booking-form">
                
                <div class="form-group field-name">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Name" required>
                </div>
                
                <div class="form-group field-location">
                    <label for="location">Clinic</label>
                    <select id="location" name="location" required>
                        <option value="City Central Clinic" selected>City Central Clinic</option>
                        <option value="Northside Wellness Center">Northside Wellness Center</option>
                        <option value="Family Care Center">Family Care Center</option>
                    </select>
                </div>
                
                <div class="form-group field-phone">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="Phone Number" required>
                </div>
                
                <div class="form-group captcha-display-wrapper">
                    <label>Captcha</label>
                    <div class="captcha-text-display">17998</div> 
                </div>
                <div class="form-group captcha-input-wrapper">
                    <label for="captcha_input">Enter Captcha</label>
                    <input type="text" id="captcha_input" name="captcha_input" placeholder="Enter Captcha" required>
                </div>
                <div class="form-group captcha-actions-wrapper">
                    <div class="captcha-actions">
                         <a href="#" onclick="alert('Functionality to change number goes here!'); return false;">Change Number!</a>
                         <a href="#" onclick="alert('Functionality to reload captcha goes here!'); return false;">Reload Captcha!</a>
                    </div>
                </div>
                <div class="form-group submit-button-wrapper">
                    <button type="submit" name="quick_book_submit" class="form-submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <div style="text-align: center; padding: 50px 5% 20px;">
        <h2 style="color: #333; font-family: 'Playfair Display', serif;">Select Your Access Portal</h2>
        <p style="color: #666; font-family: 'Montserrat', sans-serif;">Choose your profile type to proceed with login or explore our services.</p>
    </div>

    <div class="portal-grid">
        <a href="index.php?role=patient" class="role-card">
            <i class="fas fa-user icon"></i>
            <h3 class="role-title">Patient Portal</h3>
            <p class="role-description">Access your personal health records, appointments, and medical information.</p>
        </a>

        <a href="index.php?role=doctor" class="role-card">
            <i class="fas fa-stethoscope icon"></i>
            <h3 class="role-title">Doctor/Staff Login</h3>
            <p class="role-description">Manage patient appointments, view schedules, and update medical notes.</p>
        </a>

        <a href="index.php?role=admin" class="role-card">
            <i class="fas fa-shield-alt icon"></i>
            <h3 class="role-title">Administrator Access</h3>
            <p class="role-description">Oversee hospital operations, manage user accounts, and generate reports.</p>
        </a>
    </div>

    <div class="register-cta-section">
        <a href="register.php" class="btn-register">New Patient? Register Now!</a>
    </div>

    <footer class="footer">
        <div class="container">
            
            <div class="col">
                <h5>NK Hospitals</h5>
                <p>Committed to world-class healthcare with compassion.</p>
            </div>

            <div class="col">
                <h5>Quick Links</h5>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#why-choose-us">About Us</a></li>
                    <li><a href="#specializations">Our Services</a></li>
                </ul>
            </div>

            <div class="col">
                <h5>Patient Access</h5>
                <ul>
                    <li><a href="index.php?role=patient">Patient Login</a></li>
                    <li><a href="book_appointment.php">Book Appointment</a></li>
                    <!-- MODIFICATION 2: Change href to tel: protocol for calling -->
                    <li><a href="tel:06364421819" style="color: #dc3545;">Emergency</a></li>
                </ul>
            </div>

            <div class="col contact-info">
                <h5>Contact Us</h5>
                <p><i class="fas fa-map-marker-alt"></i> Metro City Office</p>
                <p><i class="fas fa-phone"></i> 0636 442 1819</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> NK Hospitals. All Rights Reserved.</p>
        </div>
    </footer>
<script>
    // This simple JS helps ensure smooth scrolling works reliably across browsers
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            // Check if the link is NOT inside the footer or quick links area
            if (!this.closest('.footer') && !this.closest('.action-cards-section')) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
</body>
</html>
