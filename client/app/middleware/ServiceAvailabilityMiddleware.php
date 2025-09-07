<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\middleware\ServiceAvailabilityMiddleware.php

class ServiceAvailabilityMiddleware {
    private static $services = [
        'patient' => [
            'url' => 'http://localhost:3001',
            'name' => 'Dịch vụ bệnh nhân',
            'critical' => true
        ],
        'prescription' => [
            'url' => 'http://localhost:3003',
            'name' => 'Dịch vụ kê đơn',
            'critical' => true
        ],
        'auth' => [
            'url' => 'http://localhost:3000',
            'name' => 'Dịch vụ xác thực',
            'critical' => true
        ],
        'appointment' => [
            'url' => 'http://localhost:3002', 
            'name' => 'Dịch vụ lịch hẹn',
            'critical' => false
        ],
        'report' => [
            'url' => 'http://localhost:3005',
            'name' => 'Dịch vụ báo cáo',
            'critical' => false
        ]
    ];
    
    /**
     * Check health of a specific service using cURL
     * @param string $serviceKey Service identifier
     * @param int $timeout Timeout in seconds
     * @return array Health status information
     */
    public static function checkService($serviceKey, $timeout = 2) {
        if (!isset(self::$services[$serviceKey])) {
            return [
                'service' => $serviceKey,
                'available' => false,
                'error' => 'Service not defined',
                'critical' => false
            ];
        }
        
        $serviceInfo = self::$services[$serviceKey];
        $serviceUrl = $serviceInfo['url'];
        
        error_log("Checking service $serviceKey at $serviceUrl");
        
        if (!$serviceUrl) {
            return [
                'service' => $serviceKey,
                'name' => $serviceInfo['name'],
                'available' => false,
                'error' => 'URL configuration missing',
                'critical' => $serviceInfo['critical']
            ];
        }
        
        // Use cURL instead of file_get_contents for more reliable HTTP requests
        $healthUrl = rtrim($serviceUrl, '/') . '/health';
        
        // Check if cURL is available
        if (!function_exists('curl_init')) {
            error_log("cURL is not available. Trying file_get_contents instead.");
            return self::checkServiceWithFileGetContents($serviceKey, $timeout);
        }
        
        try {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $healthUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_HEADER, true);
            
            // Important for local development - don't verify SSL certificates
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $responseTime = round((microtime(true) - $startTime) * 1000); // in ms
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $headerSize);
            
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($curlErrno) {
                error_log("cURL error for $serviceKey: $curlErrno - $curlError");
                return [
                    'service' => $serviceKey,
                    'name' => $serviceInfo['name'],
                    'available' => false,
                    'error' => "Connection error: $curlError",
                    'critical' => $serviceInfo['critical'],
                    'responseTime' => $responseTime,
                    'curl_error_code' => $curlErrno
                ];
            }
            
            // Parse response
            $responseData = json_decode($body, true);
            $jsonError = json_last_error();
            
            if ($jsonError !== JSON_ERROR_NONE && !empty($body)) {
                error_log("Invalid JSON response from $serviceKey: " . json_last_error_msg());
                error_log("Response body: " . substr($body, 0, 500));
                
                return [
                    'service' => $serviceKey,
                    'name' => $serviceInfo['name'],
                    'available' => false,
                    'error' => 'Invalid JSON response',
                    'critical' => $serviceInfo['critical'],
                    'responseTime' => $responseTime,
                    'statusCode' => $httpCode
                ];
            }
            
            $status = isset($responseData['status']) && $responseData['status'] === 'ok';
            
