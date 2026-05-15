<?php

namespace App\Services;
use Config\Services;

class ApiPrivadaService
{
    public function getDataUser($cedula)
    {
        $habilitarApi = false;

        if (!$habilitarApi) {
            return false;
        }
        try {
            $client = \Config\Services::curlrequest();

            // Definir datos de la API
            $url = getenv('CEDULA_API_URL');
            $user = getenv('CEDULA_API_JSON_USER');
            $ip = getenv('CEDULA_API_JSON_IP');
            $token = getenv('CEDULA_API_BEARER_TOKEN');

            // Body de la petición
            $data = [
                'cedula' => $cedula,
                'user' => $user,
                'ip' => $ip
            ];

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'insomnia/9.2.0'
                ],
                'json' => $data // Envío de JSON automáticamente
            ]);

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                log_message('warning', 'Solicitud de cédula fallida: ' . $response->getStatusCode(), ['id' => $cedula]);
                return null;
            }

        } catch (\Exception $e) {
            log_message('error', 'Excepción en la solicitud de la cédula: ' . $e->getMessage(), ['id' => $cedula]);
            return null;
        }
    }

}
