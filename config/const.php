<?php

return [
    'status' => [
        'active' => env('STATUS_ACTIVE', 1),
        'disabled' => env('STATUS_DISABLED', 2),
        'new' => env('STATUS_NEW', 3),
        'decommissioned' => env('STATUS_DECOMMISSIONED', 4),
    ],
    'type' => [
        'Internet' => 1,
        'Phone' => 2,
        'Phone-Option' => 3,
        'Customer Router' => 4,
        'Ethernet Jack' => 5,
        'Other' => 6,
        'Router' => 7,
        'Switch' => 8,
        'Credit Card' => 9,
        'Debit Card' => 10,
        'Cable Run' => 11,
        'Activation Fee' => 12,
        'Autopay' => 13,
        'Manual Pay' => 14,
    ],
    'contact_type' => [
        'Mobile Phone' => 1,
        'Home Phone' => 2,
        'Fax' => 3,
        'Work Phone' => 4,
        'Email' => 5,
    ],
    'building_properties' => [
        'Type' => '1',
        'Units' => '2',
        'Service Type' => '3',
        'Contract Expires' => '4',
        'Mgmt Company' => '5',
        'Ethernet' => '6',
        'Wireless' => '7',
        'Speeds' => '8',
        'Billing' => '9',
        'Email Service' => '10',
        'IP' => '11',
        'DNS' => '12',
        'Gateway' => '13',
        'How To Connect' => '14',
        'Description' => '15',
        'Support Number' => '16',
        'Image' => '17',
    ]

];
