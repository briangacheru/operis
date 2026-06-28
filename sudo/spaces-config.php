<?php
require_once __DIR__ . '/config.php';

return [
    'region'   => env('DO_SPACES_REGION'),
    'endpoint' => env('DO_SPACES_ENDPOINT'),
    'bucket'   => env('DO_SPACES_BUCKET'),
    'access_key' => env('DO_SPACES_KEY'),
    'secret_key' => env('DO_SPACES_SECRET'),
];
