import db from '../db.js';

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
