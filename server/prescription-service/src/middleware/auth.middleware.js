import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
dotenv.config();

const JWT_SECRET = process.env.JWT_SECRET;
const INTERNAL_API_TOKEN = process.env.INTERNAL_API_TOKEN;

/**
 * Middleware xác thực JWT
 */
export const authMiddleware = (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Thiếu hoặc sai token' });
  }

  const token = authHeader.split(' ')[1];

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded; // { id, role, iat, exp }
    next();
  } catch (err) {
    res.status(401).json({ message: 'Token không hợp lệ' });
  }
};

/**
 * Middleware xác thực cho dịch vụ nội bộ
 */
export const internalAuthMiddleware = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  console.log('Internal API Token:', token);
  if (token !== INTERNAL_API_TOKEN) {
    return res.status(401).json({ message: 'Invalid internal API token' });
  }
  
  next();
};

/**
 * Middleware phân quyền theo role
 * @param  {...any} allowedRoles - các role được phép truy cập
 */
export const authorizeRoles = (...allowedRoles) => {
  const allowed = allowedRoles.map(r => String(r).toLowerCase().trim());

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
