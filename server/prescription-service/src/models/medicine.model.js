import db from '../db.js';
import services from '../config/services.js';
import { serviceCall } from '../utils/serviceCall.js';

export const getAllMedicinesFromDB = async (search = '', stock_status = '') => {
    try {
        let query = `
            SELECT 
                m.*,
                DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                DATE_FORMAT(m.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
            FROM medicines m
            WHERE 1=1
        `;
        
        const params = [];
        
        // Add search filter
        if (search) {
            query += ` AND m.ten_thuoc LIKE ?`;
            params.push(`%${search}%`);
        }
        
        // Add stock status filter
        if (stock_status === 'low') {
            query += ` AND m.so_luong <= 10 AND m.so_luong > 0`;
        } else if (stock_status === 'out') {
            query += ` AND m.so_luong <= 0`;
        } else if (stock_status === 'liquid') {
            query += ` AND m.is_liquid = 1`;
        }
        
        query += ` ORDER BY m.ten_thuoc ASC`;
        
        const [rows] = await db.query(query, params);
        return rows;
    } catch (error) {
        console.error('Error in getAllMedicinesFromDB:', error);
        throw error;
    }
};

export const getMedicineByIdFromDB = async (id) => {
    try {
        const [rows] = await db.query(
            `SELECT 
                m.*,
                DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                DATE_FORMAT(m.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
            FROM medicines m
            WHERE m.id = ?`,
            [id]
        );
        
        if (!rows.length) {
            return null;
        }
        
        return rows[0];
    } catch (error) {
        console.error('Error in getMedicineByIdFromDB:', error);
        throw error;
    }
};

export const createMedicineInDB = async (data) => {
    const {
        ten_thuoc,
        don_vi,
        don_gia,
        so_luong,
        is_liquid,
        volume_per_bottle,
        volume_unit,
        created_by
    } = data;
    
    const conn = await db.getConnection();
    
    try {
        await conn.beginTransaction();
        
        // Insert the medicine
        const [result] = await conn.query(
            `INSERT INTO medicines (
                ten_thuoc, don_vi, don_gia, so_luong, 
                is_liquid, volume_per_bottle, volume_unit
            ) VALUES (?, ?, ?, ?, ?, ?, ?)`,
            [ten_thuoc, don_vi, don_gia, so_luong, is_liquid, volume_per_bottle, volume_unit]
        );
        
        const medicineId = result.insertId;
        
        // Log the initial stock if provided
        if (so_luong > 0) {
            await conn.query(
                `INSERT INTO medicine_stock_log (
                    medicine_id, action_type, quantity_change, 
                    bottles_used, volume_used, note, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)`,
                [
                    medicineId,
                    'purchase',
                    so_luong,
                    is_liquid ? so_luong : null,
                    is_liquid ? so_luong * volume_per_bottle : null,
                    'Initial stock',
                    created_by
                ]
            );
        }
        
        await conn.commit();
        
        // Get the created medicine
        const newMedicine = await getMedicineByIdFromDB(medicineId);
        return newMedicine;
    } catch (error) {
        await conn.rollback();
        console.error('Error in createMedicineInDB:', error);
        throw error;
    } finally {
        conn.release();
    }
};

export const updateMedicineInDB = async (id, data) => {
    const {
        ten_thuoc,
        don_vi,
        don_gia,
        is_liquid,
        volume_per_bottle,
        volume_unit,
        updated_by
    } = data;
    
    try {
        // Check if medicine exists
        const medicine = await getMedicineByIdFromDB(id);
        if (!medicine) {
            return null;
        }
        
        // Update the medicine
        await db.query(
            `UPDATE medicines SET
                ten_thuoc = ?,
                don_vi = ?,
                don_gia = ?,
                is_liquid = ?,
                volume_per_bottle = ?,
                volume_unit = ?,
                updated_at = NOW()
            WHERE id = ?`,
            [ten_thuoc, don_vi, don_gia, is_liquid, volume_per_bottle, volume_unit, id]
        );
        
        // Get the updated medicine
        const updatedMedicine = await getMedicineByIdFromDB(id);
        return updatedMedicine;
    } catch (error) {
        console.error('Error in updateMedicineInDB:', error);
        throw error;
    }
};

