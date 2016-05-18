<?php

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
Route::get('db-test', 'TestController@testDBRelations');    

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    //Route::get('/', 'HomeController@index');
    Route::get('/', 'MainController@dummy');
    Route::get('test', 'MainController@test');
    //ADMIN
    Route::get('admin', 'AdminController@admin');
    Route::get('adminStatus', 'AdminController@adminStatus');
    Route::get('adminElements', 'AdminController@adminElements');
    Route::get('adminApps', 'AdminController@adminApps');
    Route::get('adminProfiles', 'AdminController@adminProfiles');
    Route::get('adminTypes', 'AdminController@adminTypes');
    Route::get('adminCustomers', 'AdminController@adminCustomers');
    Route::get('adminAddress', 'AdminController@adminAddress');
    Route::get('adminContacts', 'AdminController@adminContacts');
    Route::get('adminPayments', 'AdminController@adminPayments');
    Route::get('adminNotes', 'AdminController@adminNotes');
    Route::get('adminAccessApps', 'AdminController@adminAccessApps');
    Route::get('adminAccessAppElements', 'AdminController@adminAccessAppElements');

    Route::get('getAdminForm', 'AdminController@getAdminForm');
    Route::get('insertAdminForm', 'AdminController@insertAdminForm');

    //MENU
    Route::get('menumaker', 'MainController@menuMaker');

    //Route::get('/', 'MainController@main');
    Route::get('dummy', 'MainController@dummy');
    Route::get('adminusers', 'MainController@adminusers');
    //Dashboards
    Route::get('buildingsdash', 'BuildingController@dashboard');
    Route::get('customersdash', 'CustomerController@dashboard');
    Route::get('supportdash/{filter?}', 'SupportController@dashboard');
    Route::get('salesdash', 'MainController@salesdashboard');
    Route::get('networkdash', 'MainController@networkDashboard');

    //Search
    Route::get('buildingsSearch', 'BuildingController@getBuildingsSearchSimple');
    Route::get('customersSearch', 'CustomerController@getCustomersSearch');
    //Buildings & List
    Route::get('buildings/{id?}', 'BuildingController@buildings');
    Route::get('buildingsList', 'BuildingController@getBuildingsList');
    //Building Form
    Route::get('newbuildingform', 'BuildingController@newbuildingform');
    //Insert Building
    Route::post('insertbuildingData', 'BuildingController@insertBuildingData');
    //Update Building
    Route::post('buildingupdate', 'BuildingController@updateBuilding');
    Route::post('userupdate', 'MainController@updateUser');

    //Customers
    Route::get('customers/{id?}', 'CustomerController@customers');
    Route::post('updateCustomerData', 'CustomerController@updateCustomerData');
    Route::post('insertCustomerData', 'CustomerController@insertCustomerData');
    Route::get('updateCustomerServiceInfo', 'CustomerController@updateCustomerServiceInfo');
    Route::get('updateCustomerActiveServiceInfo', 'CustomerController@updateCustomerActiveServiceInfo');

    //SUPPORT
    Route::post('updateTicketDetails', 'SupportController@updateTicketData');
    Route::post('updateTicketHistory', 'SupportController@updateTicketData');

    //Network functions
    Route::get('networkCheckStatus', 'NetworkController@getSwitchPortStatus');
    Route::get('netwokAdvancedInfo', 'NetworkController@getAdvSwitchPortStatus');
    Route::get('networkRecyclePort', 'NetworkController@recycleSwitchPort');
    Route::get('networkSignUp', 'NetworkController@authenticatePort');
    Route::get('networkActivate', 'NetworkController@activatePort');
    Route::get('test3', 'NetworkController@getRouterInfoByPortID');
    Route::get('networkAdvanceIPs', 'NetworkController@getPortActiveIPs');
    Route::get('test6', 'NetworkController@authenticatePort');
    Route::get('test7', 'NetworkController@activatePort');
    //NO ROUTE NEEDED
    Route::get('test8', 'NetworkController@getCustomerConnectionInfo');
    Route::get('cc-test', 'TestController@testCC');    

    $s = 'social.';
    Route::get('/social/redirect/{provider}',   ['as' => $s . 'redirect',   'uses' => 'Auth\AuthController@getSocialRedirect']);
    Route::get('/social/handle/{provider}',     ['as' => $s . 'handle',     'uses' => 'Auth\AuthController@getSocialHandle']);
    Route::get('auth/google', [
        'as' => 'auth/google',
        'uses' => 'Auth\AuthController@getSocialHandle'
    ]);

});