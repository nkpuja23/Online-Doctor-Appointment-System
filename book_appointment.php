<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Patients
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: index.php?error=access_denied");
    exit();
}

$patient_id = $_SESSION['user_id'];
$booking_success = '';
$current_date = date('Y-m-d'); 
$current_time_stamp = time(); // Current time in seconds since epoch

$grouped_available_slots = []; 

$selected_spec_id = isset($_GET['specialization_id']) ? intval($_GET['specialization_id']) : 0;
$search_date_submitted = isset($_GET['search_date']);

// --- MODE CONFIGURATION ---
$mode = $_GET['mode'] ?? 'regular';

// CRITICAL FIX: Set min_date and max_date based on mode selection.
if ($mode == 'regular') {
    // Regular: Min date is TOMORROW, Max date is 30 days out from tomorrow
    $min_date = date('Y-m-d', strtotime('+1 day')); 
    $max_date = date('Y-m-d', strtotime($min_date . ' +30 days')); 
    $slot_increment = 60; 
} else {
    // Emergency: Min date is TODAY, Max date is TODAY (and we apply time filtering)
    $min_date = $current_date;
    $max_date = $current_date; 
    $slot_increment = 30;
}


$selected_date = $min_date; 
$selected_day = date('l', strtotime($min_date)); 


// ----------------------------------------------------------------------------------
// --- 0. APPOINTMENT HANDLERS (CANCELLATION & HISTORY REMOVAL) ---
// ----------------------------------------------------------------------------------

// A. HISTORY REMOVAL HANDLER (Re-implemented for simple deletion)
if (isset($_GET['action']) && $_GET['action'] == 'remove_history' && isset($_GET['appt_id'])) {
    $delete_id = $_GET['appt_id'];
    
    $conn->begin_transaction();
    try {
        // 1. Delete associated Payment record FIRST (to clear the FK dependency)
        $stmt_delete_payment = $conn->prepare("DELETE FROM Payments WHERE appointment_id = ?");
        $stmt_delete_payment->bind_param("i", $delete_id);
        $stmt_delete_payment->execute();
        $stmt_delete_payment->close();

        // 2. Delete the Appointment record
        $stmt_delete = $conn->prepare("DELETE FROM Appointments WHERE appointment_id = ? AND patient_id = ?");
        $stmt_delete->bind_param("ii", $delete_id, $patient_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        $conn->commit();
        $msg = "Appointment ID {$delete_id} permanently removed from history.";
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $msg = "Error deleting appointment: Foreign key constraint failed. Check Payment record.";
    }
    
    // Redirect back to dashboard to clear URL parameters and display message
    header("Location: patient_dashboard.php?msg=" . urlencode($msg));
    exit();
}

// B. CANCELLATION HANDLER (Time-Sensitive Fee Check)
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['appt_id'])) {
    $cancel_id = $_GET['appt_id'];
    
    // 1. Fetch appointment details (date and time)
    $stmt_fetch = $conn->prepare("SELECT appointment_date, appointment_time FROM Appointments WHERE appointment_id = ? AND patient_id = ?");
    $stmt_fetch->bind_param("ii", $cancel_id, $patient_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows == 0) {
        $msg = "Error: Appointment not found or unauthorized.";
    } else {
        $appt_data = $result_fetch->fetch_assoc();
        
        // Combine date and time into a single timestamp string
        $appointment_datetime_str = $appt_data['appointment_date'] . ' ' . $appt_data['appointment_time'];
        
        // Calculate the critical timestamp (Appointment Time minus exactly 24 hours)
        $critical_cutoff_timestamp = strtotime($appointment_datetime_str) - (24 * 3600); 
        
        $stmt_fetch->close();

        // 2. CHECK 24-HOUR CUTOFF
        if ($current_time_stamp >= $critical_cutoff_timestamp) {
            // CANCELLATION PERIOD EXHAUSTED (FEE APPLIED)
            header("Location: payment_portal.php?appt_id={$cancel_id}");
            exit();
        } else {
            // VALID CANCELLATION (NO FEE APPLIED)
            $status_id_cancelled = 4;
            $stmt_cancel = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ? AND patient_id = ?");
            $stmt_cancel->bind_param("iii", $status_id_cancelled, $cancel_id, $patient_id);
            
            if ($stmt_cancel->execute()) {
                $msg = "Appointment ID {$cancel_id} has been successfully cancelled (No Fee Applied).";
            } else {
                $msg = "Error: Could not cancel appointment.";
            }
            $stmt_cancel->close();
        }
    }
    
    // Redirect back to dashboard to clear URL parameters and display message
    header("Location: patient_dashboard.php?msg=" . urlencode($msg));
    exit();
}


