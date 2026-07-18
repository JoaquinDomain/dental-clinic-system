<?php
session_start();
$message = '';
$messageType = '';
$referenceNo = null;

require_once 'database_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $service = $_POST['service'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $session = $_POST['session'] ?? '';

    if ($name && $mobile && $age && $sex && $address && $service && $appointment_date && $session) {
        if (!checkSlotAvailability($appointment_date, $session)) {
            $message = 'No available slots for this session. Please choose another date or session.';
            $messageType = 'error';
        } else {
            $appointmentData = [
                'full_name'        => $name,
                'mobile_number'    => $mobile,
                'age'              => $age,
                'sex'              => $sex,
                'address'          => $address,
                'service_id'       => $service,
                'appointment_date' => $appointment_date,
                'session_type'     => $session
            ];

            $result = insertAppointment($appointmentData);
            if (isset($result['success']) && $result['success']) {
                $referenceNo = $result['reference_no'] ?? null;
                $message = 'Appointment booked successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to book appointment. Please try again.';
                $messageType = 'error';
            }
        }
    } else {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    }
}

// Fallback logic for calculating real-time initial layout data slots on page load
$selectedDate = $_POST['appointment_date'] ?? date('Y-m-d');
$availableSlots = ['Morning' => 0, 'Afternoon' => 0];
if ($selectedDate) {
    $availableSlots['Morning'] = getRemainingSlots($selectedDate, 'Morning');
    $availableSlots['Afternoon'] = getRemainingSlots($selectedDate, 'Afternoon');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentalClinicSys - Book Your Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root { --primary-green: #10B981; --primary-green-dark: #059669; --primary-green-light: #34D399; --text-dark: #1F2937; --text-light: #6B7280; --bg-light: #F9FAFB; --white: #FFFFFF; --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05); --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1); --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1); --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        body { background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%); min-height: 100vh; padding: 20px 15px; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: var(--primary-green-dark); font-size: 2rem; font-weight: 700; letter-spacing: -0.5px; }
        .header p { color: var(--text-light); font-size: 0.95rem; margin-top: 5px; }
        .card { background: var(--white); border-radius: 20px; padding: 30px 25px; box-shadow: var(--shadow-xl); margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: var(--text-dark); font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 2px solid #E5E7EB; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: var(--bg-light); font-family: 'Poppins', sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary-green); background: var(--white); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
        .form-group input::placeholder { color: #9CA3AF; }
        .calendar-section { margin-bottom: 20px; }
        .calendar-section h3 { color: var(--text-dark); font-size: 1rem; font-weight: 600; margin-bottom: 15px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; margin-bottom: 20px; }
        .calendar-header { text-align: center; font-weight: 600; color: var(--text-light); font-size: 0.8rem; padding: 8px 0; }
        .calendar-day { aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; background: var(--bg-light); border: 2px solid transparent; color: var(--text-dark); padding: 2px; gap: 0; }
        .calendar-day > span:first-child { font-size: 0.85rem; line-height: 1; }
        .calendar-day .slot-info { font-size: 0.55rem; font-weight: 600; line-height: 1.2; }
        .calendar-day .slot-info.available { color: var(--primary-green); }
        .calendar-day .slot-info.full { color: #EF4444; }
        .calendar-day .slot-info.none { color: #9CA3AF; }
        .calendar-day:hover:not(.disabled):not(.selected) { border-color: var(--primary-green-light); background: #ECFDF5; }
        .calendar-day.selected { background: var(--primary-green); color: var(--white); border-color: var(--primary-green); font-weight: 600; }
        .calendar-day.disabled { color: #D1D5DB; cursor: not-allowed; background: #F3F4F6; }
        .calendar-day.today { border: 2px solid var(--primary-green); }
        .month-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .month-nav button { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); padding: 8px 16px; border-radius: 8px; transition: all 0.2s ease; }
        .month-nav button:hover { background: var(--bg-light); }
        .month-nav span { font-weight: 600; color: var(--text-dark); font-size: 1.1rem; }
        .session-options { display: flex; gap: 12px; }
        .session-option { flex: 1; position: relative; }
        .session-option input { position: absolute; opacity: 0; pointer-events: none; }
        .session-option label { display: block; padding: 16px; text-align: center; background: var(--bg-light); border: 2px solid #E5E7EB; border-radius: 12px; font-weight: 600; color: var(--text-light); cursor: pointer; transition: all 0.3s ease; }
        .session-option label .slots { display: block; font-size: 0.75rem; font-weight: 400; margin-top: 4px; }
        .session-option input:checked + label { background: var(--primary-green); border-color: var(--primary-green); color: var(--white); box-shadow: var(--shadow-md); }
        .session-option input:checked + label .slots { color: #D1FAE5; }
        .session-option label:hover { border-color: var(--primary-green-light); }
        .btn-submit { width: 100%; padding: 18px; background: var(--primary-green); color: var(--white); border: none; border-radius: 14px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: var(--shadow-md); }
        .btn-submit:hover { background: var(--primary-green-dark); transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-submit:active { transform: translateY(0); }
        .message { padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; animation: slideIn 0.3s ease; }
        .message.success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .message.error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
        .reference-box { background: #ECFDF5; border: 2px solid var(--primary-green); border-radius: 12px; padding: 20px; text-align: center; margin-top: 15px; }
        .reference-box h4 { color: var(--primary-green-dark); font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; }
        .reference-number { background: var(--white); color: var(--text-dark); font-size: 1.4rem; font-weight: 700; padding: 12px 20px; border-radius: 8px; display: inline-block; letter-spacing: 1px; font-family: monospace; }
        .reference-note { color: var(--text-light); font-size: 0.8rem; margin-top: 10px; }
        .reference-box .btn-check-status { display: inline-block; margin-top: 12px; padding: 10px 20px; background: var(--primary-green); color: var(--white); text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 500; }
        .reference-box .btn-check-status:hover { background: var(--primary-green-dark); }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .action-buttons { display: flex; gap: 12px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
        .btn-action { flex: 1; min-width: 100px; max-width: 180px; padding: 14px 16px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; text-decoration: none; text-align: center; cursor: pointer; transition: all 0.3s ease; border: none; }
        .btn-call { background: var(--primary-green); color: var(--white); box-shadow: var(--shadow-md); }
        .btn-call:hover { background: var(--primary-green-dark); }
        .btn-location { background: var(--white); color: var(--primary-green); border: 2px solid var(--primary-green); }
        .btn-location:hover { background: #ECFDF5; }
        .btn-qr { background: #8B5CF6; color: var(--white); }
        .btn-qr:hover { background: #7C3AED; }
        @media (max-width: 480px) { .header h1 { font-size: 1.6rem; } .card { padding: 25px 20px; } .session-options { flex-direction: column; } .calendar-grid { gap: 4px; } .calendar-day { font-size: 0.8rem; } .action-buttons { flex-direction: column; align-items: center; } .btn-action { max-width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🦷 DentalClinicSys</h1>
            <p style="margin-top:10px;"><a href="status.php" style="color:#10B981;text-decoration:none;font-weight:500;">📋 Check Appointment Status</a></p>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php if ($referenceNo): ?>
                <div class="reference-box">
                    <h4>📋 Your Reference Number</h4>
                    <div class="reference-number"><?php echo htmlspecialchars($referenceNo); ?></div>
                    <p class="reference-note">Save this reference number to check your appointment status</p>
                    <a href="status.php" class="btn-check-status">Check Status Now</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" action="" id="bookingForm">
                <div class="form-group">
                    <label for="name">Patient Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" required>
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" placeholder="Enter your age" required min="1" max="150">
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address" required>
                </div>

                <div class="form-group">
                    <label for="service">Service</label>
                    <select id="service" name="service" required>
                        <option value="">Select service</option>
                        <option value="1">Dental Checkup</option>
                        <option value="2">Dental Cleaning</option>
                        <option value="3">Tooth Extraction</option>
                        <option value="4">Tooth Filling</option>
                        <option value="5">Root Canal Treatment</option>
                        <option value="6">Dentures</option>
                        <option value="7">Orthodontic Consultation</option>
                    </select>
                </div>

                <div class="calendar-section">
                    <h3>Select Appointment Date</h3>
                    <div class="month-nav">
                        <button type="button" id="prevMonth">❮</button>
                        <span id="currentMonth"></span>
                        <button type="button" id="nextMonth">❯</button>
                    </div>
                    <div class="calendar-grid" id="calendarGrid"></div>
                    <input type="hidden" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                </div>

                <div class="form-group">
                    <label>Session</label>
                    <div class="session-options">
                        <div class="session-option">
                            <input type="radio" id="morning" name="session" value="Morning" required>
                            <label for="morning">
                                ☀️ Morning
                                <span class="slots"><?php echo $availableSlots['Morning']; ?> slots left</span>
                            </label>
                        </div>
                        <div class="session-option">
                            <input type="radio" id="afternoon" name="session" value="Afternoon">
                            <label for="afternoon">
                                🌤️ Afternoon
                                <span class="slots"><?php echo $availableSlots['Afternoon']; ?> slots left</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="book_appointment" class="btn-submit">Book Appointment</button>
            </form>

            <div class="action-buttons">
                <a href="tel:+1234567890" class="btn-action btn-call">📞 Call</a>
                <a href="https://www.google.com/maps/search/?api=1&query=Dental+Clinic" target="_blank" class="btn-action btn-location">📍 Location</a>
                <a href="qr.php" class="btn-action btn-qr">📱 QR Code</a>
            </div>
        </div>
    </div>

    <script>
        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        let selectedDate = new Date('<?php echo $selectedDate; ?>');

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        let slotDataCache = {};

        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            const monthLabel = document.getElementById('currentMonth');
            grid.innerHTML = '';

            dayNames.forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-header';
                header.textContent = day;
                grid.appendChild(header);
            });

            monthLabel.textContent = monthNames[currentMonth] + ' ' + currentYear;

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'calendar-day disabled';
                grid.appendChild(empty);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateEl = document.createElement('div');
                dateEl.className = 'calendar-day';

                const daySpan = document.createElement('span');
                daySpan.textContent = day;
                dateEl.appendChild(daySpan);

                const date = new Date(currentYear, currentMonth, day);
                const dateStr = date.toISOString().split('T')[0];

                if (date < todayDate) {
                    dateEl.classList.add('disabled');
                } else if (dateStr === selectedDate.toISOString().split('T')[0]) {
                    dateEl.classList.add('selected');
                } else if (date.toDateString() === today.toDateString()) {
                    dateEl.classList.add('today');
                }

                if (slotDataCache[dateStr] && date >= todayDate) {
                    addSlotInfo(dateEl, dateStr);
                }

                dateEl.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (!dateEl.classList.contains('disabled')) {
                        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                        dateEl.classList.add('selected');
                        document.getElementById('appointment_date').value = dateStr;
                        selectedDate = date;
                        fetchSlots(dateStr);
                    }
                });

                grid.appendChild(dateEl);
            }

            fetchMonthSlots();
        }

        function addSlotInfo(dateEl, dateStr) {
            const slotInfo = document.createElement('span');
            slotInfo.className = 'slot-info';

            if (slotDataCache[dateStr]) {
                const totalSlots = slotDataCache[dateStr].Morning + slotDataCache[dateStr].Afternoon;
                if (totalSlots > 0) {
                    slotInfo.textContent = totalSlots + ' avail';
                    slotInfo.classList.add('available');
                } else {
                    slotInfo.textContent = 'FULL';
                    slotInfo.classList.add('full');
                }
            } else {
                slotInfo.textContent = '...';
                slotInfo.classList.add('none');
            }

            dateEl.appendChild(slotInfo);
        }

        function fetchMonthSlots() {
            const firstDay = new Date(currentYear, currentMonth, 1);
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = date.toISOString().split('T')[0];
                const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());

                if (date >= todayDate) {
                    fetch('get_slots.php?date=' + dateStr)
                        .then(response => response.json())
                        .then(data => {
                            if (data.Morning !== undefined) {
                                slotDataCache[dateStr] = { Morning: data.Morning, Afternoon: data.Afternoon };
                                updateCalendarSlotInfo(dateStr);
                            }
                        })
                        .catch(error => console.error('Error fetching slots:', error));
                }
            }
        }

        function updateCalendarSlotInfo(dateStr) {
            const dayEls = document.querySelectorAll('.calendar-day');
            dayEls.forEach(el => {
                const daySpan = el.querySelector('span:first-child');
                if (daySpan) {
                    const dayNum = parseInt(daySpan.textContent);
                    const date = new Date(currentYear, currentMonth, dayNum);
                    const elDateStr = date.toISOString().split('T')[0];

                    if (elDateStr === dateStr) {
                        const existingSlotInfo = el.querySelector('.slot-info');
                        if (existingSlotInfo) existingSlotInfo.remove();
                        addSlotInfo(el, dateStr);
                    }
                }
            });
        }

        document.getElementById('prevMonth').addEventListener('click', (e) => {
            e.preventDefault();
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', (e) => {
            e.preventDefault();
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        });

        function fetchSlots(dateStr) {
            fetch('get_slots.php?date=' + dateStr)
                .then(response => response.json())
                .then(data => {
                    if (data.Morning !== undefined) {
                        document.querySelector('label[for="morning"] .slots').textContent = data.Morning + ' slots left';
                        document.querySelector('label[for="afternoon"] .slots').textContent = data.Afternoon + ' slots left';
                        slotDataCache[dateStr] = { Morning: data.Morning, Afternoon: data.Afternoon };
                        updateCalendarSlotInfo(dateStr);
                    }
                })
                .catch(error => console.error('Error fetching slots:', error));
        }

        const initialDate = '<?php echo $selectedDate; ?>';
        if (initialDate) {
            fetch('get_slots.php?date=' + initialDate)
                .then(response => response.json())
                .then(data => {
                    if (data.Morning !== undefined) {
                        slotDataCache[initialDate] = { Morning: data.Morning, Afternoon: data.Afternoon };
                        updateCalendarSlotInfo(initialDate);
                    }
                })
                .catch(error => console.error('Error fetching initial slots:', error));
        }

        renderCalendar();

        // --- NEW CLIENT-SIDE VALIDATION ADDED HERE ---
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const mobileInput = document.getElementById('mobile').value.trim();
            const ageInput = document.getElementById('age').value.trim();
            const dateInput = document.getElementById('appointment_date').value;
            
            // Validate Philippine Mobile Number (11 digits starting with 09)
            const mobileRegex = /^09\d{9}$/;
            if (!mobileRegex.test(mobileInput)) {
                e.preventDefault(); 
                alert('Please enter a valid 11-digit mobile number starting with 09 (e.g., 09123456789).');
                document.getElementById('mobile').focus();
                return;
            }

            // Validate Age
            if (ageInput < 1 || ageInput > 120) {
                e.preventDefault();
                alert('Please enter a valid age.');
                document.getElementById('age').focus();
                return;
            }

            // Validate Date Selection
            if (!dateInput) {
                e.preventDefault();
                alert('Please select an appointment date from the calendar.');
                return;
            }
        });
    </script>
</body>
</html>
