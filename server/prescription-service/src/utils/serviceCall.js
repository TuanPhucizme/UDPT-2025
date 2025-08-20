import axios from 'axios';
import services from '../config/services.js';

export const serviceCall = async (url, options = {}) => {
  const isInternalEndpoint = url.includes('/internal/');
  const token = isInternalEndpoint ? 
    services.INTERNAL_API_TOKEN : 
    options.token;

  try {
    const response = await axios({
      ...options,
      url,
      headers: {
        ...options.headers,
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    console.error(`Service call failed: ${url}`, error.message);
    throw new Error(`Service call failed: ${error.response?.data?.message || error.message}`);
  }
};