import axios from 'axios';
import services from '../config/services.js';

/**
 * Makes authenticated service-to-service API calls
 * @param {string} url - The full URL to call
 * @param {Object} options - Axios request options
 * @returns {Promise<any>} Response data
 */
export const serviceCall = async (url, options = {}) => {
  try {
    const response = await axios({
      ...options,
      url,
      headers: {
        ...options.headers,
        'Authorization': `Bearer ${services.INTERNAL_API_TOKEN}`
      }
    });
    return response.data;
  } catch (error) {
    console.error(`Service call failed: ${url}`, error.message);
    if (error.response?.status === 404) {
      return null; // Return null for not found resources
    }
    throw new Error(`Service call failed: ${error.response?.data?.message || error.message}`);
  }
};