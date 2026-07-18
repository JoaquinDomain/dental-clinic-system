<?php
// Appointment Status Tracking Page
session_start();
$message = '';
$messageType = '';
$appointment = null;

require_once 'database_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_status'])) {
    $reference = trim($_POST['reference'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    if ($reference && $mobile) {
        $conn = getDbConnection();
        if ($conn) {
            try {
                // PDO Prepared Statement
                $stmt = $conn->prepare("SELECT * FROM appointments WHERE reference_no = ? AND mobile_number = ?");
                $stmt->execute([$reference, $mobile]);
                
                if ($stmt->rowCount() > 0) {
                    $appointment = $stmt->fetch();
                    $message = 'Appointment found!';
                    $messageType = 'success';
                } else {
                    $message = 'No appointment found with this reference number and mobile number.';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                error_log("Status check failed: " . $e->getMessage());
                $message = 'An error occurred while fetching your appointment. Please try again.';
                $messageType = 'error';
            }
            // PDO connections close automatically, so no $conn->close() is needed.
        } else {
            $message = 'Database connection error. Please try again.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please enter both reference number and mobile number.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Appointment Status - DentalClinicSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root {
            --primary-green: #10B981;
            --primary-green-dark: #059669;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            --bg-light: #F9FAFB;
            --white: #FFFFFF;
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
        }
        body {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: var(--primary-green-dark);
            font-size: 1.8rem;
            font-weight: 700;
        }
        .header p {
            color: var(--text-light);
            margin-top: 5px;
        }
        .card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-xl);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-green);
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary-green);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: var(--primary-green-dark);
        }
        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: #D1FAE5;
            color: #065F46;
        }
        .message.error {
            background: #FEE2E2;
            color: #991B1B;
        }
        .appointment-details {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status.pending { background: #FEF3C7; color: #92400E; }
        .status.approved { background: #D1FAE5; color: #065F46; }
        .status.cancelled { background: #FEE2E2; color: #991B1B; }
        .status.completed { background: #DBEAFE; color: #1E40AF; }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Check Appointment Status</h1>
            <p>Enter your reference number and mobile number</p>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($appointment): ?>
                <div class="appointment-details">
                    <div class="detail-row">
                        <span class="detail-label">Reference No.</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['reference_no']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Patient Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['full_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Service</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['appointment_date']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Session</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['session_type']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="status <?php echo strtolower($appointment['status']); ?>">
                            <?php echo htmlspecialchars($appointment['status']); ?>
                        </span>
                    </div>
                    <?php if (isset($appointment['admin_note']) && $appointment['admin_note']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Note</span>
                        <span class="detail-value"><?php echo htmlspecialchars($appointment['admin_note']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <br>
                <a href="status.php" class="btn-submit" style="text-decoration:none; text-align:center; display:block;">Check Another</a>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="reference">Reference Number</label>
                        <input type="text" id="reference" name="reference" placeholder="e.g. DCS-20260607-AB12" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" required>
                    </div>
                    <button type="submit" name="check_status" class="btn-submit">Check Status</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="back-link">
            <a href="index.php">← Back to Booking</a>
        </div>
    </div>
</body>
</html>
