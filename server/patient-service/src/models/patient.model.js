import db from '../db.js';

export const createPatient = async (data) => {
  const { 
    hoten_bn, 
    dob, 
    gender, 
    sdt, 
    diachi, 
    tiensu_benh, 
    lichsu_kham 
  } = data;

  const [result] = await db.query(
    `INSERT INTO patients (
      hoten_bn, 
      dob, 
      gender, 
      sdt, 
      diachi, 
      tiensu_benh, 
      lichsu_kham, 
      created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())`,
    [hoten_bn, dob, gender, sdt, diachi, tiensu_benh, lichsu_kham]
  );

  return result;
};

export const getAllPatients = async (filters = {}) => {
  let sql = 'SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age FROM patients WHERE 1=1';
  const params = [];

  if (filters.name) {
    sql += ' AND hoten_bn LIKE ?';
    params.push(`%${filters.name}%`);
  }
  if (filters.gender) {
    sql += ' AND gender = ?';
    params.push(filters.gender);
  }
  if (filters.phone) {
    sql += ' AND sdt LIKE ?';
    params.push(`%${filters.phone}%`);
  }
  if (filters.age) {
    sql += ' HAVING age = ?';
    params.push(parseInt(filters.age));
  }

  sql += ' ORDER BY hoten_bn ASC';

  const [rows] = await db.query(sql, params);
  return rows;
};


export const getPatientById = async (id) => {
  const [rows] = await db.query('SELECT * FROM patients WHERE id = ?', [id]);
  return rows[0];
};

export const updatePatient = async (id, data) => {
  const { hoten_bn, dob, gender, diachi, sdt } = data;
  const [rows] = await db.query(
    `UPDATE patients SET hoten_bn=?, dob=?, gender=?, sdt=?, diachi=? WHERE id=?`,
    [hoten_bn, dob, gender, sdt, diachi, id]
  );
  return rows;
};

