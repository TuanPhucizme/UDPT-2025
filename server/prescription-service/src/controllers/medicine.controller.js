import { 
    getAllMedicinesFromDB, 
    getMedicineByIdFromDB,
    createMedicineInDB,
    updateMedicineInDB,
    updateMedicineStockInDB,
    getMedicineStockHistoryFromDB,
    getLiquidMedicinesReportFromDB
} from '../models/medicine.model.js';

export const getAllMedicines = async (req, res) => {
    try {
        const { search, stock_status } = req.query;
        const medicines = await getAllMedicinesFromDB(search, stock_status);
        console.log(medicines);
        res.json(medicines);
    } catch (error) {
        console.error('Error in getAllMedicines:', error);
        res.status(500).json({ 
            message: 'Error retrieving medicines', 
            error: error.message 
        });
    }
};

export const getMedicineById = async (req, res) => {
    try {
        const { id } = req.params;
        const medicine = await getMedicineByIdFromDB(id);
        
        if (!medicine) {
            return res.status(404).json({ message: 'Medicine not found' });
        }
        
        res.json(medicine);
    } catch (error) {
        console.error('Error in getMedicineById:', error);
        res.status(500).json({ 
            message: 'Error retrieving medicine', 
            error: error.message 
        });
    }
};

export const createMedicine = async (req, res) => {
    try {
        const { 
            ten_thuoc, 
            don_vi, 
            don_gia, 
            so_luong,
            is_liquid,
            volume_per_bottle,
            volume_unit
        } = req.body;
        
        // Validate required fields
        if (!ten_thuoc || !don_vi || !don_gia || typeof so_luong !== 'number') {
            return res.status(400).json({ 
                message: 'Missing required fields' 
            });
        }
        
        // Validate liquid medicine fields
        if (is_liquid && (!volume_per_bottle || !volume_unit)) {
            return res.status(400).json({ 
                message: 'For liquid medicines, volume_per_bottle and volume_unit are required' 
            });
        }
        
        const newMedicine = await createMedicineInDB({
            ten_thuoc, 
            don_vi, 
            don_gia, 
            so_luong,
            is_liquid: is_liquid ? 1 : 0,
            volume_per_bottle,
            volume_unit,
            created_by: req.user?.id
        });
        
        res.status(201).json({ 
            message: 'Medicine created successfully', 
            data: newMedicine 
        });
    } catch (error) {
        console.error('Error in createMedicine:', error);
        res.status(500).json({ 
            message: 'Error creating medicine', 
            error: error.message 
        });
    }
};

export const updateMedicine = async (req, res) => {
    try {
        const { id } = req.params;
        const { 
            ten_thuoc, 
            don_vi, 
            don_gia,
            is_liquid,
            volume_per_bottle,
            volume_unit
        } = req.body;
        
        // Validate required fields
        if (!ten_thuoc || !don_vi || !don_gia) {
            return res.status(400).json({ 
                message: 'Missing required fields' 
            });
        }
        
        // Validate liquid medicine fields
        if (is_liquid && (!volume_per_bottle || !volume_unit)) {
            return res.status(400).json({ 
                message: 'For liquid medicines, volume_per_bottle and volume_unit are required' 
            });
        }
        
        const updatedMedicine = await updateMedicineInDB(id, {
            ten_thuoc, 
            don_vi, 
            don_gia,
            is_liquid: is_liquid ? 1 : 0,
            volume_per_bottle,
            volume_unit,
            updated_by: req.user?.id
        });
        
        if (!updatedMedicine) {
            return res.status(404).json({ message: 'Medicine not found' });
        }
        
        res.json({ 
            message: 'Medicine updated successfully', 
            data: updatedMedicine 
        });
    } catch (error) {
        console.error('Error in updateMedicine:', error);
        res.status(500).json({ 
            message: 'Error updating medicine', 
            error: error.message 
        });
    }
};

export const updateMedicineStock = async (req, res) => {
    try {
        const { id } = req.params;
        const { quantity, action_type, note } = req.body;
        
        // Validate required fields
        if (typeof quantity !== 'number' || !action_type) {
            return res.status(400).json({ 
                message: 'Missing required fields' 
            });
        }
        
        // Validate action type
        if (!['purchase', 'adjustment', 'return'].includes(action_type)) {
            return res.status(400).json({ 
                message: 'Invalid action_type. Must be purchase, adjustment, or return' 
            });
        }
        
        const result = await updateMedicineStockInDB(id, {
            quantity,
            action_type,
            note,
            created_by: req.user?.id
        });
        
        if (!result) {
            return res.status(404).json({ message: 'Medicine not found' });
        }
        
        res.json({ 
            message: 'Medicine stock updated successfully', 
            data: result 
        });
    } catch (error) {
        console.error('Error in updateMedicineStock:', error);
        res.status(500).json({ 
            message: 'Error updating medicine stock', 
            error: error.message 
        });
    }
};

export const getMedicineStockHistory = async (req, res) => {
    try {
        const { id } = req.params;
        const history = await getMedicineStockHistoryFromDB(id);
        
        res.json(history);
    } catch (error) {
        console.error('Error in getMedicineStockHistory:', error);
        res.status(500).json({ 
            message: 'Error retrieving medicine stock history', 
            error: error.message 
        });
    }
};

export const getLiquidMedicinesReport = async (req, res) => {
    try {
        const report = await getLiquidMedicinesReportFromDB();
        
        res.json(report);
    } catch (error) {
        console.error('Error in getLiquidMedicinesReport:', error);
        res.status(500).json({ 
            message: 'Error retrieving liquid medicines report', 
            error: error.message 
        });
    }
};