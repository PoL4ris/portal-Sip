<?php

return [
    'cisco' => [
        'read'  => env('CISCO_SNMP_READ', 'devread'),
        'write' => env('CISCO_SNMP_WRITE', 'devwrite')
    ],
    'mikrotik' => [
        'username'  => env('MIKROTIK_USERNAME', 'devadmin'),
        'password' => env('MIKROTIK_PASSWORD', 'devpass')
    ],
    'devmode' => [
        'enabled' => env('NETMGMT_DEVMODE', 'false'),
        'switchip' => env('NETMGMT_DEVMODE_SWITCHIP', ''),
        'routerip' => env('NETMGMT_DEVMODE_ROUTERIP', '')
    ]
];
