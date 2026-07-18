<?php

// Connection strings pull securely from environment variables on Vercel
define('DB_HOST', getenv('DB_HOST') ?: 'your-supabase-host-here');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'postgres');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'your-supabase-password-here');
define('MAX_SLOTS_PER_SESSION', 20);

function getDbConnection() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function countAppointmentsByDateAndSession($appointmentDate, $session) {
    $pdo = getDbConnection();
    if (!$pdo) {
        return 0;
    }
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND session_type = ?");
        $stmt->execute([$appointmentDate, $session]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error in countAppointments: " . $e->getMessage());
        return 0;
    }
}

function checkSlotAvailability($appointmentDate, $session) {
    $currentCount = countAppointmentsByDateAndSession($appointmentDate, $session);
    return $currentCount < MAX_SLOTS_PER_SESSION;
}

function getRemainingSlots($appointmentDate, $session) {
    $currentCount = countAppointmentsByDateAndSession($appointmentDate, $session);
    return max(0, MAX_SLOTS_PER_SESSION - $currentCount);
}

function generateReferenceNumber() {
    $datePart = date('Ymd');
    $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    return 'DCS-' . $datePart . '-' . $randomPart;
}

function referenceNumberExists($referenceNo) {
    $pdo = getDbConnection();
    if (!$pdo) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE reference_no = ?");
        $stmt->execute([$referenceNo]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking reference number: " . $e->getMessage());
        return false;
    }
}

function generateUniqueReferenceNumber() {
    do {
        $refNo = generateReferenceNumber();
    } while (referenceNumberExists($refNo));
    return $refNo;
}

function insertAppointment($data) {
    $pdo = getDbConnection();
    if (!$pdo) {
        error_log("Failed to connect to database in insertAppointment");
        return ['success' => false, 'reference_no' => null];
    }

    $reference_no = 'DCS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    $status = 'Pending';

    $service_id = intval($data['service_id']);
    $service_name = '';

    try {
        // Get service name from service_id
        $stmtService = $pdo->prepare("SELECT name FROM services WHERE id = ?");
        $stmtService->execute([$service_id]);
        $rowService = $stmtService->fetch();
        if ($rowService) {
            $service_name = $rowService['name'];
        }

        // If no service found, use default
        if (empty($service_name)) {
            $service_name = 'Dental Checkup';
            $service_id = 1;
        }

        $query = "INSERT INTO appointments (reference_no, full_name, mobile_number, age, sex, address, service_id, service_name, appointment_date, session_type, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            $reference_no,
            $data['full_name'],
            $data['mobile_number'],
            $data['age'],
            $data['sex'],
            $data['address'],
            $service_id,
            $service_name,
            $data['appointment_date'],
            $data['session_type'],
            $status
        ]);

        if ($result) {
            return ['success' => true, 'reference_no' => $reference_no];
        }
    } catch (Exception $e) {
        error_log("Insert appointment failed: " . $e->getMessage());
    }

    return ['success' => false, 'reference_no' => null];
}

function updateAppointmentStatus($appointmentId, $status) {
    $pdo = getDbConnection();
    if (!$pdo) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $appointmentId]);
    } catch (Exception $e) {
        error_log("Update status failed: " . $e->getMessage());
        return false;
    }
}

function getAllAppointments($status = null) {
    $pdo = getDbConnection();
    if (!$pdo) {
        return [];
    }

    try {
        if ($status) {
            $stmt = $pdo->prepare("SELECT * FROM appointments WHERE status = ? ORDER BY appointment_date DESC");
            $stmt->execute([$status]);
            return $stmt->fetchAll();
        } else {
            $stmt = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC");
            return $stmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log("Get all appointments failed: " . $e->getMessage());
        return [];
    }
}

function getAppointmentCounts() {
    $pdo = getDbConnection();
    if (!$pdo) {
        return ['today' => 0, 'week' => 0, 'total' => 0];
    }

    $today = date('Y-m-d');
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    $counts = ['today' => 0, 'week' => 0, 'total' => 0];

    try {
        $stmtToday = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?");
        $stmtToday->execute([$today]);
        $rowToday = $stmtToday->fetch();
        $counts['today'] = $rowToday['count'] ?? 0;

        $stmtWeek = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date BETWEEN ? AND ?");
        $stmtWeek->execute([$startOfWeek, $endOfWeek]);
        $rowWeek = $stmtWeek->fetch();
        $counts['week'] = $rowWeek['count'] ?? 0;

        $stmtTotal = $pdo->query("SELECT COUNT(*) as count FROM appointments");
        $rowTotal = $stmtTotal->fetch();
        $counts['total'] = $rowTotal['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Get appointment counts failed: " . $e->getMessage());
    }

    return $counts;
}

// --- NEW FUNCTIONS ADDED BELOW ---

function deleteAppointment($id) {
    $pdo = getDbConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Delete failed: " . $e->getMessage());
        return false;
    }
}

function getPendingCount() {
    $pdo = getDbConnection();
    if (!$pdo) return 0;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Failed to fetch pending counts: " . $e->getMessage());
        return 0;
    }
}

?>
