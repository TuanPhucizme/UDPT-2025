<?php require_once '../app/views/layouts/header.php'; ?>

<!-- CSS TÙY CHỈNH CHO GIAO DIỆN NÀY -->
<style>
    body {
        background-color: #f8fafc;
    }
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        height: 100%;
    }
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }
    .form-step {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--bs-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    #patientResults {
        position: absolute;
        z-index: 1000;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        border-radius: 0.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .patient-item img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
    }
    .time-slot {
        border-radius: 0.5rem !important;
        transition: all 0.2s ease-in-out;
    }
    .time-slot.active {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(13,110,253,0.2);
    }
    .appointment-item {
        background-color: #f8fafc;
        border-left: 3px solid var(--bs-primary);
        padding: 0.75rem;
        border-radius: 0.5rem;
    }
</style>

<div class="container py-5">
    <div class="row g-4">
        <!-- CỘT CHÍNH: BIỂU MẪU ĐẶT LỊCH -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus text-primary me-2"></i> Tạo Lịch Hẹn Mới</h5>
                    <a href="/" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/appointments/create" novalidate>
                        <!-- BƯỚC 1: BỆNH NHÂN -->
                        <div class="mb-4">
                            <p class="form-step mb-2">Bước 1: Chọn Bệnh Nhân</p>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="searchPatient" placeholder="Nhập tên, SĐT hoặc mã bệnh nhân...">
                                <div id="patientResults" class="list-group mt-1" style="display:none;"></div>
                                <input type="hidden" name="patient_id" id="patient_id" required>
                            </div>
                        </div>

                        <div id="selectedPatient" class="mb-4" style="display:none;">
                            <div class="card bg-light">
                                <div class="card-body" id="patientInfo"></div>
                            </div>
                        </div>

                        <!-- BƯỚC 2: LỊCH HẸN -->
                        <div class="mb-3">
                            <p class="form-step mb-2">Bước 2: Chọn Lịch Hẹn</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Chuyên Khoa <span class="text-danger">*</span></label>
                                    <select class="form-select" name="department_id" id="department_id" required>
                                        <option value="">Chọn khoa...</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['ten_ck']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bác Sĩ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="doctor_id" id="doctor_id" required disabled>
                                        <option value="">Vui lòng chọn khoa trước</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Khung Giờ Có Thể Đặt</label>
                            <div id="timeSlots" class="d-flex flex-wrap gap-2">
                                <div class="text-muted small">Vui lòng chọn bác sĩ để xem khung giờ trống.</div>
                            </div>
                            <input type="datetime-local" name="requested_time" hidden>
                        </div>

                        <!-- BƯỚC 3: THÔNG TIN BỔ SUNG -->
                        <div class="mb-3">
                            <p class="form-step mb-2">Bước 3: Thông tin bổ sung</p>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Lý Do Khám <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="lydo" rows="2" required placeholder="Ví dụ: Tái khám, đau đầu..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ghi Chú</label>
                                    <textarea class="form-control" name="note" rows="2" placeholder="Ví dụ: Bệnh nhân có tiền sử dị ứng..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-calendar-check me-2"></i> Xác nhận Đặt Lịch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CỘT PHỤ: LỊCH BÁC SĨ -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-md text-primary me-2"></i> Lịch trống của Bác sĩ</h5>
                </div>
                <div class="card-body p-3" id="doctorSchedule">
                    <div class="text-center text-muted p-5">
                        <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                        <p>Vui lòng chọn một bác sĩ để xem lịch hẹn đã có.</p>
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
                    patientResults.innerHTML = `<div class="list-group-item"><div class="spinner-border spinner-border-sm"></div> Đang tìm...</div>`;
                    patientResults.style.display = 'block';
                    const response = await fetch(`/api/patients/search?term=${encodeURIComponent(searchTerm)}`);
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Search failed');
                    if (result.status === 'error') throw new Error(result.message);
                    renderPatientResults(result.data);
                } catch (error) {
                    patientResults.innerHTML = `<div class="list-group-item text-danger">${error.message}</div>`;
                }
            }, 300);
        });

        // Patient Selection
        patientResults.addEventListener('click', function(e) {
            if (e.target.closest('.patient-item')) {
                e.preventDefault();
                const item = e.target.closest('.patient-item');
                const patientData = JSON.parse(item.dataset.info);
                patientIdInput.value = patientData.id;
                patientInfo.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>${patientData.hoten_bn}</strong>
                            <div class="small text-muted">${calculateAge(patientData.dob)} tuổi | ${patientData.gender} | SĐT: ${patientData.sdt || 'N/A'}</div>
                        </div>
                    </div>`;
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
            patientResults.innerHTML = `<div class="list-group-item text-muted">Không tìm thấy</div>`;
            return;
        }
        patientResults.innerHTML = patients.map(p => `
            <a href="#" class="list-group-item list-group-item-action patient-item" data-info='${JSON.stringify(p)}'>
                <div class="d-flex align-items-center">
                    <div>
                        <strong>${p.hoten_bn}</strong>
                        <div class="small text-muted">${p.sdt || 'Không có SĐT'}</div>
                    </div>
                </div>
            </a>`).join('');
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

    function formatDateTime(datetimeStr) {
        const dt = new Date(datetimeStr);
        return `${dt.getHours().toString().padStart(2, '0')}:${dt.getMinutes().toString().padStart(2, '0')}`;
    }

    function formatSchedule(schedule) {
        if (!schedule.length) {
            return `<div class="text-center text-muted p-5">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p>Bác sĩ chưa có lịch hẹn nào trong hôm nay.</p>
                    </div>`;
        }
        return schedule.map(appt => `
            <div class="appointment-item mb-2">
                <div class="d-flex justify-content-between">
                    <strong>${appt.patient_name}</strong>
                    <span class="badge bg-${getStatusBadge(appt.status)}">${getStatusText(appt.status)}</span>
                </div>
                <div class="small text-muted">
                    <i class="fas fa-clock"></i> ${new Date(appt.thoi_gian_hen).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>`).join('');
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

    function renderTimeSlots(slots) {
        if (!Array.isArray(slots) || slots.length === 0) {
            timeSlotsDiv.innerHTML = `<div class="text-muted small">Bác sĩ không có khung giờ trống.</div>`;
            return;
        }
        timeSlotsDiv.innerHTML = slots.map(slot => `
            <button type="button" class="btn btn-outline-primary time-slot" data-time="${slot.datetime}">
                ${formatDateTime(slot.datetime)}
            </button>`).join('');
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>