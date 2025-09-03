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
-- ########## PATIENT SERVICE ##########
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
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) -- Khóa ngoại trong cùng DB thì giữ lại
);
-- ########## APPOINTMENT SERVICE ##########
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
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE SET NULL
);

-- Bạn có thể thêm các CREATE DATABASE và bảng cho các service còn lại ở đây
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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


-- =========================================================
-- SAMPLE DATA INSERTS
-- (These would typically be in separate seed files or managed by application logic)
-- =========================================================
USE auth_service;

-- Sample Data for auth-service: Roles
INSERT INTO role (ten_role) VALUES
('bacsi'), ('duocsi'), ('letan'), ('admin');

-- Sample Data for auth-service: Users
INSERT INTO users (username, password, role_id) VALUES
('bacsi1', '$2a$12$HFPc2MAAByA7mYQm2fJpgOdbGpcAZ/yNXDAe7sFywyegyU7KQ/ZqC', 1),
('bacsi2', '$2a$12$KK1I9WaXgU.sV6KAaptNROvIrwNY/64SOidByO3s/oFWRm9xs4aMC', 1),
('bacsi3', '$2a$12$5.aXKsM5qQumDJih9vxPX.eTvqC.iZrVVK3vuNPhkEba2EHCWH4aa', 1),
('bacsi4', '$2a$12$UCkSh.o8JsiRMuCpaE/P8ekg9RqS6zaCLX.HNavDD5ZYU18KJgZai', 1),
('duocsi1', '$2a$12$EA1YaTqq6CLFvQqENIrp5.yKtwWLhsX3sx1fAzN11lFQ5O9pHkktK', 2),
('letan1', '$2a$12$PU2pCGoEsTR418j/fuOf6O7oimwaPhw1qvh/EgJW/RcEEZPy6eD6S', 3),
('admin', '$2a$12$IjizNHphZoYzKuW2ZPebzuPi0ONMeWpTjL23mbSkYGkAX4WjAzWUq', 4);

-- Sample Data for auth-service: Departments
INSERT INTO department (ten_ck) VALUES
('Nội tổng quát'),
('Ngoại khoa'),
('Nhi khoa'),
('Tim mạch');

-- Sample Data for auth-service: Staff
INSERT INTO staff (staff_code, hoten_nv, email, sdt, gender, dob, role_id, department_id, begin_date) VALUES
(1, 'Dr. Nguyen Bac Si', 'bsnguyen@example.com', '0911111111', 'nam', '1970-03-01', 1, 1, '2000-01-01'),
(2, 'Dr. Tran Van Ky', 'kytran@example.com', '0916666666', 'nam', '1982-09-12', 1, 2, '2012-02-01'),
(3, 'Dr. Le Thi Hue', 'huele@example.com', '0917777777', 'nu', '1988-03-18', 1, 3, '2014-03-01'),
(4, 'Dr. Nguyen Quang', 'quangnguyen@example.com', '0918888888', 'nam', '1990-11-20', 1, 4, '2017-05-15'),

(5, 'Pharmacist Le Duoc', 'duocle@example.com', '0913333333', 'nam', '1985-06-10', 2, NULL, '2010-01-01'),
(6, 'Receptionist Pham Letan', 'letanpham@example.com', '0914444444', 'nu', '1990-07-20', 3, NULL, '2015-01-01'),
(7, 'Admin Hoang', 'adminhoang@example.com', '0915555555', 'nam', '1975-09-25', 4, NULL, '2000-01-01');


USE patient_service;

-- Sample Data for patient-service: Patients
INSERT INTO patients (hoten_bn, dob, gender, sdt, diachi, tiensu_benh, lichsu_kham) VALUES
('Nguyen Van A', '1990-05-12', 'nam', '0901111111', 'Hanoi', 'Tăng huyết áp', 'Khám năm 2021'),
('Tran Thi B', '1985-08-20', 'nu', '0902222222', 'HCM', 'Tiểu đường', 'Khám năm 2022'),
('Le Van C', '2000-01-01', 'nam', '0903333333', 'Danang', 'Hen suyễn', 'Khám năm 2023'),
('Pham Thi D', '1995-11-15', 'nu', '0904444444', 'Hue', 'Không', 'Chưa có'),
('Hoang Van E', '1975-07-30', 'nam', '0905555555', 'Can Tho', 'Bệnh tim', 'Khám năm 2020');

