<?php
class BaseService {
    protected $baseUrl;
    protected $port;

    protected function makeRequest($method, $url, $data = [], $useInternalToken = false) {
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json'
        ];
        // Choose between internal token and user token
        if ($useInternalToken) {
            $headers[] = 'Authorization: Bearer ' . INTERNAL_API_TOKEN;
        } else {
            $headers[] = 'Authorization: Bearer ' . ($_SESSION['token'] ?? '');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('Failed to connect to service');
        }
        
        $result = json_decode($response, true);
        return [
            'statusCode' => $httpCode,
            'data' => $result,
            'message' => $result['message'] ?? null
        ];
    }

    protected function request($method, $path, $data = []) {
        return $this->makeRequest(
            $method, 
            "{$this->baseUrl}:{$this->port}{$path}", 
            $data
        );
    }
}