<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đặt Lịch Khám</h5>
                    <a href="/appointments" class="btn btn-secondary btn-sm">Quay lại</a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/appointments/create" class="needs-validation" novalidate>
                        <!-- Search Patient -->
                        <div class="mb-4">
                            <label class="form-label">Tìm Bệnh Nhân</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchPatient" 
                                       placeholder="Tên hoặc SĐT...">
                            </div>
                            <div id="patientResults" class="list-group mt-2" style="display:none;">
                                <!-- Search results will be inserted here -->
                            </div>
                            <input type="hidden" name="patient_id" id="patient_id" required>
                        </div>

                        <!-- Selected Patient Info -->
                        <div id="selectedPatient" class="mb-4" style="display:none;">
                            <h6>Thông Tin Bệnh Nhân</h6>
                            <div class="card">
                                <div class="card-body" id="patientInfo">
                                    <!-- Patient info will be inserted here -->
                                </div>
                            </div>
                        </div>

                        <!-- Department Selection -->
                        <div class="mb-3">
                            <label class="form-label">Chọn Khoa <span class="text-danger">*</span></label>
                            <select class="form-select" name="department_id" id="department_id" required>
                                <option value="">Chọn khoa...</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>">
                                        <?= htmlspecialchars($dept['ten_ck']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Doctor Selection -->
                        <div class="mb-3">
                            <label class="form-label">Chọn Bác Sĩ <span class="text-danger">*</span></label>
                            <select class="form-select" name="doctor_id" id="doctor_id" required disabled>
                                <option value="">Chọn bác sĩ...</option>
                            </select>
                        </div>

                        <!-- Suggested Time Slots -->
                        <div class="mb-3">
                            <label class="form-label">Thời Gian Đề Xuất</label>
                            <div id="timeSlots" class="d-flex flex-wrap gap-2">
                                <!-- Time slots will be inserted here -->
                            </div>
                        </div>

                        <!-- Custom Time Selection -->
                        <div class="mb-3">
                            <label class="form-label">Hoặc Chọn Thời Gian Khác</label>
                            <input type="datetime-local" class="form-control" name="requested_time" 
                                   min="<?= date('Y-m-d\TH:i') ?>">
                        </div>

                        <!-- Reason for Visit -->
                        <div class="mb-3">
                            <label class="form-label">Lý Do Khám <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="lydo" rows="2" required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Tạo Lịch Hẹn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Doctor's Schedule -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lịch Bác Sĩ</h5>
                </div>
                <div class="card-body">
                    <div id="doctorSchedule">
                        <!-- Doctor's schedule will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchPatient = document.getElementById('searchPatient');
    const patientResults = document.getElementById('patientResults');
    const selectedPatient = document.getElementById('selectedPatient');
    const patientInfo = document.getElementById('patientInfo');
    const patientIdInput = document.getElementById('patient_id');
    const departmentSelect = document.getElementById('department_id');
    const doctorSelect = document.getElementById('doctor_id');
    const timeSlotsDiv = document.getElementById('timeSlots');
    const requestedTimeInput = document.querySelector('input[name="requested_time"]');
    
    // Patient Search with Debounce
    let searchTimeout;
    searchPatient.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        if (searchTerm.length < 2) {
            patientResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                patientResults.innerHTML = `
                    <div class="list-group-item">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span class="ms-2">Đang tìm kiếm...</span>
                    </div>`;
                patientResults.style.display = 'block';

                const response = await fetch(`/api/patients/search?term=${encodeURIComponent(searchTerm)}`);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Search failed');
                }
                
                if (result.status === 'error') {
                    throw new Error(result.message);
                }

                renderPatientResults(result.data);
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

    // Patient Selection
    patientResults.addEventListener('click', function(e) {
        if (e.target.closest('.patient-item')) {
            e.preventDefault();
            const item = e.target.closest('.patient-item');
            const patientData = JSON.parse(item.dataset.info);
            
            // Update hidden input and display selected patient info
            patientIdInput.value = patientData.id;
            patientInfo.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>${patientData.hoten_bn}</strong><br>
                        <small class="text-muted">
                            ${calculateAge(patientData.dob)} tuổi | ${patientData.gender}
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">SĐT: ${patientData.sdt || 'N/A'}</small><br>
                        <small class="text-muted">${patientData.diachi || 'Chưa có địa chỉ'}</small>
                    </div>
                </div>
            `;
            
            selectedPatient.style.display = 'block';
            patientResults.style.display = 'none';
            searchPatient.value = patientData.hoten_bn;
        }
    });

    // Department Change - Load Doctors
    departmentSelect.addEventListener('change', async function() {
        const deptId = this.value;
        doctorSelect.disabled = true;
        doctorSelect.innerHTML = '<option value="">Đang tải...</option>';
        timeSlotsDiv.innerHTML = '';
        
        if (!deptId) {
            doctorSelect.innerHTML = '<option value="">Chọn bác sĩ...</option>';
            return;
        }

        try {
            const response = await fetch(`/api/departments/${deptId}/doctors`);
            const doctors = await response.json();
            
            if (!response.ok) throw new Error('Failed to load doctors');
            
            renderDoctorOptions(doctors);
            doctorSelect.disabled = false;
        } catch (error) {
            console.error('Error:', error);
            doctorSelect.innerHTML = `
                <option value="">Lỗi tải danh sách bác sĩ</option>`;
        }
    });

    // Doctor Change - Load Schedule and Time Slots
    doctorSelect.addEventListener('change', async function() {
        const doctorId = this.value;
        if (!doctorId) return;

        const date = new Date().toISOString().split('T')[0];
        
        try {
            await Promise.all([
                loadDoctorSchedule(doctorId, date),
                loadAvailableSlots(doctorId, date)
            ]);
        } catch (error) {
            console.error('Error loading doctor data:', error);
        }
    });

    // Time Slot Selection
    timeSlotsDiv.addEventListener('click', function(e) {
        if (e.target.matches('.time-slot')) {
            const slots = this.querySelectorAll('.time-slot');
            slots.forEach(slot => slot.classList.remove('active'));
            e.target.classList.add('active');
            requestedTimeInput.value = e.target.dataset.time;
        }
    });

    // Helper Functions
    function calculateAge(dob) {
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

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
                            ${new Date(patient.dob).toLocaleDateString('vi-VN')} | 
                            ${patient.gender}
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary">${patient.sdt || 'Không có SĐT'}</span>
                    </div>
                </div>
            </a>
        `).join('');
    }

    function renderDoctorOptions(doctors) {
        doctorSelect.innerHTML = `
            <option value="">Chọn bác sĩ...</option>
            ${doctors.map(doctor => `
                <option value="${doctor.id}" data-info='${JSON.stringify(doctor)}'>
                    ${doctor.hoten_nv}
                    ${doctor.chuc_danh ? `(${doctor.chuc_danh})` : ''}
                </option>
            `).join('')}`;
    }

    async function loadDoctorSchedule(doctorId, date) {
        const scheduleDiv = document.getElementById('doctorSchedule');
        scheduleDiv.innerHTML = `
            <div class="text-center">
                <div class="spinner-border spinner-border-sm"></div>
                <div>Đang tải lịch...</div>
            </div>`;

        try {
            const response = await fetch(`/api/doctors/${doctorId}/schedule?date=${date}`);
            const schedule = await response.json();
            
            if (!response.ok) throw new Error('Failed to load schedule');
            
            scheduleDiv.innerHTML = formatSchedule(schedule);
        } catch (error) {
            console.error('Error:', error);
            scheduleDiv.innerHTML = `
                <div class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> Lỗi tải lịch khám
                </div>`;
        }
    }

    async function loadAvailableSlots(doctorId, date) {
        timeSlotsDiv.innerHTML = `
            <div class="spinner-border spinner-border-sm"></div>
            <span class="ms-2">Đang tải khung giờ...</span>`;

        try {
            const response = await fetch(`/api/doctors/${doctorId}/slots?date=${date}`);
            const slots = await response.json();
            
            if (!response.ok) throw new Error('Failed to load slots');
            
            renderTimeSlots(slots);
        } catch (error) {
            console.error('Error:', error);
            timeSlotsDiv.innerHTML = `
                <div class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> Lỗi tải khung giờ
                </div>`;
        }
    }

    function formatDateTime(datetime) {
        return new Date(datetime).toLocaleString('vi-VN', {
            weekday: 'short',
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatSchedule(schedule) {
        if (!schedule.length) {
            return '<div class="text-center text-muted">Không có lịch hẹn</div>';
        }

        return schedule.map(appt => `
            <div class="appointment-item mb-2 p-2 border-bottom">
                <div class="small text-muted">${formatDateTime(appt.thoi_gian_hen)}</div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>${appt.patient_name}</span>
                    <span class="badge bg-${getStatusBadge(appt.status)}">
                        ${getStatusText(appt.status)}
                    </span>
                </div>
            </div>
        `).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'confirmed': 'success',
            'pending': 'warning',
            'cancelled': 'danger',
            'completed': 'info'
        };
        return badges[status] || 'secondary';
    }

    function getStatusText(status) {
        const texts = {
            'confirmed': 'Đã xác nhận',
            'pending': 'Chờ duyệt',
            'cancelled': 'Đã hủy',
            'completed': 'Hoàn thành'
        };
        return texts[status] || status;
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>