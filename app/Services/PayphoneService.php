<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

class PayphoneService
{
    private string $apiToken;
    private string $storeId;
    private string $baseUrl = 'https://pay.payphonetodoesposible.com';

    public function __construct()
    {
        $this->apiToken = getenv('PAYPHONE_API_TOKEN');
        $this->storeId = getenv('PAYPHONE_API_STORE');
    }

    public function getToken(): string
    {
        return $this->apiToken;
    }

    public function getStore(): string
    {
        return $this->storeId;
    }

    public function createPayment(array $data): array
    {

        // Datos básicos para calcular el total
        $montoBase = $data['amount'] ?? 0;
        $por = 0.00; // Comisión fija del 0% (ajustable si se requiere)
        $iva = 0.00; // IVA del 0% (ajustable si se requiere)
        $tot = ($montoBase * $por) * $iva;
        $total = round(($montoBase + $tot) * 100);

        $clientTransactionId = $data['clientTransactionId'] ?? '';
        $quantity = (int) ($data['quantity'] ?? 1);
        $precioBoleto = ($data['precioUnitario'] * 100 ) ?? 0; // Precio unitario en centavos

        $reference = $data['reference'] ?? '';
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $email = trim($data['email'] ?? '');
        $phoneNumber = $this->formatPhoneNumber($data['phoneNumber'] ?? '');
        $currency = $data['currency'] ?? 'USD';
        $responseUrl = $data['responseUrl'] ?? '';
        $cancellationUrl = $data['cancellationUrl'] ?? '';

        $totalAmount = $total; // El total calculado con impuestos y comisiones, multiplicado por 100 para convertir a centavos

        $payload = [
            'amount' => $totalAmount,
            'amountWithoutTax' => $totalAmount,
            'amountWithTax' => 0,
            'tax' => 0,
            'clientTransactionId' => $clientTransactionId,
            'reference' => $reference,
            'storeId' => $this->storeId,
            'currency' => $currency,
            'responseUrl' => $responseUrl,
            'cancellationUrl' => $cancellationUrl,
            'order' => [
                'billTo' => [
                    'billToId' => 0,
                    'address1' => 'N/A',
                    'address2' => 'N/A',
                    'country' => 'EC',
                    'state' => 'Bolivar',
                    'locality' => 'Guaranda',
                    'firstName' => substr($firstName, 0, 50),
                    'lastName' => substr($lastName, 0, 50),
                    'phoneNumber' => $phoneNumber,
                    'email' => $email,
                    'postalCode' => '000000',
                    'customerId' => substr($clientTransactionId, 0, 20),
                    'ipAddress' => service('request')->getIPAddress()
                ],
                'lineItems' => [
                    [
                        'productName' => 'Boletos Quickluck',
                        'unitPrice' => $precioBoleto,
                        'quantity' => $quantity,
                        'totalAmount' => $total,
                        'taxAmount' => 0,
                        'productSKU' => 'QL-' . substr($clientTransactionId, 0, 10),
                        'productDescription' => substr($reference, 0, 100)
                    ]
                ]
            ]
        ];

        log_message('info', 'Payphone request payload: ' . json_encode($payload));

        try {
            $client = \Config\Services::curlrequest();

            $response = $client->request('POST', $this->baseUrl . '/api/button/Prepare', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode($payload),
                'timeout' => 30,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            log_message('info', "Payphone response status: {$statusCode}, body: {$body}");

            if ($statusCode >= 200 && $statusCode < 300) {
                $decoded = json_decode($body, true);
                return [
                    'success' => true,
                    'data' => $decoded
                ];
            }

            $errorData = json_decode($body, true);
            $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Unknown error';

            log_message('error', "Payphone API error ({$statusCode}): {$errorMessage}");

            return [
                'success' => false,
                'error' => $errorMessage,
                'status' => $statusCode,
                'details' => $errorData['errors'] ?? []
            ];

        } catch (\Throwable $e) {
            log_message('error', 'Payphone curl exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }

        if (!str_starts_with($clean, '593')) {
            $clean = '593' . $clean;
        }

        return '+' . $clean;
    }
}