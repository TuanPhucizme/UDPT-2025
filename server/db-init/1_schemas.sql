--
-- File: database_setup.sql
-- Description: SQL script to set up all microservices databases and tables.
--

-- =========================================================
-- AUTH SERVICE DATABASE
-- Manages user authentication, roles, and staff information.
-- =========================================================
CREATE DATABASE IF NOT EXISTS auth_service;
USE auth_service;

-- Role Table: Stores different user roles (e.g., doctor, pharmacist, admin)
CREATE TABLE role (
  id_role INT AUTO_INCREMENT PRIMARY KEY,
  ten_role VARCHAR(100) UNIQUE NOT NULL
);

-- Users Table: Stores user login credentials and links to roles
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  FOREIGN KEY (role_id) REFERENCES role(id_role)
);

-- Department Table: Stores information about hospital departments/specializations
CREATE TABLE department (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ten_ck VARCHAR(255) UNIQUE NOT NULL
);

-- Staff Table: Stores detailed information about staff members
CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_code INT NOT NULL UNIQUE,
  hoten_nv VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  sdt VARCHAR(50) NOT NULL UNIQUE,
  gender ENUM('nam','nu','khac') NOT NULL,
  dob DATE,
  role_id INT NOT NULL,
  department_id INT, -- Can be NULL for roles like pharmacists or receptionists
  begin_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES role(id_role),
  FOREIGN KEY (department_id) REFERENCES department(id)
);

-- =========================================================
-- PATIENT SERVICE DATABASE
-- Manages patient information and their medical records.
-- =========================================================
CREATE DATABASE IF NOT EXISTS patient_service;
USE patient_service;

-- Patients Table: Stores general patient demographic information
CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hoten_bn VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  gender ENUM('nam', 'nu', 'khac') NOT NULL,
  sdt VARCHAR(15) UNIQUE,
  diachi VARCHAR(255),
  tiensu_benh TEXT, -- Medical history
  lichsu_kham TEXT, -- Visit history
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Medical Records Table: Stores details of each patient visit/consultation
CREATE TABLE medical_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL, -- Links to staff in auth_service
  department_id INT, -- Links to department in auth_service
  ngaykham DATETIME NOT NULL,
  lydo VARCHAR(255), -- Reason for visit
  chan_doan TEXT, -- Diagnosis
  ngay_taikham DATE, -- Re-examination date
  ghichu TEXT, -- Notes
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES auth_service.staff(id),
  FOREIGN KEY (department_id) REFERENCES auth_service.department(id)
);

-- =========================================================
-- APPOINTMENT SERVICE DATABASE
-- Manages patient appointments with doctors and departments.
-- =========================================================
CREATE DATABASE IF NOT EXISTS appointment_service;
USE appointment_service;

-- Appointments Table: Stores details of scheduled appointments
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  department_id INT NOT NULL, -- Links to department in auth_service
  doctor_id INT NOT NULL, -- Links to staff in auth_service
  receptionist_id INT NOT NULL, -- Links to staff in auth_service
  thoi_gian_hen DATETIME NOT NULL, -- Appointment time
  lydo VARCHAR(255), -- Reason for appointment
  status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patient_service.patients(id),
  FOREIGN KEY (department_id) REFERENCES auth_service.department(id),
  FOREIGN KEY (doctor_id) REFERENCES auth_service.staff(id),
  FOREIGN KEY (receptionist_id) REFERENCES auth_service.staff(id)
);

-- =========================================================
-- PRESCRIPTION SERVICE DATABASE
-- Manages medicine inventory and patient prescriptions.
-- =========================================================
CREATE DATABASE IF NOT EXISTS prescription_service;
USE prescription_service;

-- Medicines Table: Stores information about available medicines
CREATE TABLE medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ten_thuoc VARCHAR(255) NOT NULL UNIQUE,
  so_luong INT, -- Current stock quantity
  don_vi VARCHAR(50) NOT NULL, -- Unit of measure (e.g., viên, túi, chai)
  don_gia DECIMAL(10, 2) DEFAULT 0.00, -- Unit price
  is_liquid BOOLEAN DEFAULT FALSE, -- Indicates if the medicine is liquid
  volume_per_bottle FLOAT NULL, -- Volume per bottle if liquid
  volume_unit VARCHAR(10) NULL, -- Unit for liquid volume (e.g., ml)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Prescriptions Table: Stores overall prescription information
