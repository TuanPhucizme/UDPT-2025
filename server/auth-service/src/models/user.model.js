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
    'SELECT * FROM users WHERE username = ?',
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
