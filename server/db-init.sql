-- ########## AUTH SERVICE ##########
CREATE DATABASE IF NOT EXISTS auth_service;
USE auth_service;

CREATE TABLE role (
  id_role INT AUTO_INCREMENT PRIMARY KEY,
  ten_role VARCHAR(100) UNIQUE
);

CREATE TABLE department (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ten_ck VARCHAR(255) UNIQUE
);

CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_code INT NOT NULL UNIQUE,
  hoten_nv VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  sdt VARCHAR(50) NOT NULL UNIQUE,
  gender ENUM('nam','nu','khac') NOT NULL,
  dob DATE,
  role_id INT NOT NULL,
  department_id INT,
  begin_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES role(id_role),
  FOREIGN KEY (department_id) REFERENCES department(id)
);

-- Sample Data for auth-service
INSERT INTO role (ten_role) VALUES ('bacsi'), ('duocsi'), ('letan'), ('admin');
INSERT INTO department (ten_ck) VALUES ('Nội tổng quát'), ('Ngoại khoa'), ('Nhi khoa'), ('Tim mạch'), ('Da liễu');
INSERT INTO staff (staff_code, hoten_nv, email, sdt, gender, dob, role_id, department_id, begin_date) VALUES
(1001, 'Dr. Nguyen Bac Si', 'bsnguyen@example.com', '0911111111', 'nam', '1970-03-01', 1, 1, '2000-01-01'),
(1002, 'Nurse Tran Y Ta', 'ytatran@example.com', '0912222222', 'nu', '1980-04-15', 1, 1, '2005-01-01'),
(1003, 'Pharmacist Le Duoc', 'duocle@example.com', '0913333333', 'nam', '1985-06-10', 2, NULL, '2010-01-01'),
(1004, 'Receptionist Pham Letan', 'letanpham@example.com', '0914444444', 'nu', '1990-07-20', 3, NULL, '2015-01-01'),
(1005, 'Admin Hoang', 'adminhoang@example.com', '0915555555', 'nam', '1975-09-25', 4, NULL, '2000-01-01');

-- ########## PATIENT SERVICE ##########
CREATE DATABASE IF NOT EXISTS patient_service;
USE patient_service;

CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hoten_bn VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  gender ENUM('nam', 'nu', 'khac') NOT NULL,
  sdt VARCHAR(15) UNIQUE,
  diachi VARCHAR(255),
  tiensu_benh TEXT,
  lichsu_kham TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE medical_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,     -- Chỉ lưu ID, không có khóa ngoại
  department_id INT,        -- Chỉ lưu ID, không có khóa ngoại
  ngaykham DATETIME NOT NULL,
  lydo VARCHAR(255),
  chan_doan TEXT,
  ngay_taikham DATE,
  ghichu TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  FOREIGN KEY (patient_id) REFERENCES patients(id) -- Khóa ngoại trong cùng DB thì giữ lại
);

-- Sample Data for patient-service
INSERT INTO patients (hoten_bn, dob, gender, sdt, diachi, tiensu_benh, lichsu_kham) VALUES
('Nguyen Van A', '1990-05-12', 'nam', '0901111111', 'Hanoi', 'Tăng huyết áp', 'Khám năm 2021'),
('Tran Thi B', '1985-08-20', 'nu', '0902222222', 'HCM', 'Tiểu đường', 'Khám năm 2022'),
('Le Van C', '2000-01-01', 'nam', '0903333333', 'Danang', 'Hen suyễn', 'Khám năm 2023'),
('Pham Thi D', '1995-11-15', 'nu', '0904444444', 'Hue', 'Không', 'Chưa có'),
('Hoang Van E', '1975-07-30', 'nam', '0905555555', 'Can Tho', 'Bệnh tim', 'Khám năm 2020');

INSERT INTO medical_records (patient_id, doctor_id, department_id, ngaykham, lydo, chan_doan, ngay_taikham, ghichu) VALUES
(1, 1001, 1, '2023-06-01 09:00:00', 'Đau đầu', 'Cảm cúm', '2023-06-10', 'Nghỉ ngơi'),
(2, 1001, 1, '2023-06-05 14:00:00', 'Đau bụng', 'Viêm dạ dày', '2023-06-12', 'Uống thuốc đầy đủ'),
(3, 1001, 4, '2023-06-07 10:00:00', 'Khó thở', 'Hen suyễn', '2023-06-14', 'Tránh dị ứng'),
(4, 1001, 5, '2023-06-08 15:00:00', 'Nổi mẩn', 'Viêm da', '2023-06-15', 'Bôi thuốc ngoài da'),
(5, 1001, 4, '2023-06-09 08:30:00', 'Đau ngực', 'Bệnh tim', '2023-06-16', 'Theo dõi thêm');

