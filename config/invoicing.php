<?php

return [
    'dian' => [
        // DIAN OAuth2 credentials
        'client_id' => env('DIAN_CLIENT_ID'),
        'client_secret' => env('DIAN_CLIENT_SECRET'),

        // DIAN API endpoint
        'api_url' => env('DIAN_API_URL', 'https://api.dian.gov.co/api/ws/fedora'),

        // Environment: 'production', 'staging', 'test'
        'environment' => env('DIAN_ENVIRONMENT', 'staging'),

        // Certificate configuration
        'certificate' => [
            'path' => env('DIAN_CERT_PATH'),
            'password' => env('DIAN_CERT_PASSWORD'),
            'type' => env('DIAN_CERT_TYPE', 'pkcs12'), // pkcs12 or pem
        ],

        // Retry configuration
        'max_retries' => env('DIAN_MAX_RETRIES', 3),
        'retry_delay_ms' => env('DIAN_RETRY_DELAY_MS', 1000),

        // Webhook configuration
        'webhook' => [
            'secret' => env('DIAN_WEBHOOK_SECRET'),
            'url' => env('DIAN_WEBHOOK_URL', '/api/v1/webhooks/dian/ack'),
        ],

        // Feature flags
        'features' => [
            'auto_send_invoice' => env('DIAN_AUTO_SEND_INVOICE', false),
            'validate_before_send' => env('DIAN_VALIDATE_BEFORE_SEND', true),
            'enable_revocation' => env('DIAN_ENABLE_REVOCATION', false),
        ],
    ],
];
