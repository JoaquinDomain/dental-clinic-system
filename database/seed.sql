-- DentalClinicSys — Seed Data
USE dental_clinic;

-- Default admin (username: admin, password: admin123) — CHANGE IMMEDIATELY
INSERT INTO admins (username, password_hash, full_name, role)
VALUES (
  'admin',
  -- bcrypt hash of "admin123"
  '$2y$10$wHc1FZQ8M9pZJX2eU0pV5e9rL6kP3qHnH3J2yQ7nQ5K0H8d3xKkZ6',
  'System Administrator',
  'superadmin'
);

-- Default services (Feature 19 examples)
INSERT INTO services (name, description) VALUES
  ('Dental Checkup',          'General oral examination and consultation.'),
  ('Dental Cleaning',         'Professional teeth cleaning and polishing.'),
  ('Tooth Extraction',        'Removal of damaged or problem teeth.'),
  ('Tooth Filling',           'Restoration of cavities using composite filling.'),
  ('Root Canal Treatment',    'Treatment for infected or damaged tooth pulp.'),
  ('Dentures',                'Custom-fitted removable dental prosthetics.'),
  ('Orthodontic Consultation','Initial assessment for braces and alignment.');

-- System settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
  ('clinic_name',       'Bright Smile Dental Clinic'),
  ('clinic_address',    '123 Main Street, Your City'),
  ('clinic_phone',      '+1234567890'),
  ('clinic_email',      'info@brightsmile.example'),
  ('clinic_hours',      'Mon–Sat: 8:00 AM – 5:00 PM'),
  ('default_morning_slots',   '20'),
  ('default_afternoon_slots', '20'),
  ('google_maps_embed', 'https://maps.google.com/maps?q=dental+clinic&t=&z=15&ie=UTF8&iwloc=&output=embed'),
  ('google_maps_link',  'https://maps.google.com/?q=dental+clinic'),
  ('sms_enabled',       '1'),
  ('booking_open_days_ahead', '30');
