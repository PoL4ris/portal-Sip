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
Route::get('getSignupProducts',  'SignupController@getSignupProducts');
Route::get('process-lease',        'DhcpController@processLease');


Route::group(['middleware' => 'web'], function () {

  Route::auth();
  Route::get('calendar',                'CalendarController@index');
  Route::get('/',                       'MainController@homeView');
  Route::get('',                        'MainController@homeView');
  Route::get('home',                    'HomeController@index');
  Route::get('test',                    'MainController@test');
  //ADMIN
  Route::get('admin',                   'AdminController@admin');
  Route::get('adminStatus',             'AdminController@adminStatus');
  Route::get('adminElements',           'AdminController@adminElements');
  Route::get('adminApps',               'AdminController@adminApps');
  Route::get('adminProfiles',           'AdminController@adminProfiles');
  Route::get('adminTypes',              'AdminController@adminTypes');
  Route::get('adminCustomers',          'AdminController@adminCustomers');
  Route::get('adminAddress',            'AdminController@adminAddress');
  Route::get('adminContacts',           'AdminController@adminContacts');
  Route::get('adminPayments',           'AdminController@adminPayments');
  Route::get('adminNotes',              'AdminController@adminNotes');
  Route::get('adminAccessApps',         'AdminController@adminAccessApps');
  Route::get('adminAccessAppElements',  'AdminController@adminAccessAppElements');
  Route::get('getAdminForm',            'AdminController@getAdminForm');
  Route::get('insertAdminForm',         'AdminController@insertAdminForm');
  Route::get('getProfileInfo',          'AdminController@getProfileInfo');
  Route::get('updateProfileInfo',       'AdminController@updateProfileInfo');
  //MENU
  Route::get('menumaker',               'MainController@menuMaker');
  Route::get('adminusers',              'MainController@adminusers');
  Route::get('getUserData',             'MainController@getUserData');
  //Dashboards
  Route::get('buildingsdash',           'BuildingController@dashboard');
  Route::get('supportdash/{filter?}',   'SupportController@dashboard');
  Route::get('salesdash',               'MainController@salesdashboard');
  Route::get('networkdash',             'MainController@networkDashboard');
  Route::get('supportTickets',          'SupportController@supportTickets');
  Route::get('supportTicketsBilling',   'SupportController@supportTicketsBilling');
  Route::get('supportTicketsAll',       'SupportController@supportTicketsAll');
  Route::get('supportTicketHistory',    'SupportController@supportTicketHistory');
  //Search
  Route::get('buildingsSearch',         'BuildingController@getBuildingsSearchSimple');
  Route::get('customersSearch',         'CustomerController@getCustomersSearch');
  Route::get('getCustomersPreview',     'MainController@getCustomersPreview');
  //GLOBAL SEARCH
  Route::get('getCustomerCodeSearch',   'MainController@getCustomerCodeSearch');
  Route::get('getCustomersSearch',      'MainController@getCustomersSearch');
  Route::get('getTicketsSearch',        'MainController@getTicketsSearch');
  Route::get('getBuildingsSearch',      'MainController@getBuildingsSearch');
  Route::get('getCustomerPoundSearch',  'MainController@getCustomerPoundSearch');

  //Buildings & List
  Route::get('buildings/{id?}',         'BuildingController@buildings');
  Route::get('buildingsList',           'BuildingController@getBuildingsList');
  Route::get('getBuildingsListTMP',           'BuildingController@getBuildingsListTMP');//new
  //Building Form
  Route::get('newbuildingform',         'BuildingController@newbuildingform');
  //Insert Building
  Route::post('insertbuildingData',     'BuildingController@insertBuildingData');
  Route::get('insertBuildingProperties','BuildingController@insertBuildingProperties');
  Route::get('insertBuildingContacts',  'BuildingController@insertBuildingContacts');
  //Update Building
  Route::get('updateBuilding',          'BuildingController@updateBuilding');
  Route::get('updateBldPropValTable',   'BuildingController@updateBldPropValTable');
  Route::get('updateBldContactTable',   'BuildingController@updateBldContactTable');
  Route::post('userupdate',             'MainController@updateUser');
  //Customers
  Route::get('customers/{id?}',         'CustomerController@customers');
  Route::post('updateCustomerData',     'CustomerController@updateCustomerData');
  Route::get('insertCustomerService',   'CustomerController@insertCustomerService');
  Route::get('disableCustomerServices', 'CustomerController@disableCustomerServices');
  Route::get('activeCustomerServices',  'CustomerController@activeCustomerServices');
  Route::get('updateCustomerServices',  'CustomerController@updateCustomerServices');
  Route::get('getCustomerStatus',       'CustomerController@getCustomerStatus');//new
  //Customer Billing
  Route::get('refundAmount',            'BillingController@refund');
  Route::get('chargeAmount',            'BillingController@charge');
  Route::get('insertPaymentMethod',     'BillingController@insertPaymentMethod');
  //UpdateCustomer
  Route::get('updateAddressTable',      'CustomerController@updateAddressTable');
  Route::get('updateCustomersTable',    'CustomerController@updateCustomersTable');
  Route::get('updateContactsTable',     'CustomerController@updateContactsTable');
  Route::get('updateContactInfo',       'CustomerController@updateContactInfo');
  Route::get('updatePaymentMethods',    'CustomerController@updatePaymentMethods');
  Route::get('insertContactInfo',       'CustomerController@insertContactInfo');
  Route::post('insertCustomerData',     'CustomerController@insertCustomerData');
  //New Ticket
  Route::get('insertCustomerTicket',    'CustomerController@insertCustomerTicket');
  Route::get('customersData',           'CustomerController@customersData');
  Route::get('getCustomerContactData',  'CustomerController@getCustomerContactData');
  Route::get('getCustomerPayment',      'CustomerController@getCustomerPayment');
  Route::get('getNewTicketData',        'CustomerController@getNewTicketData');
  Route::get('getTicketHistory',        'CustomerController@getTicketHistory');
  Route::get('getTicketHistoryNotes',   'CustomerController@getTicketHistoryNotes');//
  Route::get('getTicketHistoryReason',  'CustomerController@getTicketHistoryReason');//
  Route::get('getBillingHistory',       'CustomerController@getBillingHistory');
  Route::get('getPaymentMethods',       'CustomerController@getPaymentMethods');
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

  //LOGS
  Route::get('getCustomerLog',          'CustomerController@getCustomerLog');//new

  //SUPPORT
  Route::get('updateTicketDetails',     'SupportController@updateTicketData');
  Route::get('updateTicketHistory',     'SupportController@updateTicketHistory');
  Route::get('getTicketInfo',           'SupportController@getTicketInfo');
  Route::get('getTicketCustomerList',   'SupportController@getTicketCustomerList');
  Route::get('updateTicketCustomerName','SupportController@updateTicketCustomerName');

  //Network functions
  Route::get('networkCheckStatus',      'NetworkController@getSwitchPortStatus');
  Route::get('netwokAdvancedInfo',      'NetworkController@getAdvSwitchPortStatus');
  Route::get('networkRecyclePort',      'NetworkController@recycleSwitchPort');
  Route::get('networkSignUp',           'NetworkController@authenticatePort');
  Route::get('networkActivate',         'NetworkController@activatePort');
  Route::get('test3',                   'NetworkController@getRouterInfoByPortID');
  Route::get('networkAdvanceIPs',       'NetworkController@getPortActiveIPs');
  Route::get('test6',                   'NetworkController@authenticatePort');
  Route::get('test7',                   'NetworkController@activatePort');

  //DASHBOARDCHARTS
  Route::get('getTicketsByMonth',                 'ChartController@getTicketsByMonth');
  Route::get('getSignedUpCustomersByYear',        'ChartController@getSignedUpCustomersByYear');
  //UPDATE SERVICES
  Route::get('updateCustomerServiceInfo',         'CustomerController@updateCustomerServiceInfo');
  Route::get('updateCustomerActiveServiceInfo',   'CustomerController@updateCustomerActiveServiceInfo');
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