<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

$error = '';

// Pull credentials securely from environment variables, fallback to defaults if not set locally
$admin_username = getenv('ADMIN_USERNAME') ?: 'admin';
$admin_password = getenv('ADMIN_PASSWORD') ?: 'admin123'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && $password === $admin_password) {
        // Prevent session fixation attacks by regenerating the ID upon successful login
        session_regenerate_id(true);
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - DentalClinicSys</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
.login-container { background: #FFFFFF; border-radius: 20px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); width: 100%; max-width: 400px; }
.login-header { text-align: center; margin-bottom: 30px; }
.login-header h1 { color: #059669; font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
.login-header p { color: #6B7280; font-size: 0.9rem; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #1F2937; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; }
.form-group input { width: 100%; padding: 14px 16px; border: 2px solid #E5E7EB; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; }
.form-group input:focus { outline: none; border-color: #10B981; }
.btn-login { width: 100%; padding: 14px; background: #10B981; color: #FFFFFF; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s; }
.btn-login:hover { background: #059669; }
.error-message { background: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border-left: 4px solid #EF4444; }
.back-link { text-align: center; margin-top: 20px; }
.back-link a { color: #10B981; text-decoration: none; font-weight: 500; }
.back-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="login-container">
<div class="login-header">
<h1>🔐 Admin Login</h1>
<p>Access the dental clinic admin panel</p>
</div>

<?php if ($error): ?>
<div class="error-message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
<div class="form-group">
<label for="username">Username</label>
<input type="text" id="username" name="username" required autocomplete="username">
</div>
<div class="form-group">
<label for="password">Password</label>
<input type="password" id="password" name="password" required autocomplete="current-password">
</div>
<button type="submit" class="btn-login">Login</button>
</form>

<div class="back-link">
<a href="index.php">← Back to Home</a>
</div>
</div>
</body>
</html>