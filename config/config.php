<?php

return [
    'upload' => [
        'document' => 'esign/{document}',
        'signer_snapshot' => 'esign/{document}/signer/{signer}/snapshots',
        'signer_element' => 'esign/{document}/signer/{signer}/elements/{element}',
    ],
    'expressions' => [
        'uuid' => '/^[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}$/',
    ],
    'intervals' => [
        'notify' => 5000,
        'heartbeat' => 6000,
    ],
    'defaults' => [
        'document_status' => \NIIT\ESign\Enum\DocumentStatus::DRAFT,
        'notification_sequence' => \NIIT\ESign\Enum\NotificationSequence::ASYNC,
    ],
    'certificate' => [
        'path' => storage_path('app/esign/certificate.crt'),
        'private_key_path' => storage_path('app/esign/private_key.pem'),
        'password' => 'password',
        'level' => 2,
        'info' => [
            'Name' => 'Anand',
            'Location' => 'Office',
            'Reason' => 'ESign test',
            'ContactInfo' => 'https://google.com',
        ],
    ],
    'signing_headers' => [
        'ESign' => 'XYZ',
    ],
    'services' => [
        'ipstack_key' => '039a88e1607c2a618fed7e96252ba923',
        'gmaps_key' => null,
    ],
];
