import {
  getStaffById,
  getDepartmentById,
  getAllDepartments,
  getStaffByDepartment
} from '../models/user.model.js';

export const getStaff = async (req, res) => {
  try {
    const staffId = req.params.id;
    const staff = await getStaffById(staffId);

    if (!staff) {
      return res.status(404).json({ message: 'Không tìm thấy nhân viên' });
    }

    res.json(staff);
  } catch (error) {
    console.error('Error in getStaff:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy thông tin nhân viên', 
      error: error.message 
    });
  }
};

export const getDepartment = async (req, res) => {
  try {
    const departmentId = req.params.id;
    const department = await getDepartmentById(departmentId);

    if (!department) {
      return res.status(404).json({ message: 'Không tìm thấy khoa' });
    }

    res.json(department);
  } catch (error) {
    console.error('Error in getDepartment:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy thông tin khoa', 
      error: error.message 
    });
  }
};

export const listDepartments = async (req, res) => {
  try {
    const departments = await getAllDepartments();
    res.json(departments);
  } catch (error) {
    console.error('Error in listDepartments:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy danh sách khoa', 
      error: error.message 
    });
  }
};

export const getDepartmentStaff = async (req, res) => {
  try {
    const departmentId = req.params.id;
    const staff = await getStaffByDepartment(departmentId);

    if (staff.length === 0) {
      return res.status(404).json({ 
        message: 'Không tìm thấy bác sĩ trong khoa này' 
      });
    }

    // Remove sensitive information
    const sanitizedStaff = staff.map(({ password, ...doctor }) => doctor);
    res.json(sanitizedStaff);
  } catch (error) {
    console.error('Error in getDepartmentStaff:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy danh sách bác sĩ', 
      error: error.message 
    });
  }
};