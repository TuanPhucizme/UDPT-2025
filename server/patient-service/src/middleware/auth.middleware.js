import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
dotenv.config();

const JWT_SECRET = process.env.JWT_SECRET;
const INTERNAL_API_TOKEN = process.env.INTERNAL_API_TOKEN;

/**
 * Middleware xác thực JWT cho client requests
 */
export const authMiddleware = (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ message: 'Thiếu hoặc sai token' });
  }

  const token = authHeader.split(' ')[1];

  // Check if it's an internal API call first
  if (token === INTERNAL_API_TOKEN) {
    console.log('Internal API call authenticated');
    req.isInternalRequest = true;
    return next();
  }

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded; // { id, role, iat, exp }
    req.isInternalRequest = false;
    next();
  } catch (err) {
    res.status(401).json({ message: 'Token không hợp lệ' });
  }
};

/**
 * Middleware phân quyền theo role (chỉ áp dụng cho client requests)
 */
export const authorizeRoles = (...allowedRoles) => {
  const allowed = allowedRoles.map(r => String(r).toLowerCase().trim());

  return (req, res, next) => {
    // Skip role check for internal requests
    if (req.isInternalRequest) {
      return next();
    }

    const userRole = String(req.user?.role ?? '').toLowerCase().trim();
    if (!userRole || !allowed.includes(userRole)) {
      return res.status(403).json({
        message: `Cấm truy cập: cần role [${allowed.join(', ')}], nhưng token có role='${req.user?.role ?? ''}'`
      });
    }
    next();
  };
};
