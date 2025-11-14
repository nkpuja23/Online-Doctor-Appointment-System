<?php
require_once 'db_connect.php'; 

// Security Check: Must be logged in as Patient
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: index.php?error=access_denied");
    exit();
}

$patient_id = $_SESSION['user_id'];
$message = "";
$fee_amount = 250.00; // Define the late cancellation fee in INR
$cancel_id = isset($_REQUEST['appt_id']) ? intval($_REQUEST['appt_id']) : 0; 
$confirm_action = isset($_REQUEST['confirm']) && $_REQUEST['confirm'] === 'true';
$payment_processed = false;

// ----------------------------------------------------------------
// 1. DEFINITION OF VALID TEST CREDENTIALS (The "Bank" List)
// ----------------------------------------------------------------
$valid_cards = [
    // Format: 'CardNumber' => ['expiry' => 'MM/YY', 'cvv' => 'XXX']
    '1111222233334444' => ['expiry' => '12/26', 'cvv' => '123'], // VISA Test Card
    '5555666677778888' => ['expiry' => '08/27', 'cvv' => '901'], // MASTER Test Card
];

// ----------------------------------------------------------------
// 2. HANDLE PAYMENT PROCESSING (Simulated)
// ----------------------------------------------------------------
if ($cancel_id > 0 && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'] ?? 'Card';
    $validation_error = false;
    
    // --- ADVANCED VALIDATION LOGIC ---
    if ($payment_method === 'Card') {
        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv = $_POST['card_cvv'] ?? '';

        // Check 1: General Format Validation (as before)
        if (!preg_match('/^\d{13,19}$/', $card_number)) {
            $message = "Error: Invalid card number format.";
            $validation_error = true;
        }
        // Check 2: Against Test Credentials (The "Existence" Check)
        elseif (!array_key_exists($card_number, $valid_cards)) {
            $message = "Error: Card number is not a recognized test card.";
            $validation_error = true;
        }
        // Check 3: CVV and Expiry Match for the recognized card
        elseif ($valid_cards[$card_number]['expiry'] !== $card_expiry || $valid_cards[$card_number]['cvv'] !== $card_cvv) {
             $message = "Error: CVV or Expiry Date mismatch for the entered card.";
             $validation_error = true;
        }

    } elseif ($payment_method === 'UPI' && empty($_POST['upi_id'])) {
        $message = "Error: UPI payment selected, but UPI ID is required.";
        $validation_error = true;
    }
    // --- END VALIDATION LOGIC ---


    if ($validation_error) {
        $confirm_action = true; // Stay on the payment form view
    } else {
        // --- SIMULATE SUCCESSFUL TRANSACTION (If Card is Validated or UPI is entered) ---
        
        $conn->begin_transaction();
        try {
            $status_id_cancelled = 4; // Status ID 4 is 'Cancelled'
            $payment_status = 'Success';
            $current_datetime = date('Y-m-d H:i:s');
            
            // A. Update appointment status to Cancelled (4)
            $stmt_cancel = $conn->prepare("UPDATE Appointments SET status_id = ? WHERE appointment_id = ? AND patient_id = ?");
            $stmt_cancel->bind_param("iii", $status_id_cancelled, $cancel_id, $patient_id);
            $stmt_cancel->execute();
            
            // B. INSERT PAYMENT RECORD
            $stmt_payment = $conn->prepare("
                INSERT INTO Payments (appointment_id, amount, payment_date, payment_method, payment_status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_payment->bind_param("idsss", $cancel_id, $fee_amount, $current_datetime, $payment_method, $payment_status);
            $stmt_payment->execute();

            $conn->commit();
            $message = "Success! A cancellation fee of ₹" . number_format($fee_amount, 0) . " has been charged via " . $payment_method . ", and Appointment ID {$cancel_id} is now CANCELLED and the payment is recorded.";
            $payment_processed = true;
            
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $message = "Error recording payment or updating status: Transaction Failed.";
            $payment_processed = true;
        }
    }
} 
// ----------------------------------------------------------------
// 3. INITIAL FEE WARNING / REDIRECTION HANDLING
// ----------------------------------------------------------------
elseif ($cancel_id > 0 && !isset($_REQUEST['payment_form'])) {
    $message = "CANCELLATION WARNING: You are attempting to cancel within the 24-hour cutoff period.";
} elseif ($cancel_id > 0 && isset($_REQUEST['payment_form'])) {
    $confirm_action = true;
} else {
    $message = "Error: Invalid cancellation request.";
    $cancel_id = 0;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Cancellation Payment Portal</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // JavaScript to toggle payment detail fields
        function showPaymentDetails(method) {
            const cardFields = document.getElementById('card-details');
            const upiFields = document.getElementById('upi-details');
            const cardInputs = ['card_number', 'card_expiry', 'card_cvv'];
            const upiInputs = ['upi_id'];

            cardFields.style.display = 'none';
            upiFields.style.display = 'none';

            // Function to set required status
            const setRequired = (inputs, status) => {
                inputs.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.required = status;
                });
            };

            // Set all fields to false first
            setRequired([...cardInputs, ...upiInputs], false);

            if (method === 'Card') {
                cardFields.style.display = 'block';
                setRequired(cardInputs, true);
            } else if (method === 'UPI') {
                upiFields.style.display = 'block';
                setRequired(upiInputs, true);
            }
        }
        
        // Initialize fields based on default radio selection
        document.addEventListener('DOMContentLoaded', () => {
             showPaymentDetails('Card'); // Card is checked by default
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header-bar">
            <h1 style="color: white;">Cancellation Fee Required</h1>
        </div>
        
        <?php if ($payment_processed): ?>
            <!-- VIEW 3: SUCCESS/FAILURE MESSAGE -->
            <h2 class="<?php echo strpos($message, 'Success') !== false ? 'status-confirmed' : 'status-cancelled'; ?>">
                <?php echo strpos($message, 'Success') !== false ? 'Transaction Complete' : 'Transaction Failed'; ?>
            </h2>
            <p><?php echo $message; ?></p>
            
            <h4 style="margin-top: 20px;">Use the following test cards for future attempts:</h4>
            <ul>
                <li>VISA: 1111 2222 3333 4444 (Expiry: 12/26, CVV: 123)</li>
                <li>MASTER: 5555 6666 7777 8888 (Expiry: 08/27, CVV: 901)</li>
            </ul>
            
            <a href="patient_dashboard.php" class="button">Return to Dashboard</a>

        <?php elseif ($cancel_id > 0 && !$confirm_action): ?>
            <!-- VIEW 1: INITIAL FEE WARNING -->
            <h2 class="status-cancelled">Cancellation Now Will Result In A Fee</h2>
            <p>Your appointment (ID: <?php echo $cancel_id; ?>) is within the 24-hour cutoff period. A fee is mandatory to proceed.</p>
            
            <p style="font-size: 1.1em; font-weight: bold;">
                Cancellation Fee Due: <span style="color: #dc3545;">₹<?php echo number_format($fee_amount, 0); ?></span>
            </p>
            
            <p style="margin-top: 30px;">Do you wish to proceed with the payment process?</p>

            <a href="patient_dashboard.php" class="button" style="background-color: #6c757d;">
                &larr; Go Back
            </a>
            <a href="payment_portal.php?appt_id=<?php echo $cancel_id; ?>&payment_form=true" class="button" style="background-color: #dc3545;">
                Proceed to Payment
            </a>

        <?php elseif ($cancel_id > 0 && $confirm_action): ?>
            <!-- VIEW 2: PAYMENT FORM -->
            <h2 class="status-cancelled">Confirm Fee Payment</h2>
            
            <?php if (!empty($message)): ?>
                <p class="status-cancelled" style="font-weight: bold; padding: 10px; border-radius: 5px;"><?php echo $message; ?></p>
            <?php endif; ?>

            <p style="font-size: 1.2em; font-weight: bold; margin-bottom: 25px;">
                Amount: <span style="color: #dc3545;">₹<?php echo number_format($fee_amount, 0); ?></span> (Appt ID: <?php echo $cancel_id; ?>)
            </p>

            <form method="post" action="payment_portal.php?appt_id=<?php echo $cancel_id; ?>&confirm=true">
                <input type="hidden" name="process_payment" value="1">
                
                <h3>Select Payment Method</h3>
                <label style="display: block; margin-bottom: 10px;">
                    <input type="radio" name="payment_method" value="Card" onclick="showPaymentDetails('Card')" checked> Credit/Debit Card
                </label>
                <label style="display: block; margin-bottom: 20px;">
                    <input type="radio" name="payment_method" value="UPI" onclick="showPaymentDetails('UPI')"> UPI / QR Code
                </label>

                <!-- Card Details Section (Default Visible) -->
                <div id="card-details" style="display: block;">
                    <h4>Card Details</h4>
                    <label for="card_number">Card Number (Test: 1111222233334444):</label>
                    <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX"> 

                    <label for="card_expiry">Expiry (MM/YY) / CVV:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" style="width: 50%;">
                        <label for="card_cvv">CVV (Test: 123):</label>
                        <input type="text" id="card_cvv" name="card_cvv" placeholder="CVV" style="width: 50%;">
                    </div>
                    <p style="font-size: 0.9em; color: #777; margin-top: 10px;">
                        Use one of the defined test card numbers (e.g., 1111222233334444, Expiry: 12/26, CVV: 123).
                    </p>
                </div>

                <!-- UPI Details Section (Initially Hidden) -->
                <div id="upi-details" style="display: none;">
                    <h4>UPI Details</h4>
                    <label for="upi_id">UPI ID / VPA:</label>
                    <input type="text" id="upi_id" name="upi_id" placeholder="yourname@bankupi">
                    <p style="font-size: 0.9em; color: #777; margin-top: -15px;">(Simulation: Any input here is accepted)</p>
                </div>

                <button type="submit" style="margin-top: 30px; background-color: #0077b6;">Pay ₹<?php echo number_format($fee_amount, 0); ?></button>
            </form>

        <?php else: ?>
            <!-- Fallback for invalid request -->
            <h2 class="status-cancelled">Request Status</h2>
            <p><?php echo $message; ?></p>
            <a href="patient_dashboard.php" class="button">Return to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>

