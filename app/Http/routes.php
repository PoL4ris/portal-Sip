<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



//DB functions
Route::get('activity-log-test',  'TestController@testActivityLog');
Route::get('db-test',            'TestController@testDBRelations');
Route::get('supportTest',        'TestController@supportTest');
Route::get('logFunction',        'TestController@logFunction');
Route::get('testView',           'TestController@cleanView');
Route::get('testTickets',        'TestController@testCustomerTickets');
Route::get('process-lease',      'DhcpController@processLease');
Route::get('invoiceTest',        'TestController@invoiceTest');
Route::get('mail',               'TestController@mail');
Route::get('migrate',            'TestController@testDataMigration');
Route::get('gTest',              'TestController@generalTest');
Route::get('netTest',            'NetworkController@getPrivateVlanByPort');

//Route::get('getSignupProducts',  'SignupController@getSignupProducts');


Route::group(['middleware' => 'web'], function () {

    Route::auth();
    Route::get('calendar',                'CalendarController@index');
    Route::get('/',                       'MainController@homeView');
    Route::get('',                        'MainController@homeView');
    Route::get('home',                    'HomeController@index');
    //ADMIN
    Route::get('admin',                   'AdminController@admin');
    //POST ADMIN
    Route::post('getAdminUsers',          'AdminController@getAdminUsers');
    Route::post('getAdminProfiles',       'AdminController@getAdminProfiles');
//    Route::post('getAdminProfile',        'AdminController@getAdminProfile');
    Route::post('updateAdminUser',        'AdminController@updateAdminUser');
    Route::post('insertAdminUser',        'AdminController@insertAdminUser');
    Route::post('getAdminApps',           'AdminController@getAdminApps');
    Route::post('insertNewProfile',       'AdminController@insertNewProfile');
    Route::post('getAppAccess',           'AdminController@getAppAccess');
    Route::post('updateAdminProfile',     'AdminController@updateAdminProfile');
    Route::post('insertNewApp',           'AdminController@insertNewApp');
    Route::post('updateAdminApp',         'AdminController@updateAdminApp');
    Route::post('getAdminBldProperties',  'AdminController@getAdminBldProperties');
    Route::post('insertNewBldProperty',   'AdminController@insertNewBldProperty');
    //PROFILE
    Route::get('getProfileInfo',          'AdminController@getProfileInfo');
    Route::get('updateProfileInfo',       'AdminController@updateProfileInfo');
    //MENU
    Route::get('menumaker',               'MainController@menuMaker');

    Route::get('getUserData',             'MainController@getUserData');
    //Dashboards
//    Route::get('buildingsdash',           'BuildingController@dashboard');
    Route::get('supportdash/{filter?}',   'SupportController@dashboard');
    Route::get('networkdash',             'MainController@networkDashboard');


    Route::get('getAllOpenTickets',       'SupportController@getAllOpenTickets');
    Route::get('getNoneBillingTickets',   'SupportController@getNoneBillingTickets');
    Route::get('getBillingTickets',       'SupportController@getBillingTickets');
    Route::get('getMyTickets',            'SupportController@getMyTickets');


    Route::get('supportTicketHistory',    'SupportController@supportTicketHistory');
    //Search
    Route::get('buildingsSearch',         'BuildingController@getBuildingsSearchSimple');
    Route::get('customersSearch',         'CustomerController@getCustomersSearch');
    Route::get('getGenericSearch',        'CustomerController@getGenericSearch');//new
    Route::get('getFilterBld',            'BuildingController@getFilterBld');//new
    //GLOBAL SEARCH

    Route::get('getTicketsSearch',        'SupportController@getTicketsSearch');

    //Buildings & List
    Route::get('buildings/{id?}',         'BuildingController@buildings');
    Route::get('buildingData',            'BuildingController@buildingData');
    Route::get('getBuildingsList',        'BuildingController@getBuildingsList');//new

    Route::get('getBuildingProperty',     'BuildingController@getBuildingProperty');//new
    //Insert Building
    Route::post('insertbuildingData',     'BuildingController@insertBuildingData');
    Route::get('insertBuildingProperties','BuildingController@insertBuildingProperties');
    Route::get('insertBuildingContacts',  'BuildingController@insertBuildingContacts');
    //Update Building
    Route::get('updateBuilding',          'BuildingController@updateBuilding');
    Route::get('updateBldPropValTable',   'BuildingController@updateBldPropValTable');
    Route::get('updateBldContactTable',   'BuildingController@updateBldContactTable');
    //Customers
    Route::get('customers/{id?}',         'CustomerController@customers');
    Route::get('insertCustomerService',   'CustomerController@insertCustomerService');
    Route::get('disableCustomerServices', 'CustomerController@disableCustomerServices');
    Route::get('activeCustomerServices',  'CustomerController@activeCustomerServices');
    Route::get('updateCustomerServices',  'CustomerController@updateCustomerServices');
    Route::get('getCustomerStatus',       'CustomerController@getCustomerStatus');//new
    Route::get('insertCustomerNote',      'CustomerController@insertCustomerNote');//new
    Route::get('getCustomerNotes',        'CustomerController@getCustomerNotes');//new
    Route::get('resetCustomerPassword',   'CustomerController@resetCustomerPassword');//new
    //Customer Billing
    Route::get('refundAmount',            'BillingController@refund');
    Route::get('chargeAmount',            'BillingController@charge');
    Route::get('insertPaymentMethod',     'BillingController@insertPaymentMethod');
    Route::get('getAllPaymentMethods',    'CustomerController@getAllPaymentMethods');
    Route::get('setDefaultPaymentMethod', 'CustomerController@setDefaultPaymentMethod');
    Route::get('getDefaultPaymentMethod', 'CustomerController@getDefaultPaymentMethod');
    //UpdateCustomer
    Route::get('updateAddressTable',      'CustomerController@updateAddressTable');
    Route::get('updateCustomersTable',    'CustomerController@updateCustomersTable');
    Route::get('updateContactsTable',     'CustomerController@updateContactsTable');
    Route::get('updateContactInfo',       'CustomerController@updateContactInfo');
    Route::get('insertContactInfo',       'CustomerController@insertContactInfo');
    //New Ticket
    Route::get('insertCustomerTicket',    'CustomerController@insertCustomerTicket');
    Route::get('customersData',           'CustomerController@customersData');
    Route::get('getCustomerContactData',  'CustomerController@getCustomerContactData');
    Route::get('getNewTicketData',        'CustomerController@getNewTicketData');
    Route::get('getTicketHistory',        'CustomerController@getTicketHistory');
    Route::get('getTicketHistoryNotes',   'CustomerController@getTicketHistoryNotes');
    Route::get('getTicketHistoryReason',  'CustomerController@getTicketHistoryReason');
    Route::get('getInvoiceHistory',       'CustomerController@getInvoiceHistory');
    Route::get('getBillingHistory',       'CustomerController@getBillingHistory');

    Route::get('getCustomerNetwork',      'CustomerController@getCustomerNetwork');
    Route::get('getCustomerServices',     'CustomerController@getCustomerServices');
    Route::get('getCustomerProduct',      'CustomerController@getCustomerProduct');
    Route::get('getCustomerProductType',  'CustomerController@getCustomerProductType');
    Route::get('getCustomerBuilding',     'CustomerController@getCustomerBuilding');
    Route::get('getCustomerDataTicket',   'CustomerController@getCustomerDataTicket');

    //ALL() CALL
    Route::get('getTableData',            'MainController@getTableData');
    Route::get('getReasonsData',          'MainController@getReasonsData');
    Route::get('getCustomerList',         'CustomerController@getCustomerList');
    Route::get('getTicketOpenTime',       'SupportController@getTicketOpenTime');
    Route::get('getAvailableServices',    'SupportController@getAvailableServices');
    Route::get('getContactTypes',         'CustomerController@getContactTypes');
    Route::get('getAddress',              'CustomerController@getAddress');
    Route::get('getBuildingProperties',   'BuildingController@getBuildingProperties');
    Route::get('getProducts',             'SupportController@getProducts');//new
    Route::get('getStatus',               'MainController@getStatus');//new
    Route::get('getBuildingLocations',    'MainController@getBuildingLocations');//new

    //LOGS
    Route::get('getCustomerLog',          'CustomerController@getCustomerLog');//new

    //SIGNUP
    Route::get('signup',  function() {
        dd('There is nothing here.');
    });

    Route::get('splash',                  'SignupController@getSplashPage');
    Route::post('signup',                 'SignupController@getWelcomePage');
    Route::get('form',                    'SignupController@getSignupForm');
    Route::get('getUnitNumbersAjax',      'SignupController@getUnitNumbersAjax');
    Route::post('register',               'SignupController@validateSignupForm');
    Route::post('activate',               'SignupController@activate');

    //DUMMYCONTROLLER
    Route::get('getMainDashboard',    'MainController@dashboard');//new





    //REPORTCONTROLLER -> REFACTOR NAMES...
//    Route::get('mrrProcess', 'ReportController@mrrProcess');//new
    Route::get('reports/{code?}', 'ReportController@something');//new
    Route::get('getDisplayRetailRevenue', 'ReportController@getDisplayRetailRevenue');//new
    Route::get('getDisplayRetailRevenueDetails', 'ReportController@getDisplayRetailRevenueDetails');//new
    Route::get('getDisplayRetailRevenueUnitDetails', 'ReportController@getDisplayRetailRevenueUnitDetails');//new
    Route::get('getDisplayLocationStats', 'ReportController@getDisplayLocationStats');//new





    Route::get('email/template_support_new_ticket',    'TestController@supportTest');//new

    //MAILS
    Route::get('sendCustomerMail',        'MailController@sendCustomerMail');//new

    //SUPPORT
    Route::get('updateTicketDetails',     'SupportController@updateTicketData');
    Route::get('updateTicketHistory',     'SupportController@updateTicketHistory');
    Route::get('getTicketInfo',           'SupportController@getTicketInfo');
    Route::get('getTicketCustomerList',   'SupportController@getTicketCustomerList');
    Route::get('updateCustomerOnTicket',  'SupportController@updateCustomerOnTicket');

    //Network functions
    Route::get('networkCheckStatus',      'NetworkController@getSwitchPortStatus');
    Route::get('netwokAdvancedInfo',      'NetworkController@getAdvSwitchPortStatus');
    Route::get('networkRecyclePort',      'NetworkController@recycleSwitchPort');
    Route::get('networkSignUp',           'NetworkController@authenticatePort');
    Route::get('networkActivate',         'NetworkController@activatePort');

    Route::get('networkAdvanceIPs',       'NetworkController@getPortActiveIPs');
    Route::get('getSwitchStats',          'NetworkController@getSwitchPortAndNeighborInfoTable');

    Route::get('test3',                   'NetworkController@getRouterInfoByPortID');
    Route::get('test6',                   'NetworkController@authenticatePort');
    Route::get('test7',                   'NetworkController@activatePort');

    //DASHBOARDCHARTS
    Route::get('getTicketsByMonth',                 'ChartController@getTicketsByMonth');
    Route::get('getSignedUpCustomersByYear',        'ChartController@getSignedUpCustomersByYear');
    //UPDATE SERVICES

    //NO ROUTE NEEDED
    Route::get('test8',                             'NetworkController@getCustomerConnectionInfo');
    Route::get('cc-test',                           'TestController@testCC');

    $s = 'social.';
    Route::get('/social/redirect/{provider}',   ['as' => $s . 'redirect',
                                                 'uses' =>    'Auth\AuthController@getSocialRedirect']);
    Route::get('/social/handle/{provider}',     ['as' => $s . 'handle',
                                                 'uses' =>    'Auth\AuthController@getSocialHandle']);
    Route::get('auth/google', [
        'as' =>     'auth/google',
        'uses' =>   'Auth\AuthController@getSocialHandle'
    ]);
});
