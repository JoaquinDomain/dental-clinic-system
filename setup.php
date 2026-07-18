<?php
// Database Setup Script - Run this once to create the database and table

$servername = "localhost";
$username = "root";
$password = "";
$message = "";
$success = true;

try {
    // Connect without database first to create it
    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS dental_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        $message .= "✓ Database 'dental_clinic' created<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db("dental_clinic");

    // Create services table
    $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        description TEXT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $conn->query($sql);

    // Create appointments table
    $sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference_no VARCHAR(30) NOT NULL UNIQUE,
        full_name VARCHAR(120) NOT NULL,
        mobile_number VARCHAR(20) NOT NULL,
        age INT NOT NULL,
        sex ENUM('Male','Female','Other') NOT NULL,
        address VARCHAR(255) NOT NULL,
        service_id INT NULL,
        service_name VARCHAR(120) NOT NULL,
        appointment_date DATE NOT NULL,
        session_type ENUM('Morning','Afternoon') NOT NULL,
        status ENUM('Pending','Approved','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
        admin_note TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_appt_date (appointment_date),
        INDEX idx_appt_status (status),
        INDEX idx_appt_session (appointment_date, session_type)
    ) ENGINE=InnoDB";
    $conn->query($sql);

    $message .= "✓ Table 'appointments' created<br>";

    // Insert default services if not exists
    $result = $conn->query("SELECT COUNT(*) as cnt FROM services");
    $row = $result->fetch_assoc();
    if ($row['cnt'] == 0) {
        $conn->query("INSERT INTO services (name, description) VALUES
            ('Dental Checkup', 'General oral examination and consultation.'),
            ('Dental Cleaning', 'Professional teeth cleaning and polishing.'),
            ('Tooth Extraction', 'Removal of damaged or problem teeth.'),
            ('Tooth Filling', 'Restoration of cavities using composite filling.'),
            ('Root Canal Treatment', 'Treatment for infected or damaged tooth pulp.'),
            ('Dentures', 'Custom-fitted removable dental prosthetics.'),
            ('Orthodontic Consultation', 'Initial assessment for braces and alignment.')");
        $message .= "✓ Default services inserted<br>";
    }

    $message .= "<br><strong>Setup complete! You can now use the application.</strong>";
    $conn->close();
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - DentalClinicSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 { color: #059669; margin-bottom: 20px; }
        .message { color: #6B7280; margin-bottom: 10px; line-height: 1.6; }
        .success-msg { color: #10B981; font-weight: 600; }
        .error-msg { color: #EF4444; font-weight: 600; }
        a {
            display: inline-block;
            margin: 10px 5px;
            padding: 12px 24px;
            background: #10B981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
        a:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Database Setup</h1>
        <p class="message <?php echo $success ? 'success-msg' : 'error-msg'; ?>">
            <?php echo $message; ?>
        </p>
        <?php if ($success): ?>
            <br>
            <a href="index.php">Go to Booking Form</a>
            <a href="admin.php">Go to Admin Panel</a>
        <?php endif; ?>
    </div>
</body>
</html>