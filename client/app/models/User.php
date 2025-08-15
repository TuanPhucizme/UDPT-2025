<?php
require_once 'Model.php';

class User extends Model {
    public function __construct() {
        $this->attributes = [
            'id' => null,
            'username' => '',
            'password' => '',
            'role' => '',
            'status' => 'active'
        ];
    }

    public function validate() {
        $this->errors = [];

        if (empty($this->attributes['username'])) {
            $this->errors['username'] = 'Tên đăng nhập không được để trống';
        }

        if (empty($this->attributes['password'])) {
            $this->errors['password'] = 'Mật khẩu không được để trống';
        } elseif (strlen($this->attributes['password']) < 4) {
            $this->errors['password'] = 'Mật khẩu phải có ít nhất 4 ký tự';
        }

        return empty($this->errors);
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'status' => $this->status
        ];
    }
}