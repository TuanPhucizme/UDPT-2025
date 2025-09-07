// For server-side NodeJS services
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\server\prescription-service\src\routes\health.routes.js

import express from 'express';
import db from '../db.js';
import { checkServiceHealth, checkMultipleServices, getSystemHealth } from '../utils/healthCheck.js';

const router = express.Router();

// Define dependent services - update with your actual service URLs
const dependentServices = {
  patient: process.env.PATIENT_SERVICE_URL,
  auth: process.env.AUTH_SERVICE_URL
  // Add other dependent services as needed
};

// Simple health check endpoint
router.get('/', async (req, res) => {
    try {
        // Basic DB connection check
        await db.query('SELECT 1');
        
        // Get system health metrics
        const systemHealth = getSystemHealth();
        
        // Check if we need to verify dependent services
        const checkDependencies = req.query.dependencies === 'true';
        
        let servicesHealth = { status: 'not_checked' };
        if (checkDependencies) {
            // Check health of dependent services
            servicesHealth = await checkMultipleServices(dependentServices);
        }
        
        // Calculate overall status
        const overallStatus = checkDependencies && servicesHealth.status !== 'ok' 
            ? 'degraded'  // Some dependencies are unhealthy
            : 'ok';       // Either dependencies are healthy or we didn't check them
        
        res.json({
            status: overallStatus,
            service: 'appointment-service', // Update with your service name
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            database: { connected: true },
            system: systemHealth,
            dependencies: checkDependencies ? servicesHealth.services : undefined
        });
    } catch (error) {
        console.error('Health check failed:', error);
        res.status(503).json({
            status: 'error',
            message: 'Service is not healthy',
            details: error.message
        });
    }
});

// Detailed health check with all dependency checks
router.get('/detailed', async (req, res) => {
    try {
        // Perform DB check with timeout
        let dbStatus = { status: 'ok', connected: true };
        try {
            await Promise.race([
                db.query('SELECT 1'),
                new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('Database query timeout')), 3000)
                )
            ]);
        } catch (dbError) {
            dbStatus = { 
                status: 'error', 
                connected: false, 
                message: dbError.message 
            };
        }
        
        // Always check dependencies in detailed view
        const servicesHealth = await checkMultipleServices(dependentServices);
        
        // Get system health with more details
        const systemHealth = getSystemHealth();
        
        // Determine overall status
        let overallStatus = 'ok';
        if (dbStatus.status !== 'ok') {
            overallStatus = 'critical'; // DB is essential
        } else if (servicesHealth.status !== 'ok') {
            overallStatus = 'degraded'; // Some dependencies are unhealthy
        }
        
        res.json({
            status: overallStatus,
            service: 'appointment-service', // Update with your service name
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            version: process.env.npm_package_version || 'unknown',
            database: dbStatus,
            system: systemHealth,
            dependencies: servicesHealth.services
        });
    } catch (error) {
        console.error('Detailed health check failed:', error);
        res.status(503).json({
            status: 'error',
            message: 'Service is not healthy',
            details: error.message
        });
    }
});

// Specific database health check
router.get('/database', async (req, res) => {
    try {
        const startTime = process.hrtime();
        await db.query('SELECT 1');
        const diff = process.hrtime(startTime);
        const responseTimeMs = (diff[0] * 1e9 + diff[1]) / 1e6;
        
        res.json({
            status: 'ok',
            responseTime: `${responseTimeMs.toFixed(2)}ms`,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        console.error('Database health check failed:', error);
        res.status(503).json({
            status: 'error',
            message: 'Database is not healthy',
            details: error.message
        });
    }
});

export default router;