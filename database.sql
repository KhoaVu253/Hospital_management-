-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS hospital_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_management;

-- Bảng người dùng
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'patient', 'doctor') NOT NULL DEFAULT 'patient',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng bác sĩ
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    specialty VARCHAR(100) NOT NULL,
    experience_years INT,
    education TEXT,
    license_number VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng lịch khám
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    symptoms TEXT,
    diagnosis TEXT,
    prescription TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Bảng hóa đơn
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    consultation_fee DECIMAL(10,2) DEFAULT 0.00,
    medicine_fee DECIMAL(10,2) DEFAULT 0.00,
    other_fees DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('unpaid', 'paid', 'cancelled') DEFAULT 'unpaid',
    payment_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- Bảng thuốc
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    unit VARCHAR(20),
    status ENUM('available', 'out_of_stock') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng đơn thuốc
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    medicine_id INT NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    duration VARCHAR(100),
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

-- Thêm dữ liệu mẫu

-- Tạo tài khoản admin
INSERT INTO users (username, password, email, role, full_name, phone, gender) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hospital.com', 'admin', 'Administrator', '0123456789', 'male');

-- Tạo một số bác sĩ mẫu
INSERT INTO users (username, password, email, role, full_name, phone, gender, date_of_birth) VALUES 
('doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor1@hospital.com', 'doctor', 'Bác sĩ Nguyễn Văn A', '0987654321', 'male', '1980-01-15'),
('doctor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor2@hospital.com', 'doctor', 'Bác sĩ Trần Thị B', '0987654322', 'female', '1985-03-20');

-- Thêm thông tin bác sĩ
INSERT INTO doctors (user_id, specialty, experience_years, education, license_number) VALUES 
(2, 'Tim mạch', 15, 'Đại học Y Hà Nội', 'BS001'),
(3, 'Nhi khoa', 12, 'Đại học Y Dược TP.HCM', 'BS002');

-- Tạo một số bệnh nhân mẫu
INSERT INTO users (username, password, email, role, full_name, phone, gender, date_of_birth, address) VALUES 
('patient1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient1@email.com', 'patient', 'Nguyễn Văn C', '0123456780', 'male', '1990-05-10', 'Hà Nội'),
('patient2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient2@email.com', 'patient', 'Trần Thị D', '0123456781', 'female', '1995-08-15', 'TP.HCM');

-- Thêm thuốc mẫu
INSERT INTO medicines (name, description, price, stock_quantity, unit) VALUES 
('Paracetamol', 'Thuốc giảm đau, hạ sốt', 5000.00, 100, 'viên'),
('Amoxicillin', 'Kháng sinh', 15000.00, 50, 'viên'),
('Vitamin C', 'Bổ sung vitamin', 3000.00, 200, 'viên'); 