<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

class PayphoneService
{
    private string $apiToken;
    private string $storeId;
    private string $baseUrl = 'https://pay.payphonetodoesposible.com';
    private const COMMISSION_RATE = 0.06;

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
        $clientTransactionId = $data['clientTransactionId'] ?? '';
        $amountBase = (int) ($data['amount'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);

        $commission = (int) round($amountBase * self::COMMISSION_RATE);
        $amountWithoutTax = $amountBase;
        $amountWithTax = $amountBase + $commission;
        $tax = $commission;

        $reference = $data['reference'] ?? '';
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $email = trim($data['email'] ?? '');
        $phoneNumber = $this->formatPhoneNumber($data['phoneNumber'] ?? '');
        $currency = $data['currency'] ?? 'USD';
        $responseUrl = $data['responseUrl'] ?? '';
        $cancellationUrl = $data['cancellationUrl'] ?? '';

        $totalAmount = $amountWithoutTax + $amountWithTax + $tax;

        $payload = [
            'amount' => $totalAmount,
            'amountWithoutTax' => $amountWithoutTax,
            'amountWithTax' => $amountWithTax,
            'tax' => $tax,
            'service' => 0,
            'tip' => 0,
            'clientTransactionId' => $clientTransactionId,
            'reference' => $reference,
            'storeId' => $this->storeId,
            'currency' => $currency,
            'responseUrl' => $responseUrl,
            'cancellationUrl' => $cancellationUrl,
            'timeZone' => -5,
            'lat' => '-1.831239',
            'lng' => '-78.183406',
            'order' => [
                'billTo' => [
                    'billToId' => 0,
                    'address1' => 'N/A',
                    'address2' => 'N/A',
                    'country' => 'EC',
                    'state' => 'Azuay',
                    'locality' => 'Cuenca',
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
                        'unitPrice' => (int) ($amountWithoutTax / $quantity),
                        'quantity' => $quantity,
                        'totalAmount' => $amountWithoutTax,
                        'taxAmount' => $tax,
                        'productSKU' => 'QL-' . substr($clientTransactionId, 0, 10),
                        'productDescription' => substr($reference, 0, 100)
                    ]
                ]
            ],
            'documentId' => '',
            'phoneNumber' => $phoneNumber,
            'email' => $email,
            'optionalParameter' => substr($reference, 0, 50)
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