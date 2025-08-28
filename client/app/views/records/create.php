<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\views\records\create.php
require_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tạo Hồ Sơ Khám Bệnh</h5>
                    <a href="<?php echo !empty($appointment) ? '/appointments/view/' . $appointment['id'] : '/records'; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="/records/create">
                        <!-- Hidden fields for connection to appointment -->
                        <?php if (!empty($appointment)): ?>
                            <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment['id']) ?>">
                        <?php endif; ?>
                        
                        <!-- Patient Information -->
                        <div class="row mb-4">
                            <div class="col">
                                <h6 class="text-muted mb-3">Thông Tin Bệnh Nhân</h6>
                                
                                <?php if (!empty($patient)): ?>
                                    <!-- Patient is pre-selected -->
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($patient['hoten_bn']) ?></h6>
                                            <p class="card-text small mb-0">
                                                <strong>SĐT:</strong> <?= htmlspecialchars($patient['sdt'] ?? 'N/A') ?> &bull; 
                                                <strong>Ngày sinh:</strong> <?= date('d/m/Y', strtotime($patient['dob'])) ?> &bull; 
                                                <strong>Giới tính:</strong> <?= htmlspecialchars($patient['gender']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient['id']) ?>">
                                <?php else: ?>
                                    <!-- No patient selected, need to search -->
                                    <div class="mb-3">
                                        <label class="form-label">Tìm Bệnh Nhân</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchPatient" 
                                                placeholder="Tìm bệnh nhân theo tên hoặc SĐT..." required>
                                            <button class="btn btn-outline-secondary" type="button" id="newPatientBtn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="patient_id" id="patientId" required>
                                        <div id="patientResults" class="list-group mt-2" style="display: none;"></div>
                                        
                                        <div class="mt-2" id="selectedPatient" style="display: none;">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title" id="patientName"></h6>
                                                    <p class="card-text small" id="patientDetails"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Medical Examination -->
                        <h6 class="text-muted mb-3">Thông Tin Khám Bệnh</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Khoa</label>
                                <select class="form-select" name="department_id" required>
                                    <option value="">Chọn khoa...</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['id']) ?>" 
                                            <?= (!empty($appointment) && $appointment['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['ten_ck']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bác sĩ</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" readonly>
                                <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($_SESSION['user']['id']) ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Lý Do Khám</label>
                            <textarea class="form-control" name="lydo" rows="2" required><?= !empty($appointment) ? htmlspecialchars($appointment['lydo']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Chẩn Đoán</label>
                            <textarea class="form-control" name="chan_doan" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Ngày Tái Khám (nếu cần)</label>
                                <input type="date" class="form-control" name="ngay_taikham">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ghi Chú</label>
                            <textarea class="form-control" name="ghichu" rows="2"></textarea>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end mt-4">
                            <a href="<?php echo !empty($appointment) ? '/appointments/view/' . $appointment['id'] : '/records'; ?>" class="btn btn-secondary me-2">
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu và Kê Đơn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize patient search if needed
    <?php if (empty($patient)): ?>
    const searchPatient = document.getElementById('searchPatient');
    const patientResults = document.getElementById('patientResults');
    const selectedPatient = document.getElementById('selectedPatient');
    const patientId = document.getElementById('patientId');
    const patientName = document.getElementById('patientName');
    const patientDetails = document.getElementById('patientDetails');
    
    // Patient search with debounce
    let searchTimeout;
    searchPatient.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const term = this.value.trim();
        
        if (term.length < 2) {
            patientResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                patientResults.innerHTML = `
                    <div class="list-group-item">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span class="ms-2">Đang tìm...</span>
                    </div>`;
                patientResults.style.display = 'block';
                
                const response = await fetch(`/api/patients/search?term=${encodeURIComponent(term)}`);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Search failed');
                }
                
                renderPatientResults(result.data || []);
            } catch (error) {
                console.error('Search error:', error);
                patientResults.innerHTML = `
                    <div class="list-group-item text-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        ${error.message || 'Lỗi tìm kiếm'}
                    </div>`;
            }
        }, 300);
    });

    // Patient selection
    patientResults.addEventListener('click', function(e) {
        const item = e.target.closest('.patient-item');
        if (item) {
            const id = item.dataset.id;
            const info = JSON.parse(item.dataset.info);
            
            patientId.value = id;
            patientName.textContent = info.hoten_bn;
            
            // Format birthday
            const dob = info.dob ? new Date(info.dob).toLocaleDateString('vi-VN') : 'N/A';
            
            patientDetails.innerHTML = `
                <strong>SĐT:</strong> ${info.sdt || 'Không có'} &bull; 
                <strong>Ngày sinh:</strong> ${dob} &bull; 
                <strong>Giới tính:</strong> ${info.gender || 'N/A'}
            `;
            
            selectedPatient.style.display = 'block';
            patientResults.style.display = 'none';
            searchPatient.value = '';
        }
    });
    
    // Render patient search results
    function renderPatientResults(patients) {
        if (!Array.isArray(patients) || patients.length === 0) {
            patientResults.innerHTML = `
                <div class="list-group-item text-muted">
                    <i class="fas fa-info-circle"></i> Không tìm thấy bệnh nhân
                </div>`;
            return;
        }

        patientResults.innerHTML = patients.map(patient => `
            <a href="#" class="list-group-item list-group-item-action patient-item" 
               data-id="${patient.id}" 
               data-info='${JSON.stringify(patient)}'>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${patient.hoten_bn}</strong>
                        <br>
                        <small class="text-muted">
                            ${patient.dob ? new Date(patient.dob).toLocaleDateString('vi-VN') : 'N/A'} | 
                            ${patient.gender || 'N/A'}
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary">${patient.sdt || 'Không có SĐT'}</span>
                    </div>
                </div>
            </a>
        `).join('');
    }
    <?php endif; ?>
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>