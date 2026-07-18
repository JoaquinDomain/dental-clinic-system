-- DentalClinicSys — Database Schema
-- MySQL 5.7+ / MariaDB

CREATE DATABASE IF NOT EXISTS dental_clinic
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dental_clinic;

-- ============================================================
-- Admins
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(100) NOT NULL,
  role          ENUM('superadmin','staff') NOT NULL DEFAULT 'superadmin',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  last_login    DATETIME     NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Services (Feature 19)
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  description TEXT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Schedule settings — per-date overrides (Feature 14)
-- If a date is NOT in this table, default open/slot settings apply.
-- ============================================================
CREATE TABLE IF NOT EXISTS schedule_settings (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  schedule_date   DATE NOT NULL UNIQUE,
  is_closed       TINYINT(1) NOT NULL DEFAULT 0,        -- holiday / closure
  morning_slots   INT NOT NULL DEFAULT 20,              -- override default
  afternoon_slots INT NOT NULL DEFAULT 20,
  note            VARCHAR(255) NULL,                    -- e.g. "Holiday — Independence Day"
  created_at      TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_schedule_date (schedule_date)
) ENGINE=InnoDB;

-- ============================================================
-- Appointments (Features 3–8, 15)
-- ============================================================
CREATE TABLE IF NOT EXISTS appointments (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  reference_no     VARCHAR(30)  NOT NULL UNIQUE,         -- DCS-YYYYMMDD-XXXX
  full_name        VARCHAR(120) NOT NULL,
  mobile_number    VARCHAR(20)  NOT NULL,
  age              INT          NOT NULL,
  sex              ENUM('Male','Female','Other') NOT NULL,
  address          VARCHAR(255) NOT NULL,
  service_id       INT          NULL,
  service_name     VARCHAR(120) NOT NULL,                -- snapshot at booking time
  appointment_date DATE         NOT NULL,
  session_type     ENUM('Morning','Afternoon') NOT NULL,
  status           ENUM('Pending','Approved','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  admin_note       TEXT         NULL,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_appt_date    (appointment_date),
  INDEX idx_appt_status  (status),
  INDEX idx_appt_mobile  (mobile_number),
  INDEX idx_appt_session (appointment_date, session_type),
  CONSTRAINT fk_appt_service FOREIGN KEY (service_id)
    REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SMS log (Features 18, 23)
-- ============================================================
CREATE TABLE IF NOT EXISTS sms_logs (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NULL,
  mobile_number  VARCHAR(20) NOT NULL,
  message        TEXT NOT NULL,
  sms_type       ENUM('Confirmation','Approval','Reminder','Cancellation','Custom') NOT NULL DEFAULT 'Custom',
  status         ENUM('Sent','Failed','Queued') NOT NULL DEFAULT 'Queued',
  provider_sid   VARCHAR(100) NULL,
  error_message  TEXT NULL,
  sent_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sms_appt FOREIGN KEY (appointment_id)
    REFERENCES appointments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- System settings (single-row key/value store)
-- Used for clinic info, default slots, Twilio toggle, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS system_settings (
  setting_key   VARCHAR(60) PRIMARY KEY,
  setting_value TEXT NULL,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Login attempts (light brute-force protection)
-- ============================================================
CREATE TABLE IF NOT EXISTS login_attempts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(50) NOT NULL,
  ip_address  VARCHAR(45) NOT NULL,
  success     TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_ip (ip_address, attempted_at)
) ENGINE=InnoDB;
