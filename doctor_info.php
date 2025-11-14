<?php
// doctor_info.php (Final Aesthetic Version - Dummy Data)
session_start();

// --- Dummy Data (Expanded to 15 Doctors) ---
$dummy_doctors = [
    ['user_id' => 101, 'name' => 'Dr. Aaron Smith', 'qual' => 'MD, Cardiologist, Fellow of ACC', 'spec_desc' => 'Leading expert in non-invasive cardiac procedures.'],
    ['user_id' => 102, 'name' => 'Dr. Brenda Jones', 'qual' => 'PhD, Clinical Psychologist, Pediatric Mental Health Specialist', 'spec_desc' => 'Focuses on child and adolescent behavioral therapy.'],
    ['user_id' => 103, 'name' => 'Dr. Cindy Chen', 'qual' => 'MBBS, FRCS, Expertise in Laparoscopic Surgery', 'spec_desc' => 'Highly experienced in advanced minimally invasive surgery.'],
    ['user_id' => 104, 'name' => 'Dr. Davina Patel', 'qual' => 'MD, Pediatrician, Board-certified in Neonatal Medicine', 'spec_desc' => 'Specializes in the health and care of newborns and infants.'],
    ['user_id' => 105, 'name' => 'Dr. Evan Lee', 'qual' => 'DDS, Maxillofacial Surgeon', 'spec_desc' => 'Advanced surgical care for the face, mouth, and jaws.'],
    
    // 10 NEW Doctors added below
    ['user_id' => 106, 'name' => 'Dr. Fiona Miller', 'qual' => 'DO, Family Medicine Physician', 'spec_desc' => 'Provides comprehensive, holistic primary care for all ages.'],
    ['user_id' => 107, 'name' => 'Dr. George Ryan', 'qual' => 'MD, Neurologist', 'spec_desc' => 'Pioneering treatments for neurological and chronic pain conditions.'],
    ['user_id' => 108, 'name' => 'Dr. Heather Kelly', 'qual' => 'MBBS, MD, Expert in Infectious Diseases', 'spec_desc' => 'Specializes in the diagnosis and management of complex infections.'],
    ['user_id' => 109, 'name' => 'Dr. Ivan Fernandez', 'qual' => 'MPH, Epidemiologist', 'spec_desc' => 'Public Health consultant focused on disease prevention strategies.'],
    ['user_id' => 110, 'name' => 'Dr. Jess Willis', 'qual' => 'PhD, Medical Researcher, Gene Therapy Specialist', 'spec_desc' => 'Active researcher in genetic therapies for chronic illnesses.'],
    ['user_id' => 111, 'name' => 'Dr. James Ford', 'qual' => 'MD, Nephrologist', 'spec_desc' => 'Specializes in kidney function, dialysis, and renal care.'],
    ['user_id' => 112, 'name' => 'Dr. Andrew Patel', 'qual' => 'BDS, Cosmetic Dentist', 'spec_desc' => 'Focuses on dental implants and advanced cosmetic procedures.'],
    ['user_id' => 113, 'name' => 'Dr. William Grey', 'qual' => 'MD, Oncologist', 'spec_desc' => 'Leading specialist in complex cancer treatment protocols.'],
    ['user_id' => 114, 'name' => 'Dr. Emily Chen', 'qual' => 'D.O., Orthopedic Surgeon', 'spec_desc' => 'Expert in knee and shoulder repair and sports injury rehabilitation.'],
    ['user_id' => 115, 'name' => 'Dr. Sarah Lopez', 'qual' => 'MD, Gastroenterologist', 'spec_desc' => 'Specializes in disorders of the digestive tract and endoscopic procedures.'],
];

// Dummy Location Data
$dummy_locations = [
    'Dr. Aaron Smith' => 'City Central Clinic', 'Dr. Brenda Jones' => 'Northside Wellness Center',
    'Dr. Cindy Chen' => 'Family Care Center', 'Dr. Davina Patel' => 'City Central Clinic',
    'Dr. Evan Lee' => 'Northside Wellness Center', 'Dr. Fiona Miller' => 'Family Care Center',
    'Dr. George Ryan' => 'City Central Clinic', 'Dr. Heather Kelly' => 'Northside Wellness Center',
    'Dr. Ivan Fernandez' => 'Family Care Center', 'Dr. Jess Willis' => 'City Central Clinic',
    'Dr. James Ford' => 'Northside Wellness Center', 'Dr. Lena Cruz' => 'Family Care Center',
    'Dr. Emily Chen' => 'City Central Clinic', 'Dr. Noah King' => 'Northside Wellness Center',
    'Dr. Sarah Lopez' => 'Family Care Center',
];

