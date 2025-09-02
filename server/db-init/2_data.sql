
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
('duocsi1', '$2a$12$EA1YaTqq6CLFvQqENIrp5.yKtwWLhsX3sx1fAzN11lFQ5O9pHkktK', 2),
('letan1', '$2a$12$PU2pCGoEsTR418j/fuOf6O7oimwaPhw1qvh/EgJW/RcEEZPy6eD6S', 3),
('admin', '$2a$12$7TY7NeHT4tS1cZPglO6w/.MQQse8msHf9/T2.GC1oRpmaslhJyk.y', 4);

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