// --- 1. HANDLE BOOKING SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_slot'])) {
    list($schedule_id, $doctor_id, $appt_time) = explode('|', $_POST['selected_slot_info']);
    
    $appt_date = $_POST['appt_date'];
    $status_id_confirmed = 2; 

    $temp_spec_id = isset($_POST['temp_spec_id']) ? intval($_POST['temp_spec_id']) : 0; 
    $post_mode = $_POST['mode'] ?? 'regular';


    // CRITICAL CHECK: Double-check if the slot was just taken
    $check_slot = $conn->prepare("SELECT appointment_id FROM Appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status_id IN (2, 3)");
    $check_slot->bind_param("iss", $doctor_id, $appt_date, $appt_time);
    $check_slot->execute();
    
    if ($check_slot->get_result()->num_rows > 0) {
        // Conflict detected: Redirect with original parameters and error flag.
        header("Location: book_appointment.php?specialization_id={$temp_spec_id}&search_date={$appt_date}&booking_error=taken&mode={$post_mode}");
        exit();
    } else {
        // INSERT new appointment into Appointments table
        $stmt_insert = $conn->prepare("
            INSERT INTO Appointments 
            (patient_id, doctor_id, schedule_id, appointment_date, appointment_time, status_id) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt_insert->bind_param("iiisss", 
            $patient_id, $doctor_id, $schedule_id, $appt_date, $appt_time, $status_id_confirmed);

        if ($stmt_insert->execute()) {
            // Success: Redirect to clear the POST data and display a clean success message
            header("Location: book_appointment.php?specialization_id={$temp_spec_id}&search_date={$appt_date}&booking_success=true&mode={$post_mode}");
            exit();
        } else {
            $booking_success = "Error booking appointment.";
        }
        $stmt_insert->close();
    }
    $check_slot->close();
}

// Check for the quiet error parameter or success parameter added above
if (isset($_GET['booking_error']) && $_GET['booking_error'] === 'taken') {
     $booking_success = "Error: This time slot was just taken by another patient. Please select another slot from the list.";
} elseif (isset($_GET['booking_success']) && $_GET['booking_success'] === 'true') {
    // We need to fetch the date and specialization again from the GET parameters for the success message
    $selected_date = $_GET['search_date'] ?? $min_date; 
    $booking_success = "Appointment successfully booked and CONFIRMED for " . $selected_date . ".";
}


// --- 2. FETCH LOOKUP DATA AND SLOTS ---

// A. Fetch Specializations for the dropdown menu
$specializations_result = $conn->query("SELECT specialization_id, specialization_name FROM Specializations ORDER BY specialization_name ASC");
$all_specializations = $specializations_result->fetch_all(MYSQLI_ASSOC);

// B. Determine Search Parameters
if ($search_date_submitted) {
    $selected_date = $_GET['search_date'];
    $selected_day = date('l', strtotime($selected_date));
    $selected_spec_id = intval($_GET['specialization_id']);
} else {
    // Default the selected date to the calculated min_date (tomorrow for regular, today for emergency)
    $selected_date = $min_date;
    $selected_day = date('l', strtotime($min_date));
}

// C. Construct the Dynamic SQL Query based on Specialization ID
$sql_schedules = "
    SELECT 
        DS.schedule_id, DS.doctor_id, DS.start_time, DS.end_time,
        U.name AS doctor_name, S.specialization_name, 
        C.name AS clinic_name
    FROM Doctor_Schedules DS
    JOIN Doctors D ON DS.doctor_id = D.doctor_id
    JOIN Users U ON D.doctor_id = U.user_id
    JOIN Specializations S ON D.specialization_id = S.specialization_id
    JOIN Clinics C ON DS.clinic_id = C.clinic_id
    WHERE DS.day_of_week = ?
";
$param_types = "s";
$param_values = [$selected_day];

if ($selected_spec_id > 0) {
    $sql_schedules .= " AND D.specialization_id = ?";
    $param_types .= "i";
    $param_values[] = $selected_spec_id;
}

$stmt_schedules = $conn->prepare($sql_schedules);
if ($stmt_schedules) {
    $stmt_schedules->bind_param($param_types, ...$param_values);
    $stmt_schedules->execute();
    $all_schedules = $stmt_schedules->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_schedules->close();
} else {
    $all_schedules = [];
}


// D. Get all existing appointments for the selected day to filter out taken slots
$booked_slots_query = $conn->prepare("SELECT doctor_id, appointment_time FROM Appointments WHERE appointment_date = ? AND status_id IN (2, 3)");
$booked_slots_query->bind_param("s", $selected_date);
$booked_slots_query->execute();
$booked_slots_result = $booked_slots_query->get_result();

$taken_slots = [];
while ($row = $booked_slots_result->fetch_assoc()) {
    $key = $row['doctor_id'] . '_' . $row['appointment_time'];
    $taken_slots[$key] = true;
}
$booked_slots_query->close();

// E. Process schedules into slots and group by doctor
$minimum_valid_timestamp = strtotime('+60 minutes'); 

