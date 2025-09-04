import express from 'express';
import { 
    getAllMedicines,
    getMedicineById,
    createMedicine,
    updateMedicine,
    updateMedicineStock,
    getMedicineStockHistory,
    getLiquidMedicinesReport
} from '../controllers/medicine.controller.js';
import { authMiddleware,authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Get all medicines
router.get('/', authMiddleware, getAllMedicines);

// Get medicine by ID
router.get('/:id', authMiddleware, getMedicineById);

// Create new medicine
router.post('/', authMiddleware, authorizeRoles('duocsi', 'admin'), createMedicine);

// Update medicine
router.put('/:id', authMiddleware, authorizeRoles('duocsi', 'admin'), updateMedicine);

// Update medicine stock
router.put('/:id/stock', authMiddleware, authorizeRoles('duocsi', 'admin'), updateMedicineStock);

// Get medicine stock history
router.get('/:id/stockHistory', authMiddleware, getMedicineStockHistory);

// Get liquid medicines report
router.get('/reports/liquid-medicines', authMiddleware, getLiquidMedicinesReport);

export default router;