$dummy_specialties = [
    'Dr. Aaron Smith' => 'Cardiology', 'Dr. Brenda Jones' => 'Pediatrics',
    'Dr. Cindy Chen' => 'Surgery', 'Dr. Davina Patel' => 'Pediatrics',
    'Dr. Evan Lee' => 'Dentistry', 'Dr. Fiona Miller' => 'Family Medicine',
    'Dr. George Ryan' => 'Neurology', 'Dr. Heather Kelly' => 'Infectious Diseases',
    'Dr. Ivan Fernandez' => 'Public Health', 'Dr. Jess Willis' => 'Research',
    'Dr. Kurt Wallace' => 'Nephrology', 'Dr. Lena Cruz' => 'Dentistry',
    'Dr. Mia Chang' => 'Oncology', 'Dr. Noah King' => 'Orthopedics',
    'Dr. Olivia Reed' => 'Gastroenterology',
];

// Use single grey placeholder URL
$placeholder_grey_image = 'https://via.placeholder.com/90/cccccc/888888?text=MD'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Service Providers - NK Hospitals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* CSS styles remain the same, ensuring the aesthetic is preserved */
        body, html {
            margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; color: #333; line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        .navbar {
            display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; width: 100%; box-sizing: border-box; z-index: 1000;
        }
        .navbar .logo { display: flex; align-items: center; font-size: 1.5em; font-weight: 700; color: #444; }
        .navbar .logo i { margin-right: 10px; color: #d14757ff; }
        .navbar .nav-links { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; }
        .navbar .nav-links li { margin-left: 30px; }
        .navbar .contact-phone { color: #555; font-weight: 600; display: flex; align-items: center; margin-left: 30px; cursor: pointer; }
        .navbar .contact-phone i { color: #ec5c74ff; margin-right: 8px; font-size: 1.1em; }
        .navbar .action-buttons .btn {
            background: linear-gradient(90deg, #ed5574ff, #A3E4D7); color: white; padding: 10px 20px; border-radius: 5px; font-weight: 600; transition: opacity 0.3s ease;
        }
        
        .hero-banner {
            background: linear-gradient(135deg, #FFD1DC 0%, #A3E4D7 100%); 
            height: 250px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #333; 
            position: relative; 
        }
        .hero-banner h1 {
            font-family: 'Playfair Display', serif; font-size: 3.5em; z-index: 1; text-shadow: 1px 1px 5px rgba(255, 255, 255, 0.7);
        }
        
        /* Doctor Cards Grid */
        .doctors-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); 
            gap: 30px; 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 0 5%;
        }

        .doctor-card {
            background-color: #fff; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .doctor-card:hover {
            transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .doctor-card .top-section {
            background: linear-gradient(135deg, #93b4f1ff 0%, #f1818eff 100%); color: white; padding: 25px; display: flex; align-items: center; position: relative; min-height: 150px;
        }
        .doctor-card .location-tag {
            background-color: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px; font-size: 0.8em; position: absolute; top: 15px; left: 15px; font-weight: 600;
        }
        .doctor-card .doctor-image {
            width: 90px; height: 90px; border-radius: 50%; border: 4px solid white; object-fit: cover; margin-right: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); flex-shrink: 0;
        }
        .doctor-card .doctor-details h3 {
            font-family: 'Playfair Display', serif; font-size: 1.6em; margin: 0 0 5px;
        }
        .doctor-card .doctor-details p {
            font-size: 0.9em; margin: 0; line-height: 1.4;
        }
        .doctor-card .qualifications {
            font-weight: 600; color: #A3E4D7;
        }
        
        .doctor-card .middle-section {
            padding: 20px 25px; background-color: #f7f7f7; font-size: 0.9em; color: #555;
            border-bottom: 1px solid #eee;
        }
        
        .doctor-card .actions-section {
            padding: 15px 25px;
            display: flex;
            justify-content: flex-end;
        }

        .doctor-card .actions-section .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .doctor-card .actions-section .btn-profile {
            background-color: #A3E4D7;
            color: #2e5a9b;
        }
        .doctor-card .actions-section .btn-profile:hover {
            background-color: #8cd0c0;
        }

        /* Footer Styles */
        .footer {
            background-color: #333; color: #ccc; padding: 15px 5% 10px; font-size: 0.85em; font-family: 'Montserrat', sans-serif; margin-top: 80px;
        }
        .footer .container {
            display: flex; flex-wrap: wrap; justify-content: space-between; max-width: 1200px; margin: 0 auto;
        }
        .footer .col { flex: 1; min-width: 150px; margin-bottom: 5px; flex-basis: 25%; }
        .footer h5 {
            color: #fff; font-weight: 700; margin-bottom: 5px; font-size: 0.95em; 
            border-bottom: 1px solid #A3E4D7; padding-bottom: 2px; display: inline-block; font-family: 'Playfair Display', serif;
        }
        .footer ul { list-style: none; padding: 0; }
        .footer ul li { margin-bottom: 3px; }
        .footer-bottom { border-top: 1px solid #555; padding-top: 8px; margin-top: 10px; text-align: center; }

        @media (max-width: 768px) {
            .doctors-grid { grid-template-columns: 1fr; }
            .doctor-card .top-section { flex-direction: column; text-align: center; }
            .doctor-card .doctor-image { margin: 0 0 15px 0; }
            .doctor-card .location-tag { position: static; margin-bottom: 10px; }
            .footer .col { min-width: 100%; text-align: center; margin-bottom: 15px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-clinic-medical"></i> NK Hospitals
        </div>
        <ul class="nav-links">
            <li><a href="role_select.php">Home</a></li>
            <li><a href="role_select.php#why-choose-us">About</a></li>
            <li><a href="role_select.php#specializations">Services</a></li>
            <li class="contact-phone">
                <a href="tel:06364421819">
                    <i class="fas fa-phone"></i>
                    0636 442 1819
                </a>
            </li>
        </ul>
        <div class="action-buttons">
            <a href="index.php?role=patient" class="btn">Login</a>
        </div>
    </nav>

    <div class="hero-banner">
        <h1>Our Expert Doctors</h1>
    </div>

    <div class="doctors-grid">
        <?php foreach ($dummy_doctors as $doctor):
            $doctor_name = htmlspecialchars($doctor['name']);
            $qualification = htmlspecialchars($doctor['qual']);
            $description = htmlspecialchars($doctor['spec_desc']);
            
            // This is the CRITICAL change: passing the user_id to the profile page
            $user_id = $doctor['user_id'];
            
            // Use the single grey placeholder URL
            $image_src = $placeholder_grey_image; 
            
            $location = $dummy_locations[$doctor_name] ?? 'Metro City Office';
            $specialty = $dummy_specialties[$doctor_name] ?? 'General Practice';
        ?>
            <div class="doctor-card">
                <div class="top-section">
                    <span class="location-tag"><?php echo $location; ?></span>
                    <img src="<?php echo $image_src; ?>" alt="<?php echo $doctor_name; ?> Placeholder Logo" class="doctor-image">
                    <div class="doctor-details">
                        <h3><?php echo $doctor_name; ?></h3>
                        <p class="qualifications"><?php echo $qualification; ?></p>
                    </div>
                </div>
                
                <div class="middle-section">
                    <p>Primary Specialty: **<?php echo $specialty; ?>**</p>
                    <p><?php echo $description; ?></p>
                </div>
                
                <div class="actions-section">
                    <a href="doctor_profile.php?id=<?php echo $user_id; ?>" class="btn btn-profile">View Profile</a>
                </div>
            </div>
        <?php endforeach; ?>
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
                    <li><a href="role_select.php">Home</a></li>
                    <li><a href="role_select.php#why-choose-us">About Us</a></li>
                    <li><a href="role_select.php#specializations">Our Services</a></li>
                </ul>
            </div>
            <div class="col">
                <h5>Patient Access</h5>
                <ul>
                    <li><a href="index.php?role=patient">Patient Login</a></li>
                    <li><a href="book_appointment.php">Book Appointment</a></li>
                    <li><a href="emergency_booking.php" style="color: #dc3545;">Emergency</a></li>
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

</body>
</html>