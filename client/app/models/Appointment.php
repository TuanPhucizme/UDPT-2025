<?php
class Appointment {
    private $id;
    private $patient_id;
    private $doctor_id;
    private $department_id;
    private $requested_time;
    private $proposed_time;
    private $confirmed_time;
    private $status;
    private $lydo;
    private $decline_reason;
    private $receptionist_id;
    private $created_at;
    private $updated_at;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->patient_id = $data['patient_id'] ?? null;
        $this->doctor_id = $data['doctor_id'] ?? null;
        $this->department_id = $data['department_id'] ?? null;
        $this->requested_time = $data['requested_time'] ?? null;
        $this->proposed_time = $data['proposed_time'] ?? null;
        $this->confirmed_time = $data['confirmed_time'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->lydo = $data['lydo'] ?? null;
        $this->decline_reason = $data['decline_reason'] ?? null;
        $this->receptionist_id = $data['receptionist_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'department_id' => $this->department_id,
            'requested_time' => $this->requested_time,
            'proposed_time' => $this->proposed_time,
            'confirmed_time' => $this->confirmed_time,
            'status' => $this->status,
            'lydo' => $this->lydo,
            'decline_reason' => $this->decline_reason,
            'receptionist_id' => $this->receptionist_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}