# DentalClinicSys

A complete dental clinic appointment booking and management system. Mobile-first, QR-accessible patient portal with a secure admin dashboard.

## Stack
- **Backend:** PHP 8+ (PDO, MySQLi-free)
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** Bootstrap 5, vanilla JS (AJAX)
- **SMS:** Twilio REST API
- **Server:** XAMPP / WAMP / any Apache + PHP host

## Setup (XAMPP)

1. **Copy the project** into `C:\xampp\htdocs\DentalClinicSys`
2. **Start Apache + MySQL** in the XAMPP control panel
3. **Import the database:**
   - Open http://localhost/phpmyadmin
   - Create a database named `dental_clinic`
   - Import `database/schema.sql` then `database/seed.sql`
4. **Configure credentials** in `includes/config.php`:
   - DB host/user/pass
   - Twilio Account SID, Auth Token, From Number
   - Admin contact info (phone, address, Google Maps embed URL)
5. **Open in browser:**
   - Patient portal: http://localhost/DentalClinicSys/
   - Admin panel: http://localhost/DentalClinicSys/admin/
   - Default admin: `admin` / `admin123` (change immediately)

## QR Code Access
Generate a QR code pointing to your public URL using `qr.php` once deployed, or scan the one on the patient landing page.

## Features
See [FEATURES.md](FEATURES.md) for the full 27-feature breakdown.

## Folder layout
```
DentalClinicSys/
├── index.php                 # Patient landing page
├── book.php                  # Booking form + calendar
├── status.php                # Track appointment by reference number
├── qr.php                    # QR code page
├── assets/
│   ├── css/style.css
│   ├── js/app.js
│   └── img/
├── includes/
│   ├── config.php            # DB + Twilio + clinic settings
│   ├── db.php                # PDO connection
│   ├── functions.php         # Helpers (reference number, sanitization, slots)
│   ├── sms.php               # Twilio wrapper
│   ├── auth.php              # Admin session guard
│   ├── header.php            # Public header
│   ├── footer.php            # Public footer
│   └── captcha.php           # Simple math CAPTCHA
├── api/                      # AJAX endpoints
│   ├── slots.php             # Returns remaining slots for a date
│   ├── book.php              # Submits booking
│   └── status.php            # Returns appointment status
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── appointments.php
│   ├── schedule.php
│   ├── patients.php
│   ├── services.php
│   ├── sms.php               # SMS log + send
│   ├── reports.php
│   ├── backup.php
│   ├── includes/
│   │   ├── header.php
│   │   └── sidebar.php
│   └── assets/
│       └── admin.css
├── database/
│   ├── schema.sql
│   └── seed.sql
├── backups/                  # Generated DB backups (gitignored)
└── README.md
```
