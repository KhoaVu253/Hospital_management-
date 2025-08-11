-- Cập nhật cơ sở dữ liệu để thêm cột consultation_fee vào bảng doctors
USE hospital_management;

-- Thêm cột consultation_fee vào bảng doctors nếu chưa có
ALTER TABLE doctors ADD COLUMN IF NOT EXISTS consultation_fee DECIMAL(10,2) DEFAULT 200000.00;

-- Cập nhật phí khám bệnh cho các bác sĩ hiện có
UPDATE doctors SET consultation_fee = 200000.00 WHERE consultation_fee = 0 OR consultation_fee IS NULL;

-- Cập nhật phí khám bệnh cụ thể cho từng bác sĩ (nếu có)
-- Bác sĩ Nội tổng quát
UPDATE doctors d 
JOIN users u ON d.user_id = u.id 
SET d.consultation_fee = 200000.00 
WHERE u.full_name LIKE '%Nguyễn Văn A%' AND d.specialty = 'Nội tổng quát';

-- Bác sĩ Ngoại tổng quát  
UPDATE doctors d 
JOIN users u ON d.user_id = u.id 
SET d.consultation_fee = 250000.00 
WHERE u.full_name LIKE '%Trần Thị B%' AND d.specialty = 'Ngoại tổng quát';

-- Hiển thị kết quả
SELECT d.id, u.full_name, d.specialty, d.consultation_fee 
FROM doctors d 
JOIN users u ON d.user_id = u.id;
