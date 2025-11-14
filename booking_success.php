<?php
// Ensure session is started to use the theme
session_start();

// We expect a URL-encoded message from role_select.php
// htmlspecialchars is used for general safety, but we now use stripslashes for markdown-like formatting 
// and allow specific HTML (like the <b> tag) to pass through if you want to bold clinic/phone.
$message = $_GET['msg'] ?? "Your booking request has been successfully submitted.";

// Basic cleaning, but allowing basic text formatting for the dynamic message.
$clean_message = nl2br(htmlspecialchars($message));
$clean_message = str_replace(['**', '*'], ['<b>', '</b>'], $clean_message); // Convert markdown ** to <b>

?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Request Received</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container full-screen">
        <div class="header-bar">
            <div style="display: flex; align-items: center;">
                <span class="logo-circle">NK</span>
                <h1>NK Hospitals</h1>
            </div>
            <div style="display: flex; gap: 25px; align-items: center;">
                <a href="role_select.php" class="button" style="background-color: #ff6b81; padding: 10px 20px;">
                    <i class="fas fa-home" style="margin-right: 5px;"></i> Back to Main Portal
                </a>
            </div>
        </div>
        
        <div class="centered-content" style="text-align: center; max-width: 600px; margin-top: 100px;">
            
            <i class="fas fa-calendar-check" style="font-size: 5em; color: #4caf50; margin-bottom: 20px;"></i>
            
            <h2 style="color: #4caf50; font-size: 2.5em;">Booking Request Received!</h2>
            
            <p style="font-size: 1.2em; color: #333; margin-top: 20px; line-height: 1.5;">
                <!-- Display the safely cleaned, dynamic message -->
                <?php echo $clean_message; ?>
            </p>
            
            <p style="font-size: 1.1em; color: #0077b6; font-weight: 600; margin-top: 30px;">
                Our team will contact you shortly to confirm your slot details and guide you through registration.
            </p>

            <a href="role_select.php" class="button" style="margin-top: 30px;">
                Continue Exploring NK Hospitals
            </a>
        </div>
    </div>
</body>
</html>