export const updateMedicineStockInDB = async (id, data) => {
    const {
        quantity,
        action_type,
        note,
        created_by
    } = data;
    
    const conn = await db.getConnection();
    
    try {
        await conn.beginTransaction();
        
        // Get current medicine details
        const [medicineRows] = await conn.query(
            `SELECT 
                id, ten_thuoc, so_luong, is_liquid, 
                volume_per_bottle, volume_unit 
            FROM medicines 
            WHERE id = ?`,
            [id]
        );
        
        if (!medicineRows.length) {
            return null;
        }
        
        const medicine = medicineRows[0];
        let newQuantity = medicine.so_luong;
        let quantityChange = 0;
        
        // Calculate the new quantity based on action type
        switch (action_type) {
            case 'purchase':
                newQuantity += quantity;
                quantityChange = quantity;
                break;
            case 'adjustment':
                quantityChange = quantity - medicine.so_luong;
                newQuantity = quantity;
                break;
            case 'return':
                newQuantity = Math.max(0, medicine.so_luong - quantity);
                quantityChange = -Math.min(quantity, medicine.so_luong);
                break;
            default:
                throw new Error('Invalid action type');
        }
        
        // Update the medicine stock
        await conn.query(
            `UPDATE medicines SET so_luong = ?, updated_at = NOW() WHERE id = ?`,
            [newQuantity, id]
        );
        
        // Calculate volume for liquid medicines
        let volumeUsed = null;
        if (medicine.is_liquid && medicine.volume_per_bottle) {
            volumeUsed = Math.abs(quantityChange) * medicine.volume_per_bottle;
        }
        
        // Log the stock change
        await conn.query(
            `INSERT INTO medicine_stock_log (
                medicine_id, action_type, quantity_change, 
                bottles_used, volume_used, note, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?)`,
            [
                id,
                action_type,
                quantityChange,
                medicine.is_liquid ? Math.abs(quantityChange) : null,
                volumeUsed,
                note || `${action_type} stock adjustment`,
                created_by
            ]
        );
        
        await conn.commit();
        
        // Get the updated medicine
        const updatedMedicine = await getMedicineByIdFromDB(id);
        return updatedMedicine;
    } catch (error) {
        await conn.rollback();
        console.error('Error in updateMedicineStockInDB:', error);
        throw error;
    } finally {
        conn.release();
    }
};

export const getMedicineStockHistoryFromDB = async (id) => {
    try {
        // First check if the medicine exists
        const medicine = await getMedicineByIdFromDB(id);
        if (!medicine) {
            return [];
        }
        
        // Get the stock history
        const [rows] = await db.query(
            `SELECT 
                msl.*,
                DATE_FORMAT(msl.created_at, '%Y-%m-%d %H:%i:%s') as created_at
            FROM medicine_stock_log msl
            WHERE msl.medicine_id = ?
            ORDER BY msl.created_at DESC`,
            [id]
        );
        
        // Enrich with user information
        const enrichedHistory = await Promise.all(rows.map(async (record) => {
            if (record.created_by) {
                try {
                    const user = await serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${record.created_by}`);
                    if (user) {
                        record.created_by_name = user.hoten_nv;
                    }
                } catch (error) {
                    console.error(`Error fetching user info for ID ${record.created_by}:`, error);
                }
            }
            return record;
        }));
        
        return enrichedHistory;
    } catch (error) {
        console.error('Error in getMedicineStockHistoryFromDB:', error);
        throw error;
    }
};

export const getLiquidMedicinesReportFromDB = async () => {
    try {
        // Get all liquid medicines
        const [medicines] = await db.query(
            `SELECT 
                m.*,
                DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                DATE_FORMAT(m.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
            FROM medicines m
            WHERE m.is_liquid = 1
            ORDER BY m.ten_thuoc ASC`
        );
        
        if (!medicines.length) {
            return [];
        }
        
        // Get this month's usage for each medicine
        const currentMonth = new Date().getMonth() + 1; // JS months are 0-indexed
        const currentYear = new Date().getFullYear();
        
        const startDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
        const nextMonth = currentMonth === 12 ? 1 : currentMonth + 1;
        const nextYear = currentMonth === 12 ? currentYear + 1 : currentYear;
        const endDate = `${nextYear}-${nextMonth.toString().padStart(2, '0')}-01`;
        
        // For each medicine, calculate total volume used this month
        const enrichedMedicines = await Promise.all(medicines.map(async (medicine) => {
            try {
                const [usageRows] = await db.query(
                    `SELECT 
                        COALESCE(SUM(volume_used), 0) as total_volume_used
                    FROM medicine_stock_log
                    WHERE medicine_id = ? 
                      AND action_type = 'dispense'
                      AND created_at >= ? 
                      AND created_at < ?`,
                    [medicine.id, startDate, endDate]
                );
                
                return {
                    ...medicine,
                    volume_used: usageRows[0].total_volume_used || 0
                };
            } catch (error) {
                console.error(`Error calculating usage for medicine ${medicine.id}:`, error);
                return {
                    ...medicine,
                    volume_used: 0
                };
            }
        }));
        console.log(enrichedMedicines);
        return enrichedMedicines;
    } catch (error) {
        console.error('Error in getLiquidMedicinesReportFromDB:', error);
        throw error;
    }
};