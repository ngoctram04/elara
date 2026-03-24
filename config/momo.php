<?php

return [
    'partner_code' => env('MOMO_PARTNER_CODE'),
    'access_key'   => env('MOMO_ACCESS_KEY'),
    'secret_key'   => env('MOMO_SECRET_KEY'),
    'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    'query_url'    => env('MOMO_QUERY_URL'),
    'return_url'   => env('MOMO_RETURN_URL'),
    'ipn_url'      => env('MOMO_IPN_URL'),
];