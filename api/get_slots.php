<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'database_logic.php';

$date = $_GET['date'] ?? '';

// Validate the date format (YYYY-MM-DD) before querying the database
if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $morningSlots = getRemainingSlots($date, 'Morning');
    $afternoonSlots = getRemainingSlots($date, 'Afternoon');
    
    echo json_encode([
        'Morning' => $morningSlots,
        'Afternoon' => $afternoonSlots
    ]);
} else {
    // If no date or invalid format, return 0 slots to prevent front-end errors
    echo json_encode([
        'Morning' => 0, 
        'Afternoon' => 0, 
        'error' => 'Invalid or missing date'
    ]);
}
