const axios =require('axios');
const {promisify}  =require('util') ;
const dns =require ('dns');

const dnsLookup = promisify(dns.lookup);

/**
 * Check health of a dependent service
 * @param {string} serviceUrl - URL of the service to check
 * @param {number} timeout - Timeout in milliseconds
 * @returns {Promise<Object>} Health status object
 */
const checkServiceHealth = async (serviceUrl, timeout = 2000) => {
  if (!serviceUrl) {
    return {
      available: false,
      status: 'error',
      message: 'Service URL not provided'
    };
  }

  try {
    // Extract hostname for DNS check
    const url = new URL(serviceUrl);
    const hostname = url.hostname;
    
    // First check if the hostname is resolvable (DNS check)
    try {
      await dnsLookup(hostname);
    } catch (dnsError) {
      return {
        available: false,
        status: 'error',
        message: 'DNS resolution failed',
        details: dnsError.message
      };
    }
    
    // If DNS resolves, check the health endpoint
    const healthEndpoint = `${serviceUrl.replace(/\/+$/, '')}/health`;
    
    const response = await axios.get(healthEndpoint, {
      timeout,
      validateStatus: null // Accept any status code
    });
    
    // Check response status
    if (response.status >= 200 && response.status < 300) {
      if (response.data?.status === 'ok') {
        return {
          available: true,
          status: 'ok',
          serviceName: response.data?.service || 'unknown',
          responseTime: response.headers['x-response-time'] || 'unknown'
        };
      } else {
        return {
          available: false,
          status: 'degraded',
          message: 'Service reported unhealthy status',
          details: response.data
        };
      }
    } else {
      return {
        available: false,
        status: 'error',
        message: `Service returned status code ${response.status}`,
        details: response.data
      };
    }
  } catch (error) {
    // Handle different types of errors
    if (error.code === 'ECONNABORTED') {
      return {
        available: false,
        status: 'timeout',
        message: `Service connection timed out after ${timeout}ms`,
        details: error.message
      };
    } else if (error.code === 'ECONNREFUSED') {
      return {
        available: false,
        status: 'error',
        message: 'Connection refused',
        details: error.message
      };
    } else {
      return {
        available: false,
        status: 'error',
        message: 'Failed to connect to service',
        details: error.message
      };
    }
  }
};

/**
 * Check multiple services health in parallel
 * @param {Object} services - Object mapping service names to URLs
 * @param {number} timeout - Timeout in milliseconds
 * @returns {Promise<Object>} Health status for each service
 */
const checkMultipleServices = async (services, timeout = 2000) => {
  const checks = {};
  
  // Check all services in parallel
  await Promise.all(
    Object.entries(services).map(async ([name, url]) => {
      checks[name] = await checkServiceHealth(url, timeout);
    })
  );
  
  const allHealthy = Object.values(checks).every(check => check.available);
  
  return {
    status: allHealthy ? 'ok' : 'degraded',
    timestamp: new Date().toISOString(),
    services: checks
  };
};

/**
 * Get system resource usage for health monitoring
 * @returns {Object} Resource usage info
 */
const getSystemHealth = () => {
  const memoryUsage = process.memoryUsage();
  
  return {
    memory: {
      rss: Math.round(memoryUsage.rss / 1024 / 1024) + 'MB', // Resident Set Size
      heapTotal: Math.round(memoryUsage.heapTotal / 1024 / 1024) + 'MB',
      heapUsed: Math.round(memoryUsage.heapUsed / 1024 / 1024) + 'MB',
      external: Math.round(memoryUsage.external / 1024 / 1024) + 'MB',
    },
    cpu: process.cpuUsage(),
    uptime: process.uptime() + 's'
  };
};

module.exports = {
  checkServiceHealth,
  checkMultipleServices,
  getSystemHealth
};