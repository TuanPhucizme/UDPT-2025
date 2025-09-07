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
  
  // Admin mode for internal access (no phone encoding)
  const isAdminMode = filters.adminMode === true;

  const validSortBy = ['id', 'hoten_bn', 'dob', 'created_at'];
  const sortBy = validSortBy.includes(filters.sortBy) ? filters.sortBy : 'id';
  const sortOrder = filters.sortOrder === 'desc' ? 'DESC' : 'ASC';

  sql += ` ORDER BY ${sortBy} ${sortOrder}`;

  const [rows] = await db.query(sql, params);
  
  // Apply phone number encoding if not in admin mode
  if (!isAdminMode) {
    rows.forEach(patient => {
      if (patient.sdt && patient.sdt.length > 4) {
        // Encode the phone number - show only last 3 digits
        patient.sdt = encodePhoneNumber(patient.sdt);
      }
    });
  }
  
  return rows;
};


export const getPatientById = async (id, options = {}) => {
  const [rows] = await db.query('SELECT * FROM patients WHERE id = ?', [id]);
  const patient = rows[0];
  
  if (patient && !options.adminMode && patient.sdt) {
    // Encode phone number for non-admin requests
    patient.sdt = encodePhoneNumber(patient.sdt);
  }
  
  return patient;
};

// Function to encode phone numbers for privacy
function encodePhoneNumber(phone) {
  if (!phone) return '';
  if (phone.length <= 4) return phone; // Don't encode very short numbers
  
  // Keep last 3 digits visible, mask the rest with 'x'
  return 'x'.repeat(phone.length - 3) + phone.substring(phone.length - 3);
}

export const updatePatient = async (id, data) => {
  const { hoten_bn, dob, gender, diachi, sdt } = data;
  
  // Check if the phone number is encoded (starts with 'x' characters)
  const isPhoneEncoded = sdt && sdt.match(/^x+\d{3}$/);
  
  if (isPhoneEncoded) {
    // If phone is encoded, don't update it - keep the original
    const [rows] = await db.query(
      `UPDATE patients SET hoten_bn=?, dob=?, gender=?, diachi=? WHERE id=?`,
      [hoten_bn, dob, gender, diachi, id]
    );
    return rows;
  } else {
    // If phone is not encoded (either new or changed), update it along with other fields
    const [rows] = await db.query(
      `UPDATE patients SET hoten_bn=?, dob=?, gender=?, sdt=?, diachi=? WHERE id=?`,
      [hoten_bn, dob, gender, sdt, diachi, id]
    );
    return rows;
  }
};

