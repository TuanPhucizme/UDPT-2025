/*import db from '../db.js';

export const createUser = async (username, hashedPassword, role) => {
  const [rows] = await db.query(
    'INSERT INTO users (username, password, role) VALUES (?, ?, ?)',
    [username, hashedPassword, role]
  );
  return rows;
};

export const getUserByUsername = async (username) => {
  const [rows] = await db.query(
    'SELECT * FROM users u join role r on u.role_id=r.id_role  WHERE username = ?',
    [username]
  );
  return rows[0];
};
*/

import db from '../db.js';

export const createUser = async ({
  username,
  hashedPassword,
  role,
  full_name,
  email,
  phone,
  gender,
  specialty
}) => {
  const [rows] = await db.query(
    `INSERT INTO users
       (username, password, role, full_name, email, phone, gender, specialty)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      username,
      hashedPassword,
      role,
      full_name || null,
      email || null,
      phone || null,
      gender || null,
      specialty || null
    ]
  );
  return rows;
};

export const getUserByUsername = async (username) => {
  const [rows] = await db.query(
    'SELECT * FROM users u join role r on u.role_id=r.id_role WHERE username = ?',
    [username]
  );
  return rows[0];
};

export const getUserByEmail = async (email) => {
  const [rows] = await db.query(
    'SELECT * FROM users WHERE email = ?',
    [email]
  );
  return rows[0];
};

export const getUserById = async (id) => {
  const [rows] = await db.query(
    'SELECT * FROM users WHERE id = ?',
    [id]
  );
  return rows[0];
};

export const getStaffById = async (id) => {
  const [rows] = await db.query(
    `SELECT 
      s.id,
      s.staff_code,
      s.hoten_nv,
      s.email,
      s.sdt,
      s.gender,
      s.dob,
      r.ten_role as role,
      d.ten_ck as department,
      d.id as department_id,
      s.begin_date
    FROM staff s
    JOIN role r ON s.role_id = r.id_role
    LEFT JOIN department d ON s.department_id = d.id
    WHERE s.id = ?`,
    [id]
  );
  return rows[0];
};

export const getDepartmentById = async (id) => {
  const [rows] = await db.query(
    'SELECT * FROM department WHERE id = ?',
    [id]
  );
  return rows[0];
};

export const getAllDepartments = async () => {
  const [rows] = await db.query(
    'SELECT * FROM department ORDER BY ten_ck'
  );
  return rows;
};

export const getStaffByDepartment = async (departmentId) => {
  const [rows] = await db.query(
    `SELECT 
      s.id,
      s.staff_code,
      s.hoten_nv,
      s.email,
      s.sdt,
      s.gender,
      r.ten_role as role
    FROM staff s
    JOIN role r ON s.role_id = r.id_role
    WHERE s.department_id = ?`,
    [departmentId]
  );
  return rows;
};