-- ########## APPOINTMENT SERVICE ##########
CREATE DATABASE IF NOT EXISTS appointment_service;
USE appointment_service;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  department_id INT NOT NULL,
  doctor_id INT NOT NULL,
  receptionist_id INT NOT NULL,
  thoi_gian_hen DATETIME NOT NULL,
  lydo VARCHAR(255),
  status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  -- Đã xóa tất cả các khóa ngoại tham chiếu chéo
);

-- Sample Data for appointment-service
INSERT INTO appointments (patient_id, department_id, doctor_id, receptionist_id, thoi_gian_hen, lydo, status, note) VALUES
(1, 1, 1001, 1004, '2023-07-01 09:00:00', 'Khám định kỳ', 'pending', 'Mang hồ sơ'),
(2, 1, 1001, 1004, '2023-07-02 10:00:00', 'Đau bụng', 'confirmed', 'Đến sớm 10 phút'),
(3, 4, 1001, 1004, '2023-07-03 14:00:00', 'Khó thở', 'pending', ''),
(4, 5, 1001, 1004, '2023-07-04 15:00:00', 'Nổi mẩn', 'confirmed', 'Mang kết quả xét nghiệm'),
(5, 4, 1001, 1004, '2023-07-05 08:30:00', 'Đau ngực', 'confirmed', 'Mang thuốc đang dùng');

-- ########## PRESCRIPTION SERVICE ##########
CREATE DATABASE IF NOT EXISTS prescription_service;
USE prescription_service;

CREATE TABLE medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ten_thuoc VARCHAR(255) NOT NULL UNIQUE,
  so_luong INT,
  don_vi VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE prescriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  record_id INT NOT NULL,
  pharmacist_id INT,
  status ENUM('pending','dispensed','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE prescription_medicines (
  id INT PRIMARY KEY AUTO_INCREMENT,
  prescription_id INT NOT NULL,
  medicine_id INT NOT NULL,
  dose INT NOT NULL,
  frequency VARCHAR(50),
  duration VARCHAR(50),
  note TEXT,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id)
);
CREATE TABLE IF NOT EXISTS medicine_stock_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medicine_id INT NOT NULL,
  prescription_id INT NULL,
  action_type ENUM('purchase', 'dispense', 'adjustment', 'return') NOT NULL,
  quantity_change INT NOT NULL,
  bottles_used INT NULL,
  volume_used FLOAT NULL,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT NULL,
  FOREIGN KEY (medicine_id) REFERENCES medicines(id),
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE SET NULL
);
-- Sample Data for prescription-service
INSERT INTO medicines (ten_thuoc, so_luong,don_vi) VALUES
('Paracetamol', 200,'viên'),
('Amoxicillin', 150,'túi'),
('Salbutamol', 100,'ống'),
('Aspirin', 300,'viên'),
('Cetirizine', 120,'chai');

-- Updated sample prescriptions with numeric dose
INSERT INTO prescriptions (record_id, pharmacist_id, status, created_at) VALUES
(1, 1003, 'dispensed', '2023-06-01 10:00:00'),
(2, 1003, 'dispensed', '2023-06-05 15:00:00'),
(3, NULL, 'pending', '2023-06-07 11:00:00'),
(4, 1003, 'dispensed', '2023-06-08 16:00:00'),
(5, 1003, 'dispensed', '2023-06-09 09:30:00');

-- Prescription medicines with numeric dose
INSERT INTO prescription_medicines (prescription_id, medicine_id, dose, frequency, duration, note) VALUES
(1, 1, 1, '2 lần/ngày', '5 ngày', 'Uống sau ăn'),
(2, 2, 2, '3 lần/ngày', '7 ngày', 'Uống đủ liệu trình'),
(3, 3, 3, 'Khi khó thở', '10 ngày', 'Mang theo khi đi ra ngoài'),
(4, 5, 4, '1 lần/ngày', '14 ngày', 'Trước khi ngủ'),
(5, 4, 5, '1 lần/ngày', '30 ngày', 'Sau bữa sáng');

-- Bạn có thể thêm các CREATE DATABASE và bảng cho các service còn lại ở đây
CREATE DATABASE IF NOT EXISTS notification_service;
CREATE DATABASE IF NOT EXISTS report_service;