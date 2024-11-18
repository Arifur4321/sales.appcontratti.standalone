<?php



return [
    'base_uri' => env('DOCUSIGN_BASE_URI', 'https://account-d.docusign.com'),
    'integration_key' => env('DOCUSIGN_INTEGRATION_KEY'),
    'user_id' => env('DOCUSIGN_USER_ID'),
    'account_id' => env('DOCUSIGN_ACCOUNT_ID'),
    'private_key' => env('DOCUSIGN_PRIVATE_KEY_PATH'),
    'redirect_uri' => env('DOCUSIGN_REDIRECT_URI'),
    'expires_in' => 3600,
];
