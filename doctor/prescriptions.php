<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';
require_once '../includes/functions.php';

requireDoctor();

$message = '';
$error = '';

$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $diagnosis = sanitizeInput($_POST['diagnosis']);
    $prescription_notes = sanitizeInput($_POST['prescription_notes']);
    $medicines = $_POST['medicines'] ?? [];
    $dosages = $_POST['dosages'] ?? [];
    $durations = $_POST['durations'] ?? [];
    $instructions = $_POST['instructions'] ?? [];

    if (empty($diagnosis)) {
        $error = "Vui lòng nhập chẩn đoán bệnh lý";
    } elseif (empty($medicines) || empty($medicines[0])) {
        $error = "Vui lòng chọn ít nhất một loại thuốc";
    } else {
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ? AND status IN ('confirmed', 'completed')");
        $stmt->bind_param("ii", $appointment_id, $doctor_id);
        $stmt->execute();
        $appointment_check = $stmt->get_result();
        
        if ($appointment_check->num_rows === 0) {
            $error = "Lịch khám không hợp lệ hoặc không thuộc quyền của bạn";
        } else {
            $conn->begin_transaction();

            try {
                $stmt = $conn->prepare("UPDATE appointments SET diagnosis = ?, prescription = ? WHERE id = ? AND doctor_id = ?");
                $stmt->bind_param("ssii", $diagnosis, $prescription_notes, $appointment_id, $doctor_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi khi cập nhật chẩn đoán: " . $conn->error);
                }

                $stmt = $conn->prepare("DELETE FROM prescriptions WHERE appointment_id = ?");
                $stmt->bind_param("i", $appointment_id);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO prescriptions (appointment_id, medicine_id, dosage, duration, instructions) VALUES (?, ?, ?, ?, ?)");
                
                $medicines_added = 0;
                for ($i = 0; $i < count($medicines); $i++) {
                    if (!empty($medicines[$i]) && !empty($dosages[$i])) {
                        $stmt->bind_param("iisss", $appointment_id, $medicines[$i], $dosages[$i], $durations[$i], $instructions[$i]);
                        if (!$stmt->execute()) {
                            throw new Exception("Lỗi khi thêm thuốc: " . $conn->error);
                        }
                        $medicines_added++;
                    }
                }
                
                if ($medicines_added === 0) {
                    throw new Exception("Không có thuốc nào được thêm vào đơn thuốc");
                }

                $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND doctor_id = ?");
                $stmt->bind_param("ii", $appointment_id, $doctor_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi khi cập nhật trạng thái lịch khám: " . $conn->error);
                }

                $conn->commit();
                $message = "Kê thuốc và đánh giá bệnh lý thành công! Đã thêm " . $medicines_added . " loại thuốc.";
                
                header('Location: prescriptions.php?success=1');
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = "Kê thuốc và đánh giá bệnh lý thành công!";
}

$stmt = $conn->prepare("SELECT a.*, u.full_name as patient_name, u.gender, u.date_of_birth
                        FROM appointments a
                        JOIN users u ON a.patient_id = u.id
                        WHERE a.doctor_id = ? AND a.status IN ('confirmed', 'completed')
                        ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();

$stmt = $conn->prepare("SELECT id, name, description, price, unit FROM medicines WHERE status = 'available' ORDER BY name");
$stmt->execute();
$medicines = $stmt->get_result();

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<style>
.medicine-row {
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.medicine-row.fade-out {
    opacity: 0;
    transform: translateX(-20px);
}

.modal {
    transition: opacity 0.3s ease;
}

.form-loading {
    opacity: 0.6;
    pointer-events: none;
}

.table-responsive {
    will-change: transform;
}

.table {
    table-layout: fixed;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Lịch khám
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="prescriptions.php">
                            <i class="fas fa-pills me-2"></i>
                            Kê thuốc
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>
                            Thông tin cá nhân
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-pills me-2"></i>
                    Kê thuốc và đánh giá bệnh lý
                </h1>
                <div>
                    <button type="button" class="btn btn-info btn-sm me-2" onclick="testJavaScript()">
                        <i class="fas fa-bug"></i> Test JavaScript
                    </button>
                    <a href="?debug=1" class="btn btn-warning btn-sm">
                        <i class="fas fa-cog"></i> Debug Mode
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Danh sách lịch khám cần kê thuốc
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Bệnh nhân</th>
                                    <th>Ngày khám</th>
                                    <th>Triệu chứng</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['symptoms'] ?? 'Không có'); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        switch($row['status']) {
                                            case 'confirmed':
                                                $status_class = 'badge bg-warning';
                                                $status_text = 'Đã xác nhận';
                                                break;
                                            case 'completed':
                                                $status_class = 'badge bg-success';
                                                $status_text = 'Hoàn thành';
                                                break;
                                            default:
                                                $status_class = 'badge bg-secondary';
                                                $status_text = $row['status'];
                                        }
                                        ?>
                                        <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="openPrescriptionModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['patient_name']); ?>', '<?php echo $row['status']; ?>')">
                                            <?php if ($row['status'] === 'confirmed'): ?>
                                                <i class="fas fa-prescription"></i> Kê thuốc
                                            <?php else: ?>
                                                <i class="fas fa-eye"></i> Xem đơn thuốc
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if($appointments->num_rows == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có lịch khám nào cần kê thuốc</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="prescriptionModal" tabindex="-1" aria-labelledby="prescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prescriptionModalLabel">
                    <i class="fas fa-prescription me-2"></i>
                    Kê thuốc
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="prescriptionForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="appointment_id">
                    <input type="hidden" id="appointment_status">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bệnh nhân</label>
                                <input type="text" class="form-control" id="patient_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày khám</label>
                                <input type="text" class="form-control" id="appointment_date" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Chẩn đoán <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="prescription_notes" class="form-label">Ghi chú đơn thuốc</label>
                        <textarea class="form-control" id="prescription_notes" name="prescription_notes" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thuốc kê</label>
                        <div id="medicines_container">
                            <div class="row mb-2 medicine-row">
                                <div class="col-md-3">
                                    <select class="form-select" name="medicines[]" required>
                                        <option value="">Chọn thuốc</option>
                                        <?php 
                                        $medicines->data_seek(0);
                                        while ($medicine = $medicines->fetch_assoc()): 
                                        ?>
                                        <option value="<?php echo $medicine['id']; ?>">
                                            <?php echo htmlspecialchars($medicine['name']); ?> - <?php echo formatCurrency($medicine['price']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="dosages[]" placeholder="Liều dùng" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="durations[]" placeholder="Thời gian">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="instructions[]" placeholder="Hướng dẫn">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-medicine">Xóa</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addMedicine()">
                            <i class="fas fa-plus"></i> Thêm thuốc
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Lưu đơn thuốc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const medicinesData = <?php 
    $medicines->data_seek(0);
    $medicinesArray = [];
    while ($medicine = $medicines->fetch_assoc()) {
        $medicinesArray[] = [
            'id' => $medicine['id'],
            'name' => $medicine['name'],
            'price' => $medicine['price']
        ];
    }
    echo json_encode($medicinesArray);
?>;

const medicineRowTemplate = `
    <div class="row mb-2 medicine-row">
        <div class="col-md-3">
            <select class="form-select" name="medicines[]" required>
                <option value="">Chọn thuốc</option>
                ${medicinesData.map(medicine => 
                    `<option value="${medicine.id}">${medicine.name} - ${formatCurrency(medicine.price)}</option>`
                ).join('')}
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="dosages[]" placeholder="Liều dùng" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="durations[]" placeholder="Thời gian">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="instructions[]" placeholder="Hướng dẫn">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-medicine">Xóa</button>
        </div>
    </div>
`;

function openPrescriptionModal(appointmentId, patientName, status) {
    document.getElementById('prescriptionForm').reset();
    document.getElementById('appointment_id').value = appointmentId;
    document.getElementById('patient_name').value = patientName;
    document.getElementById('appointment_status').value = status;
    
    <?php
    $appointments_data = [];
    $appointments->data_seek(0);
    while ($row = $appointments->fetch_assoc()) {
        $appointments_data[] = $row;
    }
    ?>
    const appointmentsData = <?php echo json_encode($appointments_data); ?>;
    
    const appointment = appointmentsData.find(a => a.id == appointmentId);
    if (appointment) {
        document.getElementById('appointment_date').value = appointment.appointment_date;
        document.getElementById('diagnosis').value = appointment.diagnosis || '';
        document.getElementById('prescription_notes').value = appointment.prescription || '';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('prescriptionModal'));
    modal.show();
}

function addMedicine() {
    const container = document.getElementById('medicines_container');
    const newRow = document.createElement('div');
    newRow.innerHTML = medicineRowTemplate;
    container.appendChild(newRow);
    
    const newSelect = newRow.querySelector('select');
    if (newSelect) {
        newSelect.focus();
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function testJavaScript() {
    console.log('JavaScript test function called');
    alert('JavaScript is working! Check console for more details.');
    console.log('Medicines data:', medicinesData);
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-medicine')) {
        const row = e.target.closest('.medicine-row');
        if (row) {
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
            }, 300);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('prescriptionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
            this.classList.add('form-loading');
        });
    }
    
    const modal = document.getElementById('prescriptionModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                const container = document.getElementById('medicines_container');
                container.innerHTML = `
                    <div class="row mb-2 medicine-row">
                        <div class="col-md-3">
                            <select class="form-select" name="medicines[]" required>
                                <option value="">Chọn thuốc</option>
                                ${medicinesData.map(medicine => 
                                    `<option value="${medicine.id}">${medicine.name} - ${formatCurrency(medicine.price)}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="dosages[]" placeholder="Liều dùng" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="durations[]" placeholder="Thời gian">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="instructions[]" placeholder="Hướng dẫn">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-medicine">Xóa</button>
                        </div>
                    </div>
                `;
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
