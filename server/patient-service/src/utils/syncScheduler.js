import cron from 'node-cron';
import { processSyncQueue } from './syncProcessor.js';
import { initSyncQueue } from '../models/sync_queue.model.js';

// Initialize the sync queue table when the server starts
export const initializeSyncSystem = async () => {
  try {
    // Create the sync_queue table if it doesn't exist
    await initSyncQueue();
    
    // Schedule sync processing every 2 minutes
    cron.schedule('*/2 * * * *', async () => {
      console.log('Running scheduled sync queue processing...');
      await processSyncQueue();
    });
    
    console.log('Sync system initialized');
  } catch (err) {
    console.error('Failed to initialize sync system:', err);
  }
};

export default {
  initializeSyncSystem
};
