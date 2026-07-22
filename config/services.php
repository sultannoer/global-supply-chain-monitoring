<?php

return [

    'rest_countries' => [
        'base_url' => env('REST_COUNTRIES_BASE_URL', 'https://api.restcountries.com/countries/v5'),
        'key' => env('REST_COUNTRIES_API_KEY'),
        'timeout' => (int) env('REST_COUNTRIES_TIMEOUT', 8),
    ],

    'gnews' => [
        'base_url' => env('GNEWS_BASE_URL', 'https://gnews.io/api/v4'),
        'key' => env('GNEWS_API_KEY'),
        'timeout' => (int) env('GNEWS_TIMEOUT', 8),
    ],

    'world_port_index' => [
        'query_url' => env(
            'WORLD_PORT_INDEX_QUERY_URL',
            'https://vcps.nga.mil/nauticalpubs-feature/rest/services/WPI/World_Port_Index_Viewer/FeatureServer/0/query'
        ),
        'timeout' => (int) env('WORLD_PORT_INDEX_TIMEOUT', 30),
        'page_size' => (int) env('WORLD_PORT_INDEX_PAGE_SIZE', 1000),
        'csv_url' => env(
            'WORLD_PORT_INDEX_CSV_URL',
            'https://ckan.rdas.live/dataset/ef461b79-7a50-4ffc-8327-31d71a690c6b/resource/23538e38-830f-4df1-b69d-4469fa6ee7af/download/updatedpub150.csv'
        ),
        'verify_ssl' => (bool) env('WORLD_PORT_INDEX_VERIFY_SSL', false),
        'fallback_file' => env('WORLD_PORT_INDEX_FALLBACK_FILE', 'ports.json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
