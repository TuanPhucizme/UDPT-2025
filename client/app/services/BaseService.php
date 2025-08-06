<?php
abstract class BaseService {
    protected $baseUrl;
    protected $port;

    protected function request($method, $endpoint, $data = null) {
        $curl = curl_init();
        $url = "{$this->baseUrl}:{$this->port}{$endpoint}";
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // Add JWT token if available
        if (isset($_SESSION['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method
        ];

        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }

        curl_close($curl);

        return [
            'data' => json_decode($response, true),
            'statusCode' => $statusCode
        ];
    }
}