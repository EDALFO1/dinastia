<?php

return [
    'dian' => [
        // DIAN OAuth2 credentials para nómina electrónica
        'client_id' => env('DIAN_NOMINA_CLIENT_ID'),
        'client_secret' => env('DIAN_NOMINA_CLIENT_SECRET'),

        // DIAN API endpoint
        'api_url' => env('DIAN_NOMINA_API_URL', 'https://api.dian.gov.co/api/ws/nomina'),

        // Environment: 'production', 'staging', 'test'
        'environment' => env('DIAN_NOMINA_ENVIRONMENT', 'staging'),

        // Certificate configuration
        'certificate' => [
            'path' => env('DIAN_NOMINA_CERT_PATH'),
            'password' => env('DIAN_NOMINA_CERT_PASSWORD'),
            'type' => env('DIAN_NOMINA_CERT_TYPE', 'pkcs12'),
        ],

        // Retry configuration
        'max_retries' => env('DIAN_NOMINA_MAX_RETRIES', 3),
        'retry_delay_ms' => env('DIAN_NOMINA_RETRY_DELAY_MS', 1000),

        // Webhook configuration
        'webhook' => [
            'secret' => env('DIAN_NOMINA_WEBHOOK_SECRET'),
            'url' => env('DIAN_NOMINA_WEBHOOK_URL', '/api/v1/webhooks/dian/nomina/ack'),
        ],

        // Feature flags
        'features' => [
            'auto_send_payroll' => env('DIAN_NOMINA_AUTO_SEND', false),
            'validate_before_send' => env('DIAN_NOMINA_VALIDATE_BEFORE_SEND', true),
        ],
    ],

    // Cálculo de nómina
    'calculo' => [
        // Salario mínimo 2024 (ajustar anualmente)
        'salario_minimo' => 1300000,

        // Auxilio de transporte 2024
        'auxilio_transporte' => 162000,

        // Porcentajes de aportes (pueden variar)
        'aportes' => [
            'afp' => [
                'empleado' => 4.0,
                'empleador' => 12.0,
            ],
            'eps' => [
                'empleado' => 4.0,
                'empleador' => 8.5,
            ],
            'arl' => [
                'empleado' => 0.0,
                'empleador' => 0.52, // Varía por riesgo
            ],
            'caja_compensacion' => [
                'empleado' => 0.0,
                'empleador' => 4.0,
            ],
        ],

        // Retenciones
        'retenciones' => [
            // Tabla de renta 2024 (UVT 2024)
            'renta_uvt' => 44.148,
            'renta_tabla' => [
                ['hasta' => 95, 'porcentaje' => 0],
                ['hasta' => 150, 'porcentaje' => 19],
                ['hasta' => 360, 'porcentaje' => 28],
                ['hasta' => 'inf', 'porcentaje' => 33],
            ],
            'solidaridad' => [
                'minimo' => 2, // 2 SMLMV
                'porcentaje' => 1.0,
            ],
        ],
    ],

    // Período de nómina
    'periodo' => [
        'tipo' => env('PAYROLL_PERIOD_TYPE', 'mensual'), // mensual, quincenal, semanal
        'fecha_pago_default' => 25, // Día del mes para pago
    ],
];
