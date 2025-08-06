<?php
require_once 'Model.php';

class User extends Model {
    public function __construct() {
        $this->attributes = [
            'id' => null,
            'username' => '',
            'email' => '',
            'password' => '',
            'role' => '',
            'fullName' => '',
            'phone' => '',
            'status' => 'active'
        ];
    }

    public function validate() {
        $this->errors = [];

        if (empty($this->attributes['username'])) {
            $this->errors['username'] = 'Tên đăng nhập không được để trống';
        }

        if (empty($this->attributes['email'])) {
            $this->errors['email'] = 'Email không được để trống';
        } elseif (!filter_var($this->attributes['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email không hợp lệ';
        }

        if (empty($this->attributes['password'])) {
            $this->errors['password'] = 'Mật khẩu không được để trống';
        } elseif (strlen($this->attributes['password']) < 6) {
            $this->errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        if (empty($this->attributes['fullName'])) {
            $this->errors['fullName'] = 'Họ tên không được để trống';
        }

        return empty($this->errors);
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'fullName' => $this->fullName,
            'phone' => $this->phone,
            'status' => $this->status
        ];
    }
}