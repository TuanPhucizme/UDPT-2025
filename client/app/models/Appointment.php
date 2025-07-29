<?php
class Appointment {
    private $id;
    private $patientId;
    private $doctorId;
    private $appointmentDate;
    private $status;
    private $notes;
    
    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->patientId = $data['patientId'] ?? null;
        $this->doctorId = $data['doctorId'] ?? null;
        $this->appointmentDate = $data['appointmentDate'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->notes = $data['notes'] ?? '';
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'patientId' => $this->patientId,
            'doctorId' => $this->doctorId,
            'appointmentDate' => $this->appointmentDate,
            'status' => $this->status,
            'notes' => $this->notes
        ];
    }
}