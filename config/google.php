<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The Client ID can be found in the OAuth Credentials under Service Account
    |
    */
    //'client_id' => 'sipcal2@api-project-1056896948311.silverip.com.iam.gserviceaccount.com',
    'client_id' => 'SIPCal2',
    /*
    |--------------------------------------------------------------------------
    | Service account name
    |--------------------------------------------------------------------------
    |
    | The Service account name is the Email Address that can be found in the
    | OAuth Credentials under Service Account
    |
    */
    'service_account_name' => 'sipcal2@api-project-1056896948311.silverip.com.iam.gserviceaccount.com',

    /*
    |--------------------------------------------------------------------------
    | Key file location
    |--------------------------------------------------------------------------
    |
    | This is the location of the .p12 file from the Laravel root directory
    |
    */
    'key_file_location' => '/resources/assets/GoogleCalKey.p12',

    /*
    Pre-Defined Calendar Locations
    */
    
    'problem_appointment' => 'silverip.com_e5glc3dbassqckgva13d3qg7ic@group.calendar.google.com',
    'completed_appointment' => 'silverip.com_tpbi296lb5hldljngg6fcmjsac@group.calendar.google.com',
    'onsite_appointment' => 'silverip.com_elc3ctcfdgle90b5jntqlpfnh8@group.calendar.google.com',
    'cancelled_appointment' => 'silverip.com_jgg5r8u7ohr8n8456nrt71m9lk@group.calendar.google.com',
    'ooo_appointment' => 'silverip.com_prbvgck5lsdotakb8tbsntu5o4@group.calendar.google.com',
    'schedule_appointment' => 'silverip.com_drmputqi6m147uhrr5d5p4du3g@group.calendar.google.com',
    //'pending_appointment' => 'sipcal2@api-project-1056896948311.silverip.com.iam.gserviceaccount.com',
    'pending_appointment' => 'help@silverip.com',


];

