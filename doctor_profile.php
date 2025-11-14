<?php
// doctor_profile.php - Dynamic Profile Viewer
session_start();

// --- 1. Master Doctor Data (Internal Database) ---
// This array holds the complete data for all 15 doctors.
$doctors_data = [
    101 => [
        'name' => 'Dr. Aaron Smith',
        'qual' => 'MD, Cardiologist, Fellow of ACC',
        'spec_desc' => 'Leading expert in non-invasive cardiac procedures and complex cardiovascular health management.',
        'specialty' => 'Cardiology',
        'location' => 'City Central Clinic',
        'bio' => 'Dr. Smith is the Head of Cardiology at NK Hospitals, specializing in diagnostics and preventive heart care. He has over 20 years of experience and is dedicated to patient education and recovery.',
        'hours' => 'Mondays & Wednesdays: 9:00 AM - 5:00 PM',
        'image_text' => 'AS'
    ],
    102 => [
        'name' => 'Dr. Brenda Jones',
        'qual' => 'PhD, Clinical Psychologist',
        'spec_desc' => 'Focuses on child and adolescent behavioral therapy.',
        'specialty' => 'Pediatrics',
        'location' => 'Northside Wellness Center',
        'bio' => 'A compassionate clinical psychologist specializing in pediatric mental health. Dr. Jones helps families navigate developmental and behavioral challenges.',
        'hours' => 'Tuesdays & Thursdays: 10:00 AM - 6:00 PM',
        'image_text' => 'BJ'
    ],
    103 => [
        'name' => 'Dr. Cindy Chen',
        'qual' => 'MBBS, FRCS, Expertise in Laparoscopic Surgery',
        'spec_desc' => 'Highly experienced in advanced minimally invasive surgery.',
        'specialty' => 'Surgery',
        'location' => 'Family Care Center',
        'bio' => 'Dr. Chen is renowned for her precision in laparoscopic and general surgeries, minimizing patient recovery time and scarring.',
        'hours' => 'Fridays: 8:00 AM - 4:00 PM',
        'image_text' => 'CC'
    ],
    104 => [
        'name' => 'Dr. Davina Patel',
        'qual' => 'MD, Pediatrician, Board-certified in Neonatal Medicine',
        'spec_desc' => 'Specializes in the health and care of newborns and infants.',
        'specialty' => 'Pediatrics',
        'location' => 'City Central Clinic',
        'bio' => 'As a leading neonatologist, Dr. Patel provides the highest level of care for premature and critically ill infants.',
        'hours' => 'Mondays & Saturdays: 9:00 AM - 1:00 PM',
        'image_text' => 'DP'
    ],
    105 => [
        'name' => 'Dr. Evan Lee',
        'qual' => 'DDS, Maxillofacial Surgeon',
        'spec_desc' => 'Advanced surgical care for the face, mouth, and jaws.',
        'specialty' => 'Dentistry',
        'location' => 'Northside Wellness Center',
        'bio' => 'Dr. Lee treats conditions of the head, neck, face, and jaws, from dental implants to complex reconstructive surgery.',
        'hours' => 'Wednesdays: 1:00 PM - 7:00 PM',
        'image_text' => 'EL'
    ],
    106 => [
        'name' => 'Dr. Fiona Miller',
        'qual' => 'DO, Family Medicine Physician',
        'spec_desc' => 'Provides comprehensive, holistic primary care for all ages.',
        'specialty' => 'Family Medicine',
        'location' => 'Family Care Center',
        'bio' => 'Dr. Miller is committed to preventative care and managing chronic conditions across the entire family lifespan.',
        'hours' => 'Thursdays: 9:00 AM - 5:00 PM',
        'image_text' => 'FM'
    ],
    107 => [
        'name' => 'Dr. George Ryan',
        'qual' => 'MD, Neurologist',
        'spec_desc' => 'Pioneering treatments for neurological and chronic pain conditions.',
        'specialty' => 'Neurology',
        'location' => 'City Central Clinic',
        'bio' => 'With advanced training in headache disorders and movement disorders, Dr. Ryan offers specialized diagnostic and treatment plans.',
        'hours' => 'Mondays & Fridays: 10:00 AM - 4:00 PM',
        'image_text' => 'GR'
    ],
    108 => [
        'name' => 'Dr. Heather Kelly',
        'qual' => 'MBBS, MD, Expert in Infectious Diseases',
        'spec_desc' => 'Specializes in the diagnosis and management of complex infections.',
        'specialty' => 'Infectious Diseases',
        'location' => 'Northside Wellness Center',
        'bio' => 'Dr. Kelly manages cases ranging from routine infections to tropical diseases and is a key consultant for hospital infection control.',
        'hours' => 'Wednesdays: 11:00 AM - 3:00 PM',
        'image_text' => 'HK'
    ],
    109 => [
        'name' => 'Dr. Ivan Fernandez',
        'qual' => 'MPH, Epidemiologist',
        'spec_desc' => 'Public Health consultant focused on disease prevention strategies.',
        'specialty' => 'Public Health',
        'location' => 'Family Care Center',
        'bio' => 'Dr. Fernandez works behind the scenes, applying scientific methods to control and prevent the spread of diseases in the community.',
        'hours' => 'Thursdays: 9:00 AM - 1:00 PM',
        'image_text' => 'IF'
    ],
    110 => [
        'name' => 'Dr. Jess Willis',
        'qual' => 'PhD, Medical Researcher, Gene Therapy Specialist',
        'spec_desc' => 'Active researcher in genetic therapies for chronic illnesses.',
        'specialty' => 'Research',
        'location' => 'City Central Clinic',
        'bio' => 'Dr. Willis bridges the gap between research and clinical application, focusing on cutting-edge gene therapy trials.',
        'hours' => 'Tuesdays: 1:00 PM - 5:00 PM',
        'image_text' => 'JW'
    ],
    111 => [
        'name' => 'Dr. James Ford',
        'qual' => 'MD, Nephrologist',
        'spec_desc' => 'Specializes in kidney function, dialysis, and renal care.',
        'specialty' => 'Nephrology',
        'location' => 'Northside Wellness Center',
        'bio' => 'A dedicated physician managing all aspects of kidney health, including hypertension and transplant care.',
        'hours' => 'Fridays: 10:00 AM - 3:00 PM',
        'image_text' => 'JF'
    ],
    112 => [
        'name' => 'Dr. Andrew Patel',
        'qual' => 'BDS, Cosmetic Dentist',
        'spec_desc' => 'Focuses on dental implants and advanced cosmetic procedures.',
        'specialty' => 'Dentistry',
        'location' => 'Family Care Center',
        'bio' => 'Dr. Patel provides state-of-the-art cosmetic and restorative dental treatments, prioritizing patient comfort and perfect results.',
        'hours' => 'Mondays: 1:00 PM - 5:00 PM',
        'image_text' => 'AP'
    ],
    113 => [
        'name' => 'Dr. William Grey',
        'qual' => 'MD, Oncologist',
        'spec_desc' => 'Leading specialist in complex cancer treatment protocols.',
        'specialty' => 'Oncology',
        'location' => 'City Central Clinic',
        'bio' => 'Dr. Grey works with a multidisciplinary team to offer personalized and advanced care for various cancers.',
        'hours' => 'Saturdays: 9:00 AM - 12:00 PM',
        'image_text' => 'WG'
    ],
    114 => [
        'name' => 'Dr. Emily Chen',
        'qual' => 'D.O., Orthopedic Surgeon',
        'spec_desc' => 'Expert in knee and shoulder repair and sports injury rehabilitation.',
        'specialty' => 'Orthopedics',
        'location' => 'Northside Wellness Center',
        'bio' => 'Specializing in minimally invasive orthopedic surgery, Dr. Chen helps athletes and active individuals return to full mobility.',
        'hours' => 'Tuesdays: 10:00 AM - 3:00 PM',
        'image_text' => 'EC'
    ],
    115 => [
        'name' => 'Dr. Sarah Lopez',
        'qual' => 'MD, Gastroenterologist',
        'spec_desc' => 'Specializes in disorders of the digestive tract and endoscopic procedures.',
        'specialty' => 'Gastroenterology',
        'location' => 'Family Care Center',
        'bio' => 'Dr. Lopez is highly skilled in both diagnostic and therapeutic endoscopy, treating conditions from IBS to complex liver issues.',
        'hours' => 'Sundays: 10:00 AM - 2:00 PM',
        'image_text' => 'SL'
    ]
];

