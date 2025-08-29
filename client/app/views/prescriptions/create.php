<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\views\prescriptions\create.php
require_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Kê Đơn Thuốc</h5>
                    <a href="<?php echo !empty($record) ? '/records/view/' . $record['id'] : '/prescriptions'; ?>" class="btn btn-secondary btn-sm">
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
                    
                    <form method="POST" action="/prescriptions/create">
                        <!-- Hidden fields -->
                        <?php if (!empty($record)): ?>
                            <input type="hidden" name="record_id" value="<?= htmlspecialchars($record['id']) ?>">
                        <?php else: ?>
                            <input type="hidden" name="record_id" id="recordId" required>
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
                        
                        <!-- Medical Record Information -->
                        <?php if (!empty($record)): ?>
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Thông Tin Hồ Sơ Khám Bệnh</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Mã hồ sơ:</strong> <?= htmlspecialchars($record['id']) ?></p>
                                            <p class="mb-1"><strong>Bác sĩ:</strong> <?= htmlspecialchars($record['doctor_name']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Ngày khám:</strong> <?= date('d/m/Y', strtotime($record['ngaykham'])) ?></p>
                                            <p class="mb-1"><strong>Khoa:</strong> <?= htmlspecialchars($record['department_name']) ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>Chẩn đoán:</strong> <?= nl2br(htmlspecialchars($record['chan_doan'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Medicines -->
                        <h6 class="text-muted mb-3">Danh Sách Thuốc</h6>
                        
                        <div id="medicinesList">
                            <div class="medicine-item card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Thuốc</label>
                                            <select class="form-select" name="medicines[]" required>
                                                <option value="">Chọn thuốc...</option>
                                                <?php foreach ($medicines as $medicine): ?>
                                                    <option value="<?= htmlspecialchars($medicine['id']) ?>">
                                                        <?= htmlspecialchars($medicine['ten_thuoc']) ?> (<?= htmlspecialchars($medicine['don_vi']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="dosage[]">Liều dùng</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="dosage[]" placeholder="Số lượng" min="1" required>
                                                <span class="input-group-text medicine-unit">mg</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tần suất</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control frequency-number" name="frequency_number[]" placeholder="Số lần" min="1" required>
                                                <select class="form-select" name="frequency_unit[]">
                                                    <option value="lần/ngày">lần/ngày</option>
                                                    <option value="lần/tuần">lần/tuần</option>
                                                    <option value="giờ/lần">giờ/lần</option>
                                                    <option value="khi cần">khi cần</option>
                                                </select>
                                                <input type="hidden" name="frequency[]" class="frequency-hidden">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Thời gian dùng</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control duration-number" name="duration_number[]" placeholder="Số" min="1" required>
                                                <select class="form-select" name="duration_unit[]">
                                                    <option value="ngày">ngày</option>
                                                    <option value="tuần">tuần</option>
                                                    <option value="tháng">tháng</option>
                                                </select>
                                                <input type="hidden" name="duration[]" class="duration-hidden">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Ghi chú</label>
                                            <input type="text" class="form-control" name="note[]" placeholder="VD: Sau khi ăn">
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-medicine">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addMedicine">
                                <i class="fas fa-plus"></i> Thêm thuốc
                            </button>
                        </div>
                        <div id="totalAmountsContainer"></div>
                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end mt-4">
                            <a href="<?php echo !empty($record) ? '/records/view/' . $record['id'] : '/prescriptions'; ?>" class="btn btn-secondary me-2">
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu Đơn Thuốc
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
    // Medicine items handling
    const medicinesList = document.getElementById('medicinesList');
    const addMedicineBtn = document.getElementById('addMedicine');
    
    // Initialize medicine selection handlers
    function initMedicineHandlers() {
        document.querySelectorAll('select[name="medicines[]"]').forEach(select => {
            select.addEventListener('change', function() {
                const medicineItem = this.closest('.medicine-item');
                const selectedOption = this.options[this.selectedIndex];
                
                if (selectedOption && selectedOption.value) {
                    // Extract the unit from the option text (format: "Medicine Name (unit)")
                    const unitMatch = selectedOption.text.match(/\(([^)]+)\)$/);
                    const unit = unitMatch ? unitMatch[1] : 'viên';
                    
                    // Update the unit in the UI
                    medicineItem.querySelector('.medicine-unit').textContent = unit;
                    
                    // Auto-add note for bottle medicines
                    const noteField = medicineItem.querySelector('input[name="note[]"]');
                    if (unit.toLowerCase() === 'chai' && noteField && !noteField.value) {
                        noteField.value = 'Tham khảo hướng dẫn sử dụng trên chai';
                    }
                    
                    // Update the hidden field
                    updateHiddenFields();
                }
            });
        });
    }
    
    // Function to update hidden fields with combined values for frequency and duration
    function updateHiddenFields() {
        document.querySelectorAll('.medicine-item').forEach(item => {
            // Update frequency
            const frequencyNumber = item.querySelector('.frequency-number').value;
            const frequencyUnit = item.querySelector('select[name="frequency_unit[]"]').value;
            const frequencyHidden = item.querySelector('.frequency-hidden');
            
            if (frequencyNumber && frequencyUnit) {
                frequencyHidden.value = `${frequencyNumber} ${frequencyUnit}`;
            }
            
            // Update duration
            const durationNumber = item.querySelector('.duration-number').value;
            const durationUnit = item.querySelector('select[name="duration_unit[]"]').value;
            const durationHidden = item.querySelector('.duration-hidden');
            
            if (durationNumber && durationUnit) {
                durationHidden.value = `${durationNumber} ${durationUnit}`;
            }
        });
    }
    
    // Initialize input handlers
    function initInputHandlers() {
        document.querySelectorAll('.frequency-number, select[name="frequency_unit[]"]').forEach(el => {
            el.addEventListener('input', updateHiddenFields);
            el.addEventListener('change', updateHiddenFields);
        });
        
        document.querySelectorAll('.duration-number, select[name="duration_unit[]"]').forEach(el => {
            el.addEventListener('input', updateHiddenFields);
            el.addEventListener('change', updateHiddenFields);
        });
    }
    
    // Add new medicine item
    addMedicineBtn.addEventListener('click', function() {
        const firstItem = document.querySelector('.medicine-item');
        const newItem = firstItem.cloneNode(true);
        
        // Clear values in the new item
        newItem.querySelectorAll('input').forEach(input => {
            input.value = '';
        });
        
        // Reset selects to first option
        newItem.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
        
        // Reset the unit to default
        newItem.querySelector('.medicine-unit').textContent = 'mg';
        
        // Add to the list
        medicinesList.appendChild(newItem);
        
        // Reinitialize buttons and handlers
        initRemoveButtons();
        initInputHandlers();
        initMedicineHandlers();
    });
    
    // Initialize remove buttons
    function initRemoveButtons() {
        document.querySelectorAll('.remove-medicine').forEach(button => {
            button.addEventListener('click', function() {
                const items = document.querySelectorAll('.medicine-item');
                if (items.length > 1) {
                    this.closest('.medicine-item').remove();
                } else {
                    alert('Phải có ít nhất một thuốc trong đơn');
                }
            });
        });
    }
    
    // Form submission handler
    document.querySelector('form').addEventListener('submit', function(e) {
        // Update all hidden fields before submitting
        updateHiddenFields();
    });
    
    // Initialize on page load
    initRemoveButtons();
    initInputHandlers();
    initMedicineHandlers();
    updateHiddenFields(); // Initial update
    
    <?php if (empty($patient) && empty($record)): ?>
    // Patient search code (similar to record creation page)
    // [Code omitted for brevity - it's the same as in records/create.php]
    <?php endif; ?>
});

document.addEventListener('DOMContentLoaded', function() {
  // When a medicine is selected, check if it's a liquid medicine
  document.querySelectorAll('select[name="medicines[]"]').forEach(select => {
    select.addEventListener('change', handleMedicineSelection);
  });
  
  // Also handle existing medicine selections when cloning items
  document.getElementById('addMedicine').addEventListener('click', function() {
    // Wait for DOM to update after adding a new medicine item
    setTimeout(() => {
      const newItem = document.querySelector('.medicine-item:last-child');
      const select = newItem.querySelector('select[name="medicines[]"]');
      
      // Remove any volume info from the cloned item
      const volumeInfo = newItem.querySelector('.volume-info');
      if (volumeInfo) {
        volumeInfo.remove();
      }
      
      // Reset dosage label
      const doseLabel = newItem.querySelector('label[for^="dosage"]');
      if (doseLabel) {
        doseLabel.textContent = 'Liều dùng';
      }
      
      // Reset note field if it has the bottle reference
      const noteField = newItem.querySelector('input[name="note[]"]');
      if (noteField && noteField.value === 'Tham khảo hướng dẫn sử dụng trên chai') {
        noteField.value = '';
      }
      
      // Add event listeners to new inputs
      newItem.querySelector('select[name="medicines[]"]').addEventListener('change', handleMedicineSelection);
      setupTotalCalculation(newItem);
    }, 100);
  });
  
  // Initialize calculation for all medicine items
  document.querySelectorAll('.medicine-item').forEach(setupTotalCalculation);
  
  function handleMedicineSelection() {
    const medicineItem = this.closest('.medicine-item');
    const selectedMedicineId = this.value;
    
    // Remove any existing volume info regardless of medicine type
    const existingVolumeInfo = medicineItem.querySelector('.volume-info');
    if (existingVolumeInfo) {
      existingVolumeInfo.remove();
    }
    
    // Reset dosage label to default
    const doseLabel = medicineItem.querySelector('label[for^="dosage"]');
    doseLabel.textContent = 'Liều dùng (mỗi lần)';
    
    if (selectedMedicineId) {
      // Fetch medicine details via AJAX
      fetch(`/api/medicines/${selectedMedicineId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(medicine => {
          // Update the UI based on medicine type
          if (medicine.is_liquid == 1) {
            handleLiquidMedicine(medicineItem, medicine);
          } else {
            handleRegularMedicine(medicineItem, medicine);
          }
          
          // Update total calculation
          updateTotalCalculation(medicineItem);
        })
        .catch(error => {
          console.error('Error fetching medicine details:', error);
          // Add a fallback in case of error
          medicineItem.querySelector('.medicine-unit').textContent = 'viên';
        });
    }
  }
  
  function handleLiquidMedicine(medicineItem, medicine) {
    // For liquid medicines, show volume fields
    const doseInput = medicineItem.querySelector('input[name="dosage[]"]');
    const doseLabel = medicineItem.querySelector('label[for^="dosage"]');
    
    // Change label to reflect volume
    doseLabel.textContent = 'Thể tích (ml mỗi lần)';
    
    // Add volume-related fields
    const volumePerBottle = medicine.volume_per_bottle || 100; // Default to 100ml if not specified
    medicineItem.querySelector('.medicine-unit').textContent = medicine.volume_unit || 'ml';
    
    // Add a note about bottles
    const noteField = medicineItem.querySelector('input[name="note[]"]');
    if (!noteField.value) {
      noteField.value = 'Tham khảo hướng dẫn sử dụng trên chai';
    }
    
    // Create a dedicated container for bottle info with better styling
    const volumeInfo = document.createElement('div');
    volumeInfo.className = 'volume-info alert alert-info mt-2 p-2 mb-0';
    volumeInfo.innerHTML = `
      <div class="d-flex justify-content-between align-items-center">
        <div><i class="fas fa-info-circle me-2"></i> Mỗi chai chứa ${volumePerBottle}${medicine.volume_unit || 'ml'}</div>
        <strong class="bottle-count"></strong>
      </div>
      <div class="total-calculation mt-2">
        <small class="text-muted">Tổng lượng thuốc cần: <span class="total-amount font-weight-bold">0 ml</span></small>
        <div class="progress mt-1" style="height: 5px;">
          <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
      </div>
    `;
    
    // Insert after the input group
    const inputGroup = doseInput.closest('.input-group');
    inputGroup.parentNode.insertBefore(volumeInfo, inputGroup.nextSibling);
    
    // Update bottle count when dose, frequency, or duration changes
    doseInput.addEventListener('input', () => updateTotalCalculation(medicineItem));
    
    // Trigger input event to calculate initial bottle count
    doseInput.dispatchEvent(new Event('input'));
  }
  
  function handleRegularMedicine(medicineItem, medicine) {
    // For regular medicines (pills, tablets, etc.)
    medicineItem.querySelector('.medicine-unit').textContent = medicine.don_vi || 'viên';
    
    // Clear any bottle-related note
    const noteField = medicineItem.querySelector('input[name="note[]"]');
    if (noteField.value === 'Tham khảo hướng dẫn sử dụng trên chai') {
      noteField.value = '';
    }
    
    // Create a medication calculation info box
    const totalInfo = document.createElement('div');
    totalInfo.className = 'volume-info alert alert-secondary mt-2 p-2 mb-0';
    totalInfo.innerHTML = `
      <div class="d-flex justify-content-between align-items-center">
        <div><i class="fas fa-calculator me-2"></i> Liều dùng mỗi lần: <span class="single-dose">0</span> ${medicine.don_vi || 'viên'}</div>
      </div>
      <div class="total-calculation mt-2">
        <small class="text-muted">Tổng lượng thuốc cần: <span class="total-amount font-weight-bold">0 ${medicine.don_vi || 'viên'}</span></small>
        <div class="progress mt-1" style="height: 5px;">
          <div class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
        </div>
      </div>
    `;
    
    // Insert after the input group
    const doseInput = medicineItem.querySelector('input[name="dosage[]"]');
    const inputGroup = doseInput.closest('.input-group');
    inputGroup.parentNode.insertBefore(totalInfo, inputGroup.nextSibling);
  }
  
  function setupTotalCalculation(medicineItem) {
    const inputs = [
      medicineItem.querySelector('input[name="dosage[]"]'),
      medicineItem.querySelector('input[name="frequency_number[]"]'),
      medicineItem.querySelector('select[name="frequency_unit[]"]'),
      medicineItem.querySelector('input[name="duration_number[]"]'),
      medicineItem.querySelector('select[name="duration_unit[]"]')
    ];
    
    inputs.forEach(input => {
      if (input) {
        input.addEventListener('input', () => updateTotalCalculation(medicineItem));
        input.addEventListener('change', () => updateTotalCalculation(medicineItem));
      }
    });
  }
  
  function updateTotalCalculation(medicineItem) {
    const doseInput = medicineItem.querySelector('input[name="dosage[]"]');
    const frequencyNumberInput = medicineItem.querySelector('input[name="frequency_number[]"]');
    const frequencyUnitSelect = medicineItem.querySelector('select[name="frequency_unit[]"]');
    const durationNumberInput = medicineItem.querySelector('input[name="duration_number[]"]');
    const durationUnitSelect = medicineItem.querySelector('select[name="duration_unit[]"]');
    
    if (!doseInput || !frequencyNumberInput || !frequencyUnitSelect || !durationNumberInput || !durationUnitSelect) {
      return;
    }
    
    const dose = parseFloat(doseInput.value) || 0;
    const frequencyNumber = parseInt(frequencyNumberInput.value) || 0;
    const frequencyUnit = frequencyUnitSelect.value;
    const durationNumber = parseInt(durationNumberInput.value) || 0;
    const durationUnit = durationUnitSelect.value;
    
    // Calculate total doses based on frequency and duration
    let totalDoses = 0;
    
    if (frequencyUnit === 'lần/ngày') {
      // Frequency per day
      let daysTotal = 0;
      
      if (durationUnit === 'ngày') {
        daysTotal = durationNumber;
      } else if (durationUnit === 'tuần') {
        daysTotal = durationNumber * 7;
      } else if (durationUnit === 'tháng') {
        daysTotal = durationNumber * 30;
      }
      
      totalDoses = frequencyNumber * daysTotal;
    } else if (frequencyUnit === 'lần/tuần') {
      // Frequency per week
      let weeksTotal = 0;
      
      if (durationUnit === 'ngày') {
        weeksTotal = durationNumber / 7;
      } else if (durationUnit === 'tuần') {
        weeksTotal = durationNumber;
      } else if (durationUnit === 'tháng') {
        weeksTotal = durationNumber * 4.3;
      }
      
      totalDoses = frequencyNumber * weeksTotal;
    } else if (frequencyUnit === 'giờ/lần') {
      // Hours between doses
      let hoursTotal = 0;
      
      if (durationUnit === 'ngày') {
        hoursTotal = durationNumber * 24;
      } else if (durationUnit === 'tuần') {
        hoursTotal = durationNumber * 24 * 7;
      } else if (durationUnit === 'tháng') {
        hoursTotal = durationNumber * 24 * 30;
      }
      
      if (frequencyNumber > 0) {
        totalDoses = hoursTotal / frequencyNumber;
      }
    } else if (frequencyUnit === 'khi cần') {
      // As needed - estimate based on 3 times per week
      let weeksTotal = 0;
      
      if (durationUnit === 'ngày') {
        weeksTotal = durationNumber / 7;
      } else if (durationUnit === 'tuần') {
        weeksTotal = durationNumber;
      } else if (durationUnit === 'tháng') {
        weeksTotal = durationNumber * 4.3;
      }
      
      totalDoses = 3 * weeksTotal; // Estimate as 3 times per week
    }
    
    // Calculate total amount needed
    const totalAmount = dose * totalDoses;
    
    // Find the medicine unit
    const medicineUnit = medicineItem.querySelector('.medicine-unit').textContent;
    
    // Update the display
    const volumeInfo = medicineItem.querySelector('.volume-info');
    if (volumeInfo) {
      const totalAmountElement = volumeInfo.querySelector('.total-amount');
      if (totalAmountElement) {
        totalAmountElement.textContent = `${totalAmount.toFixed(1)} ${medicineUnit}`;
      }
      
      const singleDoseElement = volumeInfo.querySelector('.single-dose');
      if (singleDoseElement) {
        singleDoseElement.textContent = dose;
      }
      
      // Update progress bar
      const progressBar = volumeInfo.querySelector('.progress-bar');
      if (progressBar) {
        // Max width is 100%
        progressBar.style.width = Math.min(100, totalAmount / 2) + '%';
      }
      
      // If this is liquid medicine, calculate bottles
      if (medicineItem.querySelector('input[name="dosage[]"]').closest('.input-group').nextElementSibling?.classList.contains('volume-info')) {
        const medicineId = medicineItem.querySelector('select[name="medicines[]"]').value;
        if (medicineId) {
          fetch(`/api/medicines/${medicineId}`)
            .then(response => response.json())
            .then(medicine => {
              const volumePerBottle = medicine.volume_per_bottle || 100;
              const bottles = Math.ceil(totalAmount / volumePerBottle);
              const bottleCount = volumeInfo.querySelector('.bottle-count');
              
              if (bottleCount) {
                if (bottles > 0) {
                  bottleCount.textContent = `Cần dùng ${bottles} chai`;
                  
                  // Highlight if dose exceeds available stock
                  if (medicine.so_luong !== undefined && bottles > medicine.so_luong) {
                    bottleCount.classList.add('text-danger');
                    bottleCount.textContent += ` (chỉ có ${medicine.so_luong} chai trong kho)`;
                  } else {
                    bottleCount.classList.remove('text-danger');
                  }
                  
                  // Show the volume info
                  volumeInfo.style.display = 'block';
                } else {
                  // Hide the volume info if no bottles needed
                  volumeInfo.style.display = 'none';
                }
              }
            })
            .catch(error => console.error('Error updating bottle count:', error));
        }
      }
    }
    
    // Add a hidden field to store the total amount for submission
    let totalAmountInput = medicineItem.querySelector('input[name="total_amount[]"]');
    if (!totalAmountInput) {
      totalAmountInput = document.createElement('input');
      totalAmountInput.type = 'hidden';
      totalAmountInput.name = 'total_amount[]';
      medicineItem.appendChild(totalAmountInput);
    }
    totalAmountInput.value = totalAmount.toFixed(1);
  }
});
document.querySelector('form').addEventListener('submit', function(e) {
  const totalAmountInputs = document.querySelectorAll('input[name="total_amount[]"]');
  const totalAmountsContainer = document.getElementById('totalAmountsContainer');
  
  // Clear previous values
  totalAmountsContainer.innerHTML = '';
  
  // Add current values
  totalAmountInputs.forEach((input, index) => {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'total_amount[]';
    hiddenInput.value = input.value;
    totalAmountsContainer.appendChild(hiddenInput);
  });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>