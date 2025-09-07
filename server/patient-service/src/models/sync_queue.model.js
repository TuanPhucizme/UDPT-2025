import db from '../db.js';

/**
 * Creates a sync queue table if it doesn't exist
 * This table will store operations that need to be synced later when services are back online
 */
export const initSyncQueue = async () => {
  try {
    await db.query(`
      CREATE TABLE IF NOT EXISTS sync_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        payload JSON NOT NULL,
        status ENUM('pending','processing','success','failed') DEFAULT 'pending',
        retry_count INT DEFAULT 0,
        last_error TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      )
    `);
    console.log('Sync queue table initialized');
  } catch (err) {
    console.error('Failed to initialize sync queue table:', err);
  }
};

/**
 * Add an operation to the sync queue
 * @param {string} type - Type of operation (e.g., 'create_patient', 'update_record')
 * @param {object} payload - Data needed to perform the operation later
 */
export const addToSyncQueue = async (type, payload) => {
  try {
    const [result] = await db.query(
      'INSERT INTO sync_queue (type, payload) VALUES (?, ?)',
      [type, JSON.stringify(payload)]
    );
    return result.insertId;
  } catch (err) {
    console.error('Failed to add to sync queue:', err);
    throw err;
  }
};

/**
 * Get pending operations from the sync queue
 * @param {number} limit - Maximum number of items to retrieve
 */
export const getPendingSyncItems = async (limit = 10) => {
  try {
    const [rows] = await db.query(
      'SELECT * FROM sync_queue WHERE status = "pending" ORDER BY created_at LIMIT ?',
      [limit]
    );
    return rows;
  } catch (err) {
    console.error('Failed to get pending sync items:', err);
    throw err;
  }
};

/**
 * Update the status of a sync queue item
 * @param {number} id - ID of the sync queue item
 * @param {string} status - New status ('processing', 'success', 'failed')
 * @param {string} error - Error message if applicable
 */
export const updateSyncItem = async (id, status, error = null) => {
  try {
    if (status === 'failed') {
      await db.query(
        'UPDATE sync_queue SET status = ?, last_error = ?, retry_count = retry_count + 1, updated_at = NOW() WHERE id = ?',
        [status, error, id]
      );
    } else {
      await db.query(
        'UPDATE sync_queue SET status = ?, updated_at = NOW() WHERE id = ?',
        [status, id]
      );
    }
  } catch (err) {
    console.error(`Failed to update sync item ${id}:`, err);
    throw err;
  }
};
