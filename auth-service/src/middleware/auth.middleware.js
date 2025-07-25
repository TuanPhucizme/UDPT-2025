import jwt from 'jsonwebtoken';
const JWT_SECRET = process.env.JWT_SECRET;

/**
 * Middleware xác thực JWT
 */
export const authMiddleware = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ message: 'Thiếu token' });

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded;
    next();
  } catch (err) {
    return res.status(401).json({ message: 'Token không hợp lệ' });
  }
};

/**
 * Middleware phân quyền theo role
 * @param  {...any} allowedRoles - các role được phép truy cập
 */
export const authorizeRoles = (...allowedRoles) => {
  return (req, res, next) => {
    if (!req.user || !allowedRoles.includes(req.user.role)) {
      return res.status(403).json({ message: 'Cấm truy cập. Vai trò không hợp lệ.' });
    }
    next();
  };
};
