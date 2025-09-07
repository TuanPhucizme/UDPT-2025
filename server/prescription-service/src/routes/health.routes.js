// For server-side NodeJS services
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\server\prescription-service\src\routes\health.routes.js

import express from 'express';
import db from '../db.js';
import { checkServiceHealth, checkMultipleServices, getSystemHealth } from '../utils/healthCheck.js';

const router = express.Router();

// Define dependent services
const dependentServices = {
  patient: process.env.PATIENT_SERVICE_URL || 'http://localhost:3001',
  auth: process.env.AUTH_SERVICE_URL || 'http://localhost:3000'
};

// Simple health check endpoint with improved error handling
router.get('/', async (req, res) => {
    try {
        // Wrap database check in a timeout to prevent hanging
        const dbCheck = async () => {
            return new Promise(async (resolve, reject) => {
                // Set a timeout to prevent hanging on DB connection issues
                const timeout = setTimeout(() => {
                    reject(new Error('Database connection timeout'));
                }, 2000);
                
                try {
                    await db.query('SELECT 1');
                    clearTimeout(timeout);
                    resolve(true);
                } catch (error) {
                    clearTimeout(timeout);
                    reject(error);
                }
            });
        };
        
        // Try database check
        let dbConnected = false;
        try {
            await dbCheck();
            dbConnected = true;
        } catch (dbError) {
            console.error('Database health check failed:', dbError.message);
            // Don't throw - we'll report this in the health status
        }
        
        // Get system health metrics
        const systemHealth = getSystemHealth();
        
        // Determine status based on database connectivity
        const status = dbConnected ? 'ok' : 'degraded';
        
        res.json({
            status: status,
            service: 'prescription-service',
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            database: { 
                connected: dbConnected,
                status: dbConnected ? 'ok' : 'error'
            },
            system: systemHealth
        });
    } catch (error) {
        // Instead of letting the error propagate to Express's default handler,
        // catch it and return a degraded status
        console.error('Health check failed:', error);
        res.status(200).json({
            status: 'degraded',
            service: 'prescription-service',
            timestamp: new Date().toISOString(),
            error: error.message,
            database: { connected: false, status: 'unknown' }
        });
    }
});

// Detailed health check with all dependency checks
router.get('/detailed', async (req, res) => {
    try {
        // Perform DB check with timeout
        let dbStatus = { status: 'unknown', connected: false };
        try {
            await Promise.race([
                db.query('SELECT 1'),
                new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('Database query timeout')), 3000)
                )
            ]);
            dbStatus = { status: 'ok', connected: true };
        } catch (dbError) {
            dbStatus = { 
                status: 'error', 
                connected: false, 
                message: dbError.message 
            };
        }
        
        // Check dependencies
        let servicesHealth;
        try {
            servicesHealth = await checkMultipleServices(dependentServices);
        } catch (error) {
            servicesHealth = {
                status: 'error',
                message: error.message,
                services: {}
            };
        }
        
        // Get system health
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
            service: 'prescription-service',
            timestamp: new Date().toISOString(),
            uptime: process.uptime(),
            database: dbStatus,
            system: systemHealth,
            dependencies: servicesHealth.services || {}
        });
    } catch (error) {
        // Return a degraded status instead of an error
        res.status(200).json({
            status: 'degraded',
            service: 'prescription-service',
            timestamp: new Date().toISOString(),
            error: error.message
        });
    }
});

// Always respond to /health/ready with 200 OK
// This is useful for Kubernetes readiness probe
router.get('/ready', (req, res) => {
    res.status(200).json({
        status: 'ok',
        service: 'prescription-service',
        ready: true,
        timestamp: new Date().toISOString()
    });
});

export default router;