foreach ($all_schedules as $schedule) {
    $doctor_id = $schedule['doctor_id'];
    $interval_start = strtotime($schedule['start_time']);
    $interval_end = strtotime($schedule['end_time']);
    
    if (!isset($grouped_available_slots[$doctor_id])) {
        $grouped_available_slots[$doctor_id] = [
            'info' => $schedule,
            'slots' => []
        ];
    }
    
    // Determine the slot increment based on the mode
    $increment_seconds = $slot_increment * 60; // 30 min or 60 min

    while (date('H:i:s', $interval_start) < date('H:i:s', $interval_end)) {
        $slot_time = date("H:i:s", $interval_start);
        $slot_key = $doctor_id . '_' . $slot_time;
        
        // --- CRITICAL TIME CHECK LOGIC ---
        $full_appt_timestamp = strtotime($selected_date . ' ' . $slot_time);
        
        // Determine if the slot is too soon/in the past (This check only applies in emergency mode/current day)
        $is_too_soon = ($mode == 'emergency' && $full_appt_timestamp < $minimum_valid_timestamp);

        // Only add slot if it is NOT taken AND NOT too soon/in the past
        if (!isset($taken_slots[$slot_key]) && !$is_too_soon) {
            $grouped_available_slots[$doctor_id]['slots'][] = [
                'time_display' => date("h:i A", $interval_start),
                'time_value' => $schedule['schedule_id'] . '|' . $doctor_id . '|' . $slot_time
            ];
        }
        $interval_start = strtotime('+' . $increment_seconds . ' seconds', $interval_start); 
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container full-screen">
        <div class="header-bar">
            <h1 style="color: white;">Book a New Appointment</h1>
        </div>
        <p>
            <a href="patient_dashboard.php" class="button">Back to Dashboard</a>
        </p>

        <?php if ($booking_success): ?>
            <p class="<?php echo strpos($booking_success, 'Error') !== false ? 'status-cancelled' : 'status-confirmed'; ?>"><?php echo $booking_success; ?></p>
        <?php endif; ?>

        <h3>1. Select Specialization and Date</h3>
        <form method="get" action="book_appointment.php">
            <input type="hidden" name="mode" value="<?php echo $mode; ?>"> <!-- Pass mode through search form -->

            <label for="specialization_id">Doctor Type (Specialization):</label>
            <select id="specialization_id" name="specialization_id" required>
                <option value="0" disabled selected>-- Select Specialization --</option>
                <?php foreach($all_specializations as $spec): ?>
                    <option 
                        value="<?php echo $spec['specialization_id']; ?>" 
                        <?php echo ($selected_spec_id == $spec['specialization_id']) ? 'selected' : ''; ?>
                    >
                        <?php echo $spec['specialization_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="search_date">Appointment Date (Mode: <?php echo ucfirst($mode); ?>):</label>
            <!-- MIN and MAX ATTRIBUTES enforce 1-day minimum and 30-day maximum -->
            <input 
                type="date" 
                id="search_date" 
                name="search_date" 
                value="<?php echo $selected_date; ?>" 
                min="<?php echo $min_date; ?>" 
                max="<?php echo $max_date; ?>"
                required
                <?php if ($mode == 'emergency') echo 'readonly'; ?> 
            >
            <button type="submit">Check Availability</button>
        </form>
        
        <h3>2. Available <?php echo $slot_increment; ?>-Minute Slots for <?php echo date("l, F jS, Y", strtotime($selected_date)); ?></h3>
        
        <?php if ($selected_spec_id == 0): ?>
            <p style="color: #dc3545; font-weight: bold;">Please select a specialization and a date to see available doctors.</p>
        <?php elseif (empty($grouped_available_slots)): ?>
            <p>No doctors in the selected specialization have available slots on this day.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Doctor Name</th>
                    <th>Specialization</th>
                    <th>Clinic</th>
                    <th>Available Time Slot</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($grouped_available_slots as $doctor_data): 
                    $info = $doctor_data['info'];
                    $slots = $doctor_data['slots'];
                    
                    if (!empty($slots)):
                ?>
                    <tr>
                        <td><?php echo $info['doctor_name']; ?></td>
                        <td><?php echo $info['specialization_name']; ?></td>
                        <td><?php echo $info['clinic_name']; ?></td>
                        <td>
                            <form method="post" action="book_appointment.php" style="margin:0;">
                                <input type="hidden" name="book_slot" value="1">
                                <input type="hidden" name="appt_date" value="<?php echo $selected_date; ?>">
                                <!-- NEW: Pass specialization ID back for successful redirect -->
                                <input type="hidden" name="temp_spec_id" value="<?php echo $selected_spec_id; ?>">
                                <input type="hidden" name="mode" value="<?php echo $mode; ?>"> <!-- Pass mode through POST -->
                                
                                <select name="selected_slot_info" required style="width: 150px; margin:0;">
                                    <option value="" disabled selected>Select Time</option>
                                    <?php foreach ($slots as $slot): ?>
                                        <option value="<?php echo $slot['time_value']; ?>">
                                            <?php echo $slot['time_display']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                        </td>
                        <td>
                                <button type="submit" style="padding: 5px 10px;">Book Now</button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endif;
                endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>