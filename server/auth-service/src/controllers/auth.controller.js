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
      staff_code,
      hoten_nv,
      email,
      sdt,
      gender,
      dob,
      department_id,
      begin_date
    } = req.body;

    // Basic validation
    if (!username || !password) {
      return res.status(400).json({ message: 'Thiếu username hoặc password' });
    }

    // Validate role
    const validRoles = ['bacsi', 'duocsi', 'letan', 'admin'];
    if (!validRoles.includes(role)) {
      return res.status(400).json({ message: 'Role không hợp lệ' });
    }

    // Check if username exists
    const existingUser = await getUserByUsername(username);
    if (existingUser) {
      return res.status(400).json({ message: 'Username đã tồn tại' });
    }

    // Start transaction
    const connection = await db.getConnection();
    await connection.beginTransaction();

    try {
      // 1. Create user account
      const hashedPassword = await bcrypt.hash(password, 10);
      const [userResult] = await connection.query(
        'INSERT INTO users (username, password, role_id) VALUES (?, ?, (SELECT id_role FROM role WHERE ten_role = ?))',
        [username, hashedPassword, role]
      );

      // 2. Create staff record if role is not patient
      if (role !== 'benhnhan') {
        if (!staff_code || !hoten_nv || !email || !sdt || !gender) {
          throw new Error('Thiếu thông tin nhân viên');
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          throw new Error('Email không hợp lệ');
        }

        // Validate phone format
        const phoneRegex = /^[0-9]{10,11}$/;
        if (!phoneRegex.test(sdt)) {
          throw new Error('Số điện thoại không hợp lệ');
        }

        // Check if staff_code exists
        const [existingStaff] = await connection.query(
          'SELECT id FROM staff WHERE staff_code = ?',
          [staff_code]
        );
        if (existingStaff.length > 0) {
          throw new Error('Mã nhân viên đã tồn tại');
        }

        // Create staff record
        await connection.query(
          `INSERT INTO staff (
            staff_code,
            hoten_nv,
            email,
            sdt,
            gender,
            dob,
            role_id,
            department_id,
            begin_date,
            created_at
          ) VALUES (?, ?, ?, ?, ?, ?, (SELECT id_role FROM role WHERE ten_role = ?), ?, ?, NOW())`,
          [
            staff_code,
            hoten_nv,
            email,
            sdt,
            gender,
            dob || null,
            role,
            department_id || null,
            begin_date || new Date()
          ]
        );
      }

      await connection.commit();
      res.status(201).json({ 
        message: 'Tạo tài khoản thành công',
        userId: userResult.insertId
      });

    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }

  } catch (err) {
    console.error('[auth] register error:', err);
    res.status(500).json({ 
      message: 'Lỗi đăng ký', 
      error: err.message 
    });
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
      { id: user.id, role: user.ten_role, name: user.hoten_nv },
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