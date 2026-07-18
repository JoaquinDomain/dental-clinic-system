# Feature Checklist (27)

## Patient Side
1. Website Access — URL + QR code, no registration required
2. Mobile-First Responsive Design — Bootstrap 5, Android + iOS
3. Calendar-Based Appointment Scheduling — past/full dates disabled
4. Session-Based Booking — Morning (20) / Afternoon (20)
5. Appointment Booking Form — name, mobile, age, sex, address, service, date, session
6. Real-Time Slot Monitoring — AJAX, prevents overbooking
7. Appointment Reference Number — auto-generated, e.g. `DCS-20260606-AB12`
8. Appointment Status Tracking — Pending / Approved / Cancelled / Completed
9. SMS Confirmation — Twilio, includes reference number
10. Google Maps Integration — embed + one-tap navigation
11. Click-to-Call — `tel:` links

## Admin Side
12. Secure Admin Login — password_hash + sessions
13. Dashboard — today/week stats, remaining slots, upcoming appointments
14. Schedule Management — open/close dates, holidays, closures
15. Appointment Management — approve / cancel / reschedule / complete
16. Patient Record Management — details + history
17. Search and Filtering — by name, mobile, date, status
18. SMS Management — send reminders/cancellations + logs

## Services + Reports
19. Service Management — CRUD
20. Daily Reports — totals + morning/afternoon breakdown
21. Monthly Reports — patients served, top services, trends
22. Report Export — PDF + Excel/CSV

## Notifications + Security
23. Automatic SMS — confirmation, approval, reminder, cancellation
24. Input Validation — required, mobile format, data formats
25. Duplicate Booking Prevention — same mobile + same date + session
26. CAPTCHA Protection — simple math captcha
27. Database Backup and Recovery — admin-triggered backup + restore
