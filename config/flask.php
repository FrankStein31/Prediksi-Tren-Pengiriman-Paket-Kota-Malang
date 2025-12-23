<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Flask API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Flask API server that handles Prophet predictions.
    | You can change the base URL depending on your deployment environment.
    |
    */

    'api_url' => env('FLASK_API_URL', 'http://127.0.0.1:5000'),
    
    'endpoints' => [
        'predict' => '/api/predict',
        'health' => '/health',
        'kecamatans' => '/api/kecamatans',
    ],
    
    'timeout' => env('FLASK_API_TIMEOUT', 120), // 2 minutes
    'connect_timeout' => env('FLASK_API_CONNECT_TIMEOUT', 10), // 10 seconds

];
