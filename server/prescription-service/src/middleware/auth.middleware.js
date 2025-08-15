import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
dotenv.config();

const JWT_SECRET = process.env.JWT_SECRET || 'super-secret-key';

/**
 * Xác thực JWT từ Authorization header
 */
export const authMiddleware = (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer '))
    return res.status(401).json({ message: 'Thiếu hoặc sai token' });

  const token = authHeader.split(' ')[1];

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded; // { id, role, iat, exp }
    console.log('[auth] user from token:', req.user); // 👈 kiểm tra ở console
    next();
  } catch (err) {
    res.status(401).json({ message: 'Token không hợp lệ' });
  }
};

/*// ✅ Phân quyền theo vai trò
export const authorizeRoles = (...roles) => {
  return (req, res, next) => {
    if (!req.user || !roles.includes(req.user.role)) {
      return res.status(403).json({ message: 'Cấm truy cập: Bạn không có quyền.' });
    }
    next();
  };
};*/
export const authorizeRoles = (...roles) => {
  // chuẩn hóa danh sách role cho phép
  const allowed = roles.map(r => String(r).toLowerCase().trim());

  return (req, res, next) => {
    const userRole = String(req.user?.role ?? '').toLowerCase().trim();
    if (!userRole || !allowed.includes(userRole)) {
      return res.status(403).json({
        message: `Cấm truy cập: cần role [${allowed.join(', ')}], nhưng token có role='${req.user?.role ?? ''}'`
      });
    }
    next();
  };
};
