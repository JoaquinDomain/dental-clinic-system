<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Include your newly updated PostgreSQL logic file
require_once 'database_logic.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';

// Handle POST actions using our safe PDO architecture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($_POST['action'] === 'approve') {
                if (updateAppointmentStatus($id, 'Approved')) {
                    $message = 'Appointment approved!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to approve appointment.';
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'cancel') {
                if (updateAppointmentStatus($id, 'Cancelled')) {
                    $message = 'Appointment cancelled!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to cancel appointment.';
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'delete') {
                // Delete logic cleanly abstracted to database_logic.php
                if (deleteAppointment($id)) {
                    $message = 'Appointment deleted!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to delete appointment.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Fetch dynamic real-time statistics securely from Supabase via database_logic.php
$counts = getAppointmentCounts();
$totalToday = $counts['today'] ?? 0;
$totalWeek = $counts['week'] ?? 0;

// Pull pending counter safely using the new abstracted function
$totalPending = getPendingCount();

// Pull all records to display down in the table structure safely 
$appointments = getAllAppointments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - DentalClinicSys</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
:root { --primary-green: #10B981; --primary-green-dark: #059669; --text-dark: #1F2937; --text-light: #6B7280; --bg-light: #F9FAFB; --white: #FFFFFF; --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1); --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1); }
body { background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%); min-height: 100vh; padding: 20px; }
.container { max-width: 1200px; margin: 0 auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
.header h1 { color: var(--primary-green-dark); font-size: 2rem; font-weight: 700; }
.header p { color: var(--text-light); font-size: 0.95rem; margin-top: 5px; }
.header-actions { display: flex; gap: 10px; }
.btn { padding: 10px 20px; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-refresh { background: var(--text-dark); color: var(--white); }
.btn-refresh:hover { background: #374151; }
.btn-logout { background: #EF4444; color: var(--white); }
.btn-logout:hover { background: #DC2626; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: var(--white); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-lg); text-align: center; transition: transform 0.3s ease; }
.stat-card:hover { transform: translateY(-5px); }
.stat-card h3 { color: var(--text-light); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
.stat-card .number { color: var(--primary-green-dark); font-size: 2.5rem; font-weight: 700; }
.stat-card .number.pending { color: #F59E0B; }
.stat-card .number.week { color: #8B5CF6; }
.card { background: var(--white); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-xl); overflow-x: auto; }
.card h2 { color: var(--text-dark); font-size: 1.3rem; font-weight: 600; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; min-width: 700px; }
th { background: var(--bg-light); color: var(--text-dark); font-weight: 600; text-align: left; padding: 14px; border-bottom: 2px solid #E5E7EB; font-size: 0.85rem; }
td { padding: 14px; border-bottom: 1px solid #E5E7EB; color: var(--text-dark); font-size: 0.9rem; }
tr:hover { background: #F9FAFB; }
.status { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.status.pending { background: #FEF3C7; color: #92400E; }
.status.approved { background: #D1FAE5; color: #065F46; }
.status.cancelled { background: #FEE2E2; color: #991B1B; }
.status.completed { background: #DBEAFE; color: #1E40AF; }
.btn-sm { padding: 6px 12px; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; }
.btn-approve { background: var(--primary-green); color: var(--white); }
.btn-approve:hover { background: var(--primary-green-dark); }
.btn-cancel { background: #F59E0B; color: var(--white); }
.btn-cancel:hover { background: #D97706; }
.btn-delete { background: #EF4444; color: var(--white); }
.btn-delete:hover { background: #DC2626; }
.toast { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; font-size: 0.9rem; z-index: 1000; animation: slideIn 0.3s ease; }
.toast.success { background: #D1FAE5; color: #065F46; border-left: 4px solid #10B981; }
.toast.error { background: #FEE2E2; color: #991B1B; border-left: 4px solid #EF4444; }
@keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); }
.empty-icon { font-size: 4rem; margin-bottom: 15px; }
@media (max-width: 768px) { .header { flex-direction: column; } .header h1 { font-size: 1.6rem; } .stats-grid { grid-template-columns: 1fr; } th, td { padding: 10px 8px; font-size: 0.8rem; } }
</style>
</head>
<body>
<?php if ($message): ?>
<div class="toast <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<script>setTimeout(() => document.querySelector('.toast')?.remove(), 4000);</script>
<?php endif; ?>

<div class="container">
<div class="header">
<div>
<h1>Admin Dashboard</h1>
<p>Manage appointments</p>
</div>
<div class="header-actions">
<button class="btn btn-refresh" onclick="location.reload()">🔄 Refresh</button>
<a href="logout.php" class="btn btn-logout">🚪 Logout</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card">
<h3>Today's Appointments</h3>
<div class="number"><?php echo $totalToday; ?></div>
</div>
<div class="stat-card">
<h3>This Week</h3>
<div class="number week"><?php echo $totalWeek; ?></div>
</div>
<div class="stat-card">
<h3>Pending</h3>
<div class="number pending"><?php echo $totalPending; ?></div>
</div>
</div>

<div class="card">
<h2>All Appointments</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>Patient Name</th>
<th>Mobile</th>
<th>Age</th>
<th>Sex</th>
<th>Date</th>
<th>Session</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if (count($appointments) > 0): ?>
<?php foreach ($appointments as $row): ?>
<tr>
<td><?php echo htmlspecialchars($row['id']); ?></td>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
<td><?php echo htmlspecialchars($row['age']); ?></td>
<td><?php echo htmlspecialchars($row['sex']); ?></td>
<td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
<td><?php echo htmlspecialchars($row['session_type']); ?></td>
<td><span class="status <?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
<td>
<?php if ($row['status'] === 'Pending'): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
<button type="submit" name="action" value="approve" class="btn-sm btn-approve">✓</button>
<button type="submit" name="action" value="cancel" class="btn-sm btn-cancel">✗</button>
</form>
<?php endif; ?>
<?php if ($row['status'] !== 'Pending'): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
<button type="submit" name="action" value="delete" class="btn-sm btn-delete">🗑</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📋</div><p>No appointments found</p></div></td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
