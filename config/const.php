<?php

return [
    'status' => [
        'active' => env('STATUS_ACTIVE', 1),
        'disabled' => env('STATUS_DISABLED', 2),
        'new' => env('STATUS_NEW', 3),
        'decommissioned' => env('STATUS_DECOMMISSIONED', 4),
        'closed' => 'closed'
    ],
    'type' => [
        'internet' => 1,
        'phone' => 2,
        'phone_option' => 3,
        'customer_router' => 4,
        'ethernet_jack' => 5,
        'other' => 6,
        'router' => 7,
        'switch' => 8,
        'credit_card' => 9,
        'debit_card' => 10,
        'cable_run' => 11,
        'activation_fee' => 12,
        'auto_pay' => 13,
        'manual_pay' => 14,
    ],
    'contact_type' => [
        'mobile_phone' => 1,
        'home_phone' => 2,
        'fax' => 3,
        'work_phone' => 4,
        'email' => 5,
    ],
    'building_property' => [
        'type' => '1',
        'units' => '2',
        'service_type' => '3',
        'contract_expires' => '4',
        'mgmt_company' => '5',
        'ethernet' => '6',
        'wireless' => '7',
        'speeds' => '8',
        'billing' => '9',
        'email_service' => '10',
        'ip' => '11',
        'dns' => '12',
        'gateway' => '13',
        'how_to_connect' => '14',
        'description' => '15',
        'support_number' => '16',
        'image' => '17',
    ],
    'reason_category' => [
        'internet' => '1',
        'phone' => '2',
        'misc' => '3',
        'tv' => '4'
    ],
    'reason' => [
        'unknown' => '1',
        'slow_internet' => '2',
        'no_internet_access' => '3',
        'email_setup' => '4',
        'game_console' => '5',
        'vpn' => '12',
        'static_ip' => '13',
        'voip' => '14',
        'other' => '17',
        'billing' => '18',
        'registration_page' => '21',
        'dtv' => '22',
        'add_move_jack' => '23',
        'sales' => '24',
        'notice' => '25',
        'router_new_purchase' => '27',
        'router_config' => '28',
        'internal_billing' => '29',
        'move_in_howto_connect' => '30'
    ],

];
