<?php

return [
    'upload' => [
        'document' => 'esign/{id}',
        'signer' => 'esign/{id}/data/{signer}',
    ],
    'expressions' => [
        'uuid' => '/^[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}$/',
    ],
    'notify_timeout' => 5000,
];
