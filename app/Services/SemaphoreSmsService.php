<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SemaphoreSmsService
{
    protected $client;
    protected $apiKey;
    protected $senderName;
    protected $endpoint;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client(['timeout' => 15]);
        $this->apiKey = config('services.semaphore.key');
        $this->senderName = config('services.semaphore.sender');
        $this->endpoint = config('services.semaphore.endpoint', 'https://api.semaphore.co/api/v4/messages');
    }

    public function sendOtp($number, $otp)
    {
        if (!$this->apiKey) {
            return [
                'ok' => false,
                'message' => 'Semaphore API key is not configured.',
            ];
        }

        $message = "Your DMS mobile verification code is {$otp}. It expires in 5 minutes. Do not share this code.";

        $payload = [
            'apikey' => $this->apiKey,
            'number' => $number,
            'message' => $message,
        ];

        if ($this->senderName) {
            $payload['sendername'] = $this->senderName;
        }

        try {
            $response = $this->client->post($this->endpoint, [
                'form_params' => $payload,
            ]);

            return [
                'ok' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'message' => 'OTP sent successfully.',
            ];
        } catch (GuzzleException $exception) {
            Log::warning('Semaphore OTP send failed', [
                'number' => $number,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Unable to send OTP right now. Please try again.',
            ];
        }
    }
}