// --- 2. Dynamic Data Retrieval ---
$doctor_id = $_GET['id'] ?? null;
$doctor = $doctors_data[$doctor_id] ?? null;

// Handle case where doctor ID is missing or invalid
if (!$doctor) {
    http_response_code(404);
    die("<h1>404 Error: Doctor Not Found</h1><p>The profile you requested does not exist or the ID is invalid.</p><p><a href='doctor_info.php'>Back to Doctor List</a></p>");
}

// Prepare specific data variables
$doctor_name = $doctor['name'];
$qualification = $doctor['qual'];
$location = $doctor['location'];
$specialty = $doctor['specialty'];
$bio = $doctor['bio'];
$hours = $doctor['hours'];
$image_url = 'https://via.placeholder.com/150/cccccc/888888?text=' . urlencode($doctor['image_text']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile: <?php echo $doctor_name; ?> - NK Hospitals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body, html {
            margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; color: #333; line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        .rose-color { color: #eb4f5cff; }
        .aqua-color { color: #A3E4D7; }

        /* Navbar (Reduced for simplicity and focus on profile) */
        .navbar {
            display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 1000;
        }
        .navbar .logo { font-size: 1.5em; font-weight: 700; color: #444; }
        .navbar .logo i { margin-right: 10px; color: #ec3d57ff; }
        .navbar .action-buttons .btn {
            background: linear-gradient(90deg, #e04f60ff, #A3E4D7); color: white; padding: 10px 20px; border-radius: 5px; font-weight: 600; text-decoration: none;
        }
        .navbar .back-link {
            font-size: 1em; color: #555; margin-right: 20px;
        }

        /* Profile Container */
        .profile-container {
            max-width: 900px;
            margin: 60px auto;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Header Section */
        .profile-header {
            background: linear-gradient(135deg, #a1b7edff 0%, #e9638bff 100%);
            padding: 40px;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 6px solid #f0f2f5;
            object-fit: cover;
            margin-right: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            flex-shrink: 0;
        }
        .header-details h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            margin: 0 0 5px;
        }
        .header-details p {
            margin: 0 0 10px;
            font-size: 1.1em;
            color: #A3E4D7;
            font-weight: 600;
        }
        .location-tag {
            font-size: 0.9em;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }

        /* Body Content */
        .profile-content {
            padding: 40px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8em;
            color: #ee3d4fff;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .detail-item {
            padding: 15px;
            border-left: 3px solid #A3E4D7;
            background-color: #f7f7f7;
            border-radius: 5px;
        }
        .detail-item strong {
            display: block;
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }
        .detail-item span {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }
        
        .bio-section p {
            font-size: 1em;
            margin-bottom: 25px;
            color: #444;
        }

        .cta-section {
            text-align: center;
            padding-top: 20px;
        }
        .cta-section .btn-book {
            background: linear-gradient(90deg, #f3516cff, #A3E4D7);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Footer (Minimal) */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.8em;
            color: #888;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-image {
                margin: 0 0 20px 0;
            }
            .details-grid {
                grid-template-columns: 1fr;
            }
            .profile-container {
                margin: 20px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="doctor_info.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Doctors
        </a>
        <div class="logo">
            <i class="fas fa-clinic-medical"></i> NK Hospitals
        </div>
        <div class="action-buttons">
            <a href="book_appointment.php?doctor_id=<?php echo $doctor_id; ?>" class="btn">Book Now</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo $image_url; ?>" alt="<?php echo $doctor_name; ?> Logo" class="profile-image">
            <div class="header-details">
                <h1><?php echo $doctor_name; ?></h1>
                <p><?php echo $qualification; ?></p>
                <span class="location-tag"><i class="fas fa-hospital"></i> <?php echo $location; ?></span>
            </div>
        </div>

        <div class="profile-content">
            <div class="bio-section">
                <h2 class="section-title">Professional Biography</h2>
                <p><?php echo $bio; ?></p>
            </div>
            
            <h2 class="section-title">Key Details & Availability</h2>
            <div class="details-grid">
                
                <div class="detail-item">
                    <strong>Primary Specialty</strong>
                    <span class="rose-color"><?php echo $specialty; ?></span>
                </div>
                
                <div class="detail-item">
                    <strong>Practicing Location</strong>
                    <span class="aqua-color"><?php echo $location; ?></span>
                </div>
                
                <div class="detail-item" style="grid-column: 1 / span 2;">
                    <strong>Office Hours (<?php echo $specialty; ?>)</strong>
                    <span><?php echo $hours; ?></span>
                </div>

                <div class="detail-item">
                    <strong>Patient Rating</strong>
                    <span><i class="fas fa-star rose-color"></i> 4.8 / 5.0</span>
                </div>
                
                <div class="detail-item">
                    <strong>Total Experience</strong>
                    <span>15+ Years</span>
                </div>
            </div>

            <div class="cta-section">
                <a href="book_appointment.php?doctor_id=<?php echo $doctor_id; ?>" class="btn-book">Schedule an Appointment</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        &copy; <?php echo date("Y"); ?> NK Hospitals. All Rights Reserved.
    </footer>

</body>
</html>