<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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
        // Dedicated SES credentials, kept separate from the AWS_* group that
        // Laravel Cloud injects for the R2 object-storage disk. Sharing AWS_*
        // would point the SES client at the bucket's credentials.
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'nps' => [
        'key' => env('NPS_API_KEY'),
        'base_url' => env('NPS_BASE_URL', 'https://developer.nps.gov/api/v1/'),
        'timeout' => (int) env('NPS_TIMEOUT', 15),
        'connect_timeout' => (int) env('NPS_CONNECT_TIMEOUT', 5),
        'retries' => (int) env('NPS_RETRIES', 2),
        'retry_delay_ms' => (int) env('NPS_RETRY_DELAY_MS', 250),
        'page_size' => (int) env('NPS_PAGE_SIZE', 200),
    ],

];
