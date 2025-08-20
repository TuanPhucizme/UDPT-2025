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

/**
 * Liệt kê người dùng theo 1 hoặc nhiều role, có tìm kiếm & phân trang
 * @param {Object} params
 * @param {string[]} params.roles - danh sách vai trò cần lấy
 * @param {string} [params.q] - từ khoá tìm kiếm (full_name/username/email/phone)
 * @param {string} [params.specialty] - lọc chuyên khoa (chỉ áp dụng doctor)
 * @param {number} [params.page=1]
 * @param {number} [params.limit=20]
 * @returns {{data:any[], page:number, limit:number, total:number}}
 */
export const listUsers = async ({ roles, q, specialty, page = 1, limit = 20 }) => {
  if (!Array.isArray(roles) || roles.length === 0) {
    throw new Error('roles is required');
  }

  const where = [];
  const args = [];

  // role IN (...)
  where.push(`role IN (${roles.map(() => '?').join(',')})`);
  args.push(...roles);

  // keyword
  if (q) {
    where.push(`(
      COALESCE(full_name,'') LIKE ? OR
      COALESCE(username,'')  LIKE ? OR
      COALESCE(email,'')     LIKE ? OR
      COALESCE(phone,'')     LIKE ?
    )`);
    const kw = `%${q}%`;
    args.push(kw, kw, kw, kw);
  }

  // specialty (chỉ khi có doctor trong roles)
  if (specialty && roles.includes('doctor')) {
    where.push(`COALESCE(specialty,'') LIKE ?`);
    args.push(`%${specialty}%`);
  }

  const whereSql = where.length ? `WHERE ${where.join(' AND ')}` : '';
  const offset = Math.max(0, (Number(page) - 1) * Number(limit));
  const safeLimit = Math.min(Math.max(Number(limit) || 20, 1), 100);

  // total
  const [cntRows] = await db.query(
    `SELECT COUNT(*) AS total FROM users ${whereSql}`,
    args
  );
  const total = cntRows[0]?.total ?? 0;

  // data
  const [rows] = await db.query(
    `SELECT id, username, role, full_name, email, phone, gender, specialty, created_at
     FROM users
     ${whereSql}
     ORDER BY created_at DESC
     LIMIT ? OFFSET ?`,
    [...args, safeLimit, offset]
  );

  return { data: rows, page: Number(page) || 1, limit: safeLimit, total };
};