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
                                <button type="button" class="btn btn-outline-secondary" id="searchPatientBtn">
                                    <i class="fas fa-search"></i>
                                </button>
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
    const departmentSelect = document.getElementById('department_id');
    const doctorSelect = document.getElementById('doctor_id');
    
    // Patient Search
    let searchTimeout;
    searchPatient.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.trim();
            if (searchTerm.length >= 2) {
                fetch(`/api/patients/search?term=${encodeURIComponent(searchTerm)}`)
                    .then(res => res.json())
                    .then(data => {
                        patientResults.innerHTML = data.map(patient => `
                            <a href="#" class="list-group-item list-group-item-action" 
                               data-id="${patient.id}" 
                               data-info='${JSON.stringify(patient)}'>
                                ${patient.hoten_bn} - ${patient.sdt || 'Không có SĐT'}
                            </a>
                        `).join('');
                        patientResults.style.display = 'block';
                    });
            } else {
                patientResults.style.display = 'none';
            }
        }, 300);
    });

    // Department Change
    departmentSelect.addEventListener('change', function() {
        const deptId = this.value;
        if (deptId) {
            fetch(`/api/departments/${deptId}/doctors`)
                .then(res => res.json())
                .then(doctors => {
                    doctorSelect.innerHTML = '<option value="">Chọn bác sĩ...</option>' +
                        doctors.map(doc => `
                            <option value="${doc.id}">${doc.hoten_nv}</option>
                        `).join('');
                    doctorSelect.disabled = false;
                });
        } else {
            doctorSelect.innerHTML = '<option value="">Chọn bác sĩ...</option>';
            doctorSelect.disabled = true;
        }
    });

    // Doctor Change - Show Schedule
    doctorSelect.addEventListener('change', function() {
        const doctorId = this.value;
        if (doctorId) {
            updateDoctorSchedule(doctorId);
            updateSuggestedTimeSlots(doctorId);
        }
    });

    function updateDoctorSchedule(doctorId) {
        const scheduleDiv = document.getElementById('doctorSchedule');
        scheduleDiv.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

        fetch(`/api/doctors/${doctorId}/schedule`)
            .then(res => res.json())
            .then(schedule => {
                scheduleDiv.innerHTML = formatSchedule(schedule);
            });
    }

    function updateSuggestedTimeSlots(doctorId) {
        const timeSlotsDiv = document.getElementById('timeSlots');
        fetch(`/api/doctors/${doctorId}/available-slots`)
            .then(res => res.json())
            .then(slots => {
                timeSlotsDiv.innerHTML = slots.map(slot => `
                    <button type="button" class="btn btn-outline-primary btn-sm time-slot"
                            data-time="${slot.datetime}">
                        ${formatDateTime(slot.datetime)}
                    </button>
                `).join('');
            });
    }

    // Helper Functions
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
        // Format the schedule data into a readable view
        return schedule.map(appt => `
            <div class="appointment-item mb-2 p-2 border-bottom">
                <div class="small text-muted">${formatDateTime(appt.thoi_gian_hen)}</div>
                <div>${appt.patient_name}</div>
                <div class="badge bg-${getStatusBadge(appt.status)}">${appt.status}</div>
            </div>
        `).join('') || '<div class="text-center text-muted">Không có lịch hẹn</div>';
    }

    function getStatusBadge(status) {
        switch(status) {
            case 'confirmed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>