-- Sample Data for patient-service: Medical Records
INSERT INTO medical_records (patient_id, doctor_id, department_id, ngaykham, lydo, chan_doan, ngay_taikham, ghichu) VALUES
(1, 1, 1, '2023-06-01 09:00:00', 'Đau đầu', 'Cảm cúm', '2023-06-10', 'Nghỉ ngơi'),
(2, 2, 2, '2023-06-05 14:00:00', 'Đau bụng', 'Viêm dạ dày', '2023-06-12', 'Uống thuốc đầy đủ'),
(3, 3, 3, '2023-06-07 10:00:00', 'Khó thở', 'Hen suyễn', '2023-06-14', 'Tránh dị ứng'),
(4, 4, 4, '2023-06-08 15:00:00', 'Nổi mẩn', 'Viêm da', '2023-06-15', 'Bôi thuốc ngoài da'),
(5, 1, 1, '2023-06-09 08:30:00', 'Đau ngực', 'Bệnh tim', '2023-06-16', 'Theo dõi thêm');


USE appointment_service;

-- Sample Data for appointment-service: Appointments
INSERT INTO appointments (patient_id, department_id, doctor_id, receptionist_id, thoi_gian_hen, lydo, status, note) VALUES
(1, 1, 1, 7, '2023-07-01 09:00:00', 'Khám định kỳ', 'pending', 'Mang hồ sơ'),
(2, 2, 2, 7, '2023-07-02 10:00:00', 'Đau bụng', 'confirmed', 'Đến sớm 10 phút'),
(3, 3, 3, 7, '2023-07-03 14:00:00', 'Khó thở', 'pending', ''),
(4, 4, 4, 7, '2023-07-04 15:00:00', 'Nổi mẩn', 'confirmed', 'Mang kết quả xét nghiệm'),
(5, 1, 1, 7, '2023-07-05 08:30:00', 'Đau ngực', 'confirmed', 'Mang thuốc đang dùng');


USE prescription_service;

-- Sample Data for prescription-service: Medicines
INSERT INTO medicines (id, ten_thuoc, so_luong, don_vi, don_gia, is_liquid, volume_per_bottle, volume_unit) VALUES
(1, 'Paracetamol', 1, 'viên', 1000, FALSE, NULL, NULL),
(2, 'Amoxicillin', 150, 'túi', 2323, FALSE, NULL, NULL),
(3, 'Salbutamol', 100, 'chai', 5555, TRUE, 100, 'ml'),
(4, 'Aspirin', 299, 'viên', 8080, FALSE, NULL, NULL),
(5, 'Cetirizine', 120, 'ống', 3000, TRUE, 30, 'ml');

-- Sample Data for prescription-service: Prescriptions
INSERT INTO prescriptions (record_id, pharmacist_id, status, created_at) VALUES
(1, 6, 'dispensed', '2023-06-01 10:00:00'),
(2, 6, 'dispensed', '2023-06-05 15:00:00'),
(3, NULL, 'pending', '2023-06-07 11:00:00'),
(4, 6, 'dispensed', '2023-06-08 16:00:00'),
(5, 6, 'dispensed', '2023-06-09 09:30:00');

-- Sample Data for prescription-service: Prescription Medicines
INSERT INTO prescription_medicines (prescription_id, medicine_id, dose, frequency, duration, note) VALUES
(1, 1, '1 viên', '2 lần/ngày', '5 ngày', 'Uống sau ăn'),
(2, 2, '2 túi', '3 lần/ngày', '7 ngày', 'Uống đủ liệu trình'),
(3, 3, '3 nhát', '3 lần/ngày', '10 ngày', 'Mang theo khi đi ra ngoài'),
(4, 5, '4 ống', '1 lần/ngày', '14 ngày', 'Trước khi ngủ'),
(5, 4, '5 viên', '1 lần/ngày', '30 ngày', 'Sau bữa sáng');

INSERT INTO medicine_stock_log (
    medicine_id, 
    prescription_id, 
    action_type, 
    quantity_change, 
    bottles_used, 
    volume_used, 
    note, 
    created_by
) 
VALUES 
(1, 1, 'dispense', -1, NULL, NULL, 'Dispensed for patient 1', 1), 
(2, 2, 'dispense', -2, NULL, NULL, 'Dispensed for patient 2', 2), 
(3, NULL, 'purchase', 50, NULL, NULL, 'Initial stock purchase', 5), 
(5, 4, 'dispense', -4, 4, 120, 'Dispensed for patient 4', 3),
(4, 5, 'dispense', -5, NULL, NULL, 'Dispensed for patient 5', 4);