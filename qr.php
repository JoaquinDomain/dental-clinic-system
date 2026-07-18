<?php
// QR Code Generator Page
// ===== CONFIGURATION =====

// No more manual URL swapping! This automatically detects localhost or your Vercel domain.
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Automatically points to your main booking page
$website_url = $protocol . "://" . $host . "/"; 

$clinic_phone = '+ (034) 431 3673'; // clinic phone num
$clinic_address = 'MX94+7H6, BBB St, Bacolod, 6100 Negros Occidental'; // clinic address for Google Maps
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - DentalClinicSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 400px;
        }
        h1 { color: #059669; font-size: 1.8rem; margin-bottom: 10px; }
        .subtitle { color: #6B7280; margin-bottom: 30px; }
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .qr-container img { border: 4px solid #10B981; border-radius: 12px; }
        .url-box {
            background: #F3F4F6;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #374151;
            word-break: break-all;
            margin-bottom: 20px;
        }
        .instructions {
            text-align: left;
            background: #ECFDF5;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .instructions h3 { color: #059669; margin-bottom: 10px; font-size: 1rem; }
        .instructions ol { padding-left: 20px; color: #374151; font-size: 0.9rem; line-height: 1.8; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #10B981;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover { background: #059669; }
        .print-btn {
            background: #6B7280;
            margin-left: 10px;
            cursor: pointer;
            border: none;
        }
        .print-btn:hover { background: #4B5563; }
        @media print {
            body * { visibility: hidden; }
            .qr-container, .qr-container * { visibility: visible; }
            .qr-container { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>📱 QR Code Access</h1>
        <p class="subtitle">Scan to book your dental appointment</p>

        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($website_url); ?>" alt="QR Code">
        </div>

        <div class="url-box"><?php echo htmlspecialchars($website_url); ?></div>

        <div class="instructions">
            <h3>How to use:</h3>
            <ol>
                <li>Print this QR code</li>
                <li>Display it at your clinic reception</li>
                <li>Patients scan it with their phone camera</li>
                <li>They'll go directly to the booking page</li>
            </ol>
        </div>

        <a href="index.php" class="btn">← Back to Home</a>
        <button onclick="window.print()" class="btn print-btn">🖨 Print QR</button>
    </div>
</body>
</html>