<?php
class BaseService {
    protected $baseUrl;
    
    protected function request($method, $endpoint, $data = null, $headers = []) {
        $curl = curl_init();
        $url = $this->baseUrl . $endpoint;
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];
        
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        return [
            'data' => json_decode($response, true),
            'statusCode' => $statusCode
        ];
    }
}