CREATE TABLE prescriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  record_id INT NOT NULL, -- Links to medical_records in patient_service
  pharmacist_id INT NULL, -- Links to staff in auth_service (can be NULL if not yet dispensed)
  status ENUM('pending','collected','dispensed') DEFAULT 'pending', -- Renamed 'collected' to 'dispensed' for clarity
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (record_id) REFERENCES patient_service.medical_records(id)
  -- FOREIGN KEY (pharmacist_id) REFERENCES auth_service.staff(id) -- Added later for clarity, can be omitted if not critical for initial creation
);

-- Prescription Medicines Table: Links prescriptions to specific medicines with dosage details
CREATE TABLE prescription_medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prescription_id INT NOT NULL,
  medicine_id INT NOT NULL,
  dose VARCHAR(255), -- Dosage (e.g., "500mg", "2 nhát", "10ml")
  frequency VARCHAR(255), -- How often (e.g., "2 lần/ngày", "Khi khó thở")
  duration VARCHAR(255), -- How long (e.g., "5 ngày", "1 tháng")
  note TEXT,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
  FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

-- Medicine Stock Log Table: Tracks all changes in medicine stock
CREATE TABLE medicine_stock_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medicine_id INT NOT NULL,
  prescription_id INT NULL, -- Links to prescriptions, NULL for general stock adjustments
  action_type ENUM('purchase', 'dispense', 'adjustment', 'return') NOT NULL,
  quantity_change INT NOT NULL, -- Positive for increase, negative for decrease
  bottles_used INT NULL, -- For liquid medicines, number of bottles
  volume_used FLOAT NULL, -- For liquid medicines, total volume dispensed
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT NULL, -- Staff ID who performed the action
  FOREIGN KEY (medicine_id) REFERENCES medicines(id),
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES auth_service.staff(id)
);

-- =========================================================
-- NOTIFICATION SERVICE DATABASE
-- (Conceptual - Actual tables would depend on notification types)
-- =========================================================
CREATE DATABASE IF NOT EXISTS notification_service;
USE notification_service;

-- Example: Notifications Table (basic structure)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    patient_id INT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES auth_service.users(id),
    FOREIGN KEY (patient_id) REFERENCES patient_service.patients(id)
);

-- =========================================================
-- REPORT SERVICE DATABASE
-- Stores aggregated and denormalized data for reporting and analytics.
-- =========================================================
CREATE DATABASE IF NOT EXISTS report_service;
USE report_service;

-- Medicine Prescription Stats Table: Aggregates data on medicine prescriptions
CREATE TABLE IF NOT EXISTS medicine_prescription_stats (
  medicine_id INT NOT NULL,
  medicine_name VARCHAR(255) NOT NULL,
  total_prescribed INT DEFAULT 0,
  total_quantity INT DEFAULT 0,
  total_liquid_volume FLOAT DEFAULT 0,
  is_liquid BOOLEAN DEFAULT FALSE,
  month_year DATE NOT NULL,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (medicine_id, month_year),
  INDEX (month_year),
  INDEX (medicine_id)
);

-- Patient Record Stats Table: Aggregates data on patient visits and diagnoses
CREATE TABLE IF NOT EXISTS patient_record_stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  department_id INT,
  department_name VARCHAR(255),
  diagnosis VARCHAR(255),
  visit_date DATE NOT NULL,
  month_year DATE NOT NULL,
  record_id INT NOT NULL UNIQUE,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (month_year),
  INDEX (patient_id),
  INDEX (department_id)
);

-- Patients Table (Denormalized for Reporting): Basic patient info for reports
CREATE TABLE IF NOT EXISTS patients (
  id INT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  gender VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_synced TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Prescriptions Table (Denormalized for Reporting): Basic prescription info for reports
CREATE TABLE IF NOT EXISTS prescriptions (
  id INT PRIMARY KEY,
  patient_id INT,
  medicine TEXT,
  status VARCHAR(20) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_synced TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (patient_id),
  INDEX (status)
);

-- Sync Log Table: Tracks synchronization operations between services and report_service
CREATE TABLE IF NOT EXISTS sync_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sync_type VARCHAR(50) NOT NULL,
  start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  end_time TIMESTAMP NULL,
  records_processed INT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'running',
  message TEXT,
  INDEX (sync_type),
  INDEX (status)
);