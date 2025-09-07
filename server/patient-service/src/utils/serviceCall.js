import axios from 'axios';
import services from '../config/services.js';
import { addToSyncQueue } from '../models/sync_queue.model.js';

export const serviceCall = async (url, options = {}) => {
  try {
    const response = await axios({
      ...options,
      url,
      headers: {
        ...options.headers,
        'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`,
        'x-internal-request': 'true'
      },
      timeout: 5000 // 5 second timeout
    });
    return response.data;
  } catch (error) {
    console.error(`Service call failed: ${url}`, error.message);
    
    // Check if this is a request that can be queued for later
    const queueableServices = {
      [services.NOTIFICATION_SERVICE_URL]: true,
      [services.REPORT_SERVICE_URL]: true
    };
    
    // Extract the base URL from the full URL to check if it's a queueable service
    const baseUrl = url.split('/api/')[0];
    
    if (queueableServices[baseUrl]) {
      console.log(`Queueing failed request to ${url} for later processing`);
      
      // Determine the type based on the URL
      let type = 'unknown';
      if (url.includes('notifications')) {
        type = url.includes('patient') ? 'notify_patient_created' : 'notify_record_created';
      } else if (url.includes('report') || url.includes('sync')) {
        type = 'report_patient_data';
      }
      
      // Add to queue with the original request data
      await addToSyncQueue(type, {
        url,
        method: options.method || 'GET',
        data: options.data,
        ...options.data // Spread the data to make it accessible at the top level
      });
      
      // Return a "queued" status
      return { 
        success: false, 
        queued: true, 
        message: 'Request queued for later processing' 
      };
    }
    
    if (error.response?.status === 404) {
      return null;
    }
    throw new Error(`Service call failed: ${error.response?.data?.message || error.message}`);
  }
};
