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
    'SELECT * FROM users WHERE username = ?',
    [username]
  );
  return rows[0];
};
