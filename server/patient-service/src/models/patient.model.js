import db from '../db.js';

export const createPatient = async ({ name, age, gender, phone, email }) => {
  const [rows] = await db.query(
    'INSERT INTO patients (name, age, gender, phone, email) VALUES (?, ?, ?, ?, ?)',
    [name, age, gender, phone, email]
  );
  return rows;
};

export const getAllPatients = async (filters = {}) => {
  let sql = 'SELECT * FROM patients WHERE 1=1';
  const params = [];

  if (filters.name) {
    sql += ' AND hoten_bn LIKE ?';
    params.push(`%${filters.name}%`);
  }
  if (filters.gender) {
    sql += ' AND gender = ?';
    params.push(filters.gender);
  }
  if (filters.age) {
    sql += ' AND age = ?';
    params.push(filters.age);
  }

  const [rows] = await db.query(sql, params);
  return rows;
};


export const getPatientById = async (id) => {
  const [rows] = await db.query('SELECT * FROM patients WHERE id = ?', [id]);
  return rows[0];
};

export const updatePatient = async (id, data) => {
  const { name, age, gender, phone, email } = data;
  const [rows] = await db.query(
    `UPDATE patients SET name=?, age=?, gender=?, phone=?, email=? WHERE id=?`,
    [name, age, gender, phone, email, id]
  );
  return rows;
};

