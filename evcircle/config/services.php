<?php

return [

    // ...other services

    'tktev' => [
        'base_url'  => env('TKTEV_BASE_URL'),
        'username'  => env('TKTEV_USERNAME'),
        'password'  => env('TKTEV_PASSWORD'),
        'aes_key'   => env('TKTEV_AES_KEY'),

        'tenant_id' => env('TKTEV_TENANT_ID', '1'),
        'lang'      => env('TKTEV_LANG', 'zh-CN'),
        'timezone'  => env('TKTEV_TIMEZONE', 'Asia/Shanghai'),

        // These are required to create/register chargers in TKTEV:
        'model_key'     => env('TKTEV_MODEL_KEY'),
        'product_key'   => env('TKTEV_PRODUCT_KEY'),
        'model_type'    => env('TKTEV_MODEL_TYPE'),
        'protocol_type' => env('TKTEV_PROTOCOL_TYPE'),
        'device_type'   => env('TKTEV_DEVICE_TYPE'),
        'signal_mode'   => env('TKTEV_SIGNAL_MODE'),
    ],

    // Python FastAPI ML microservice (waiting-time prediction, station
    // recommendation, EV range prediction, route optimization).
    'ml' => [
        'base_url' => env('ML_SERVICE_URL', 'http://127.0.0.1:8000'),
        'timeout'  => env('ML_SERVICE_TIMEOUT', 10),
    ],
];
