import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { createUser, getUserByUsername } from '../models/user.model.js';

const JWT_SECRET = process.env.JWT_SECRET;

export const register = async (req, res) => {
  const { username, password, role } = req.body;

  const user = await getUserByUsername(username);
  if (user) return res.status(400).json({ message: 'Username đã tồn tại' });

  const hashed = await bcrypt.hash(password, 10);
  await createUser(username, hashed, role || 'user');

  res.status(201).json({ message: 'Tạo tài khoản thành công' });
};

export const login = async (req, res) => {
  const { username, password } = req.body;

  const user = await getUserByUsername(username);
  if (!user) return res.status(401).json({ message: 'Sai tài khoản' });

  const match = await bcrypt.compare(password, user.password);
  if (!match) return res.status(401).json({ message: 'Sai mật khẩu' });

  const token = jwt.sign(
    { id: user.id, role: user.role },
    JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN }
  );

  res.json({ token });
};
