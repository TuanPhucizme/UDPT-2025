/*import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { createUser, getUserByUsername } from '../models/user.model.js';

const JWT_SECRET = process.env.JWT_SECRET;

export const register = async (req, res) => {
  const { username, password, role } = req.body;

  const user = await getUserByUsername(username);
  if (user) return res.status(400).json({ message: 'Username đã tồn tại' });

  const hashed = await bcrypt.hash(password, 10);
  await createUser(username, hashed, role || 'user');

  res.status(201).json({ message: 'Tạo tài khoản thành công' });
};

export const login = async (req, res) => {
  const { username, password } = req.body;

  const user = await getUserByUsername(username);
  if (!user) return res.status(401).json({ message: 'Sai tài khoản' });

  const match = await bcrypt.compare(password, user.password);
  if (!match) return res.status(401).json({ message: 'Sai mật khẩu' });

  const token = jwt.sign(
    { id: user.id, role: user.ten_role,fullName: user.username },
    JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN }
  );
  res.json({ token });
};
*/

import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { createUser, getUserByUsername, getUserByEmail } from '../models/user.model.js';
import { listUsers } from '../models/user.model.js';
const JWT_SECRET = process.env.JWT_SECRET;

const allowedRoles = ['admin', 'doctor', 'receptionist', 'pharmacist', 'patient', 'user'];
// Helper: parse query chung
function parseQuery(req) {
  const { page = '1', limit = '20', q = '', specialty = '' } = req.query || {};
  return {
    page: Number(page) || 1,
    limit: Number(limit) || 20,
    q: q || undefined,
    specialty: specialty || undefined
  };
}
export const register = async (req, res) => {
  try {
    const {
      username,
      password,
      role,
      full_name,
      email,
      phone,
      gender,     // 'male' | 'female' | 'other'
      specialty   // bắt buộc nếu role === 'doctor'
    } = req.body;

    if (!username || !password) {
      return res.status(400).json({ message: 'Thiếu username hoặc password' });
    }

    const safeRole = allowedRoles.includes(role) ? role : 'user';
    if (safeRole === 'doctor' && !specialty) {
      return res.status(400).json({ message: 'Bác sĩ phải có chuyên khoa (specialty)' });
    }

    if (email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) return res.status(400).json({ message: 'Email không hợp lệ' });

      const existedEmail = await getUserByEmail(email);
      if (existedEmail) return res.status(400).json({ message: 'Email đã được sử dụng' });
    }

    if (phone) {
      const phoneRegex = /^[0-9+\-\s()]{6,20}$/;
      if (!phoneRegex.test(phone)) return res.status(400).json({ message: 'Số điện thoại không hợp lệ' });
    }

    const existedUser = await getUserByUsername(username);
    if (existedUser) return res.status(400).json({ message: 'Username đã tồn tại' });

    const hashed = await bcrypt.hash(password, 10);

    await createUser({
      username,
      hashedPassword: hashed,
      role: safeRole,
      full_name,
      email,
      phone,
      gender,
      specialty
    });

    res.status(201).json({ message: 'Tạo tài khoản thành công' });
  } catch (err) {
    console.error('[auth] register error:', err);
    res.status(500).json({ message: 'Lỗi đăng ký', error: err.message });
  }
};

export const login = async (req, res) => {
  try {
    const { username, password } = req.body;

    const user = await getUserByUsername(username);
    if (!user) return res.status(401).json({ message: 'Sai tài khoản' });

    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.status(401).json({ message: 'Sai mật khẩu' });

    const token = jwt.sign(
      { id: user.id, role: user.ten_role, name: user.username },
      JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
    );

    res.json({ token });
  } catch (err) {
    console.error('[auth] login error:', err);
    res.status(500).json({ message: 'Lỗi đăng nhập', error: err.message });
  }
};

// GET /users/doctors
export const getDoctors = async (req, res) => {
  try {
    const { page, limit, q, specialty } = parseQuery(req);
    const result = await listUsers({ roles: ['doctor'], q, specialty, page, limit });
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách bác sĩ', error: err.message });
  }
};

// GET /users/pharmacists
export const getPharmacists = async (req, res) => {
  try {
    const { page, limit, q } = parseQuery(req);
    const result = await listUsers({ roles: ['pharmacist'], q, page, limit });
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách dược sĩ', error: err.message });
  }
};

// GET /users/receptionists
export const getReceptionists = async (req, res) => {
  try {
    const { page, limit, q } = parseQuery(req);
    const result = await listUsers({ roles: ['receptionist'], q, page, limit });
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách lễ tân', error: err.message });
  }
};

// GET /users/patients
export const getPatients = async (req, res) => {
  try {
    const { page, limit, q } = parseQuery(req);
    const result = await listUsers({ roles: ['patient'], q, page, limit });
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách bệnh nhân', error: err.message });
  }
};