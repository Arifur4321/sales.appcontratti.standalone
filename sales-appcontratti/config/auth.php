<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'sales_details', // Update passwords to use the sales_details provider
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'sales_details', // Update the provider to use the sales_details provider
        ],
    ],

    'providers' => [
        'sales_details' => [ // Rename the users provider to sales_details
            'driver' => 'eloquent',
            'model' => App\Models\SalesDetails::class, // Update the model to SalesDetails
        ],
    ],

    'passwords' => [
        'sales_details' => [ // Rename the users passwords to sales_details
            'provider' => 'sales_details', // Update the provider to use the sales_details provider
            'table' => 'password_resets', // Ensure the correct table name if it's shared with users table
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];


 