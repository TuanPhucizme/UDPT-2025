import axios from 'axios';
import services from '../config/services.js';

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
      return null;
    }
    throw new Error(`Service call failed: ${error.response?.data?.message || error.message}`);
  }
};