            return [
                'service' => $serviceKey,
                'name' => $serviceInfo['name'],
                'available' => $status && ($httpCode >= 200 && $httpCode < 300),
                'statusCode' => $httpCode,
                'responseTime' => $responseTime,
                'critical' => $serviceInfo['critical'],
                'details' => $responseData
            ];
        } catch (Exception $e) {
            error_log("Exception checking service $serviceKey: " . $e->getMessage());
            
            return [
                'service' => $serviceKey,
                'name' => $serviceInfo['name'],
                'available' => false,
                'error' => $e->getMessage(),
                'critical' => $serviceInfo['critical']
            ];
        }
    }
    
    /**
     * Fallback method using file_get_contents
     */
    private static function checkServiceWithFileGetContents($serviceKey, $timeout = 2) {
        $serviceInfo = self::$services[$serviceKey];
        $serviceUrl = $serviceInfo['url'];
        
        try {
            $healthUrl = rtrim($serviceUrl, '/') . '/health';
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => $timeout,
                    'ignore_errors' => true,
                    'header' => 'Accept: application/json'
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            
            $startTime = microtime(true);
            $response = @file_get_contents($healthUrl, false, $context);
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            if ($response === false) {
                $error = error_get_last();
                error_log("file_get_contents failed for $serviceKey: " . ($error['message'] ?? 'Unknown error'));
                
                return [
                    'service' => $serviceKey,
                    'name' => $serviceInfo['name'],
                    'available' => false,
                    'error' => 'Service unreachable: ' . ($error['message'] ?? 'Unknown error'),
                    'critical' => $serviceInfo['critical']
                ];
            }
            
            // Get HTTP status code
            $statusCode = 0;
            if (isset($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/', $header, $matches)) {
                        $statusCode = intval($matches[1]);
                        break;
                    }
                }
            }
            
            // Parse response
            $responseData = json_decode($response, true);
            $status = isset($responseData['status']) && $responseData['status'] === 'ok';
            
            return [
                'service' => $serviceKey,
                'name' => $serviceInfo['name'],
                'available' => $status && ($statusCode >= 200 && $statusCode < 300),
                'statusCode' => $statusCode,
                'responseTime' => $responseTime,
                'critical' => $serviceInfo['critical'],
                'details' => $responseData
            ];
        } catch (Exception $e) {
            error_log("Exception in file_get_contents for $serviceKey: " . $e->getMessage());
            
            return [
                'service' => $serviceKey,
                'name' => $serviceInfo['name'],
                'available' => false,
                'error' => $e->getMessage(),
                'critical' => $serviceInfo['critical']
            ];
        }
    }
    
    /**
     * Check multiple services
     * @param array $serviceKeys List of service identifiers to check
     * @param int $timeout Timeout in seconds
     * @return array Service health statuses
     */
    public static function checkMultipleServices($serviceKeys = [], $timeout = 2) {
        // If no services specified, check all
        if (empty($serviceKeys)) {
            $serviceKeys = array_keys(self::$services);
        }
        
        $results = [];
        $unavailable = [];
        $criticalUnavailable = false;
        
        foreach ($serviceKeys as $serviceKey) {
            $status = self::checkService($serviceKey, $timeout);
            $results[$serviceKey] = $status;
            
            if (!$status['available']) {
                $unavailable[] = $status;
                
                if ($status['critical']) {
                    $criticalUnavailable = true;
                }
            }
        }
        
        return [
            'services' => $results,
            'allAvailable' => empty($unavailable),
            'unavailable' => $unavailable,
            'criticalUnavailable' => $criticalUnavailable,
            'timestamp' => time()
        ];
    }
    
    /**
     * Handle service unavailability
     * @param array $serviceStatus Result from checkServices()
     * @return void
     */
    public static function handleUnavailableServices($serviceStatus) {
        if ($serviceStatus['criticalUnavailable']) {
            // For critical services, redirect to maintenance page
            self::redirectToMaintenance($serviceStatus['unavailable']);
            exit;
        } else if (!$serviceStatus['allAvailable']) {
            // For non-critical services, just set a warning
            $_SESSION['warning'] = self::formatServiceWarning($serviceStatus['unavailable']);
        }
    }
    
    /**
     * Format warning message for unavailable services
     * @param array $unavailableServices List of unavailable service info
     * @return string Warning message
     */
    private static function formatServiceWarning($unavailableServices) {
        $serviceNames = array_map(function($service) {
            return $service['name'];
        }, $unavailableServices);
        
        return 'Một số dịch vụ đang không khả dụng: ' . implode(', ', $serviceNames) . 
               '. Một số chức năng có thể bị hạn chế.';
    }
    
    /**
     * Redirect to maintenance page
     * @param array $unavailableServices List of unavailable service info
     * @return void
     */
    private static function redirectToMaintenance($unavailableServices) {
        // Check if this is a request that should just return error without redirect
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => 'Service unavailable',
                'services' => $unavailableServices
            ]);
            exit;
        }
        
        // Get available features based on which services are still running
        $availableFeatures = self::getAvailableFeatures($unavailableServices);
        
        $_SESSION['maintenance'] = [
            'services' => $unavailableServices,
            'timestamp' => time(),
            'availableFeatures' => $availableFeatures,
            'queueEnabled' => true // Enable request queueing for write operations
        ];
        
        header('Location: /maintenance');
        exit;
    }
    
    /**
     * Determine which features are still available based on service status
     * @param array $unavailableServices List of unavailable services
     * @return array List of available features
     */
    private static function getAvailableFeatures($unavailableServices) {
        // Map services to their dependent features
        $serviceFeatureMap = [
            'patient' => ['Xem thông tin cá nhân bệnh nhân', 'Xem lịch sử khám bệnh'],
            'prescription' => ['Xem đơn thuốc', 'Kê đơn thuốc mới'],
            'auth' => ['Đăng nhập', 'Quản lý nhân viên'],
            'appointment' => ['Lịch hẹn', 'Đặt lịch khám bệnh'],
            'report' => ['Báo cáo', 'Thống kê']
        ];
        
        // Get list of unavailable service keys
        $unavailableKeys = array_map(function($service) {
            return $service['service'];
        }, $unavailableServices);
        
        // Get all available services
        $availableServices = array_diff(array_keys($serviceFeatureMap), $unavailableKeys);
        
        // Collect available features
        $availableFeatures = [];
        foreach ($availableServices as $serviceKey) {
            if (isset($serviceFeatureMap[$serviceKey])) {
                $availableFeatures = array_merge($availableFeatures, $serviceFeatureMap[$serviceKey]);
            }
        }
        
        // Include "browse" feature which is always available
        $availableFeatures[] = 'Xem thông tin chung';
        $availableFeatures[] = 'Tra cứu bệnh nhân (dữ liệu có thể không mới nhất)';
        
        return $availableFeatures;
    }
    
    /**
     * Generate a HTML report of service health
     * @param array $results Results from checkMultipleServices()
     * @return string HTML content
     */
    public static function generateHealthReport($results) {
        $html = '<div class="service-health-report">';
        $html .= '<h5>Service Health Status</h5>';
        $html .= '<table class="table table-sm table-striped">';
        $html .= '<thead><tr><th>Service</th><th>Status</th><th>Response Time</th><th>Details</th></tr></thead><tbody>';
        
        foreach ($results['services'] as $service) {
            $statusClass = $service['available'] ? 'text-success' : ($service['critical'] ? 'text-danger' : 'text-warning');
            $statusIcon = $service['available'] ? 'check-circle' : 'exclamation-triangle';
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($service['name']) . '</td>';
            $html .= '<td><span class="' . $statusClass . '"><i class="fas fa-' . $statusIcon . '"></i> ' . 
                     ($service['available'] ? 'Available' : 'Unavailable') . '</span></td>';
            $html .= '<td>' . (isset($service['responseTime']) ? $service['responseTime'] . 'ms' : 'N/A') . '</td>';
            $html .= '<td>';
            
            if (!$service['available']) {
                $html .= '<small class="text-danger">' . htmlspecialchars($service['error'] ?? 'Unknown error') . '</small>';
            } else {
                $html .= '<small class="text-muted">Status Code: ' . ($service['statusCode'] ?? 'N/A') . '</small>';
            }
            
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Create a test endpoint checker
     */
    public static function testEndpoint($serviceKey) {
        if (!isset(self::$services[$serviceKey])) {
            return "Service $serviceKey not defined";
        }
        
        $serviceInfo = self::$services[$serviceKey];
        $serviceUrl = $serviceInfo['url'];
        $healthUrl = rtrim($serviceUrl, '/') . '/health';
        
        $output = "<h3>Testing connection to {$serviceInfo['name']}</h3>";
        $output .= "<p>URL: $healthUrl</p>";
        
        // Test with cURL
        $output .= "<h4>Testing with cURL:</h4>";
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $healthUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                
                curl_close($ch);
                
                if ($errno) {
                    $output .= "<div class='alert alert-danger'>cURL Error ($errno): $error</div>";
                } else {
                    $output .= "<div class='alert alert-success'>Connected successfully! Status code: $httpCode</div>";
                    $output .= "<pre>" . htmlspecialchars($response) . "</pre>";
                }
            } catch (Exception $e) {
                $output .= "<div class='alert alert-danger'>Exception: " . $e->getMessage() . "</div>";
            }
        } else {
            $output .= "<div class='alert alert-warning'>cURL not available on this server</div>";
        }
        
        // Test with file_get_contents
        $output .= "<h4>Testing with file_get_contents:</h4>";
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            
            $response = @file_get_contents($healthUrl, false, $context);
            
            if ($response === false) {
                $error = error_get_last();
                $output .= "<div class='alert alert-danger'>Error: " . ($error['message'] ?? 'Unknown error') . "</div>";
            } else {
                $output .= "<div class='alert alert-success'>Connected successfully!</div>";
                $output .= "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        } catch (Exception $e) {
            $output .= "<div class='alert alert-danger'>Exception: " . $e->getMessage() . "</div>";
        }
        
        return $output;
    }
}