<?php

namespace App\Http\Controllers;

use App\Models\AccessApp;
use App\Models\Customer;
use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Auth;
use App\Http\Controllers\Lib\FormsController;
use App\Models\Profile;
use App\Models\Status;
use App\Models\User;
use App\Models\App;


class AdminController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }

  //FUNCTIONS

  public function getAdminUsers(Request $request){

//    if ($request->params['token'] == csrf_token())
//dd($request);
      return User::get();
//    else
//    {
//      print 'ERROR';
//      return;//with error or something...
//    }
  }
  public function getAdminProfiles(Request $request){

      return Profile::with('accessApps')->get();

  }
  public function updateAdminUser(Request $request){
    if ($request->params['token'] == csrf_token()){
      $data = $request->params['objetos'];
      $user = User::find($data['id']);
      $user->first_name = $data['first_name'];
      $user->last_name = $data['last_name'];
      $user->email = $data['email'];
      $user->alias = $data['alias'];
      if(!empty($data['password']))
        $user->password = bcrypt($data['password']);

      $user->social_access = $data['social_access'];
      $user->id_status = $data['id_status'];
      $user->id_profiles = $data['id_profiles'];
      $user->save();
      return $this->getAdminUsers($request);//arreglar illuminate request
    }
    else
    {
      print 'ERROR';
      return;//with error or something...
    }
  }
  public function insertAdminUser(Request $request)
  {
    if ($request->params['token'] == csrf_token()){
      $data = $request->params['objetos'];
      $user = new User;
      $user->first_name = $data['first_name'];
      $user->last_name = $data['last_name'];
      $user->email = $data['email'];
      $user->alias = $data['alias'];
      $user->password = bcrypt($data['password']);
      $user->social_access = $data['social_access'];
      $user->id_status = $data['id_status'];
      $user->id_profiles = $data['id_profiles'];
      $user->save();
      return $this->getAdminUsers($request);//arreglar illuminate request
    }
    else
    {
      print 'ERROR';
      return;//with error or something...
    }




  }
  public function getAdminApps(Request $request){
    return App::get();
  }
  public function insertNewProfile(Request $request){

    $data = $request->params['objects'];

    $profile = new Profile;
    $profile->name = $data['profile_name'];
    $profile->save();

    unset($data['profile_name']);

    $aApps = new AccessApp;

    foreach($data as $index => $item)
    {
      $aApps = new AccessApp;
      $aApps->id_apps = $index;
      $aApps->id_profiles = $profile->id;
      $aApps->save();
    }

    return Profile::with('accessApps')->get();
  }
  public function updateAdminProfile(Request $request){
    $data = $request->params['objects'];

    $profile = Profile::find($data['id_profiles']);
    $profile->name = $data['profile_name'];
    $profile->save();

    unset($data['profile_name']);

    AccessApp::where('id_profiles', $data['id_profiles'])->delete();

    $aApps = new AccessApp;

    foreach($data as $index => $item)
    {
      $aApps = new AccessApp;
      $aApps->id_apps = $index;
      $aApps->id_profiles = $data['id_profiles'];
      $aApps->save();
    }

    return Profile::with('accessApps')->get();

  }
  public function getAppAccess(Request $request){
    $data = $request->params;
    return AccessApp::where('id_profiles', $data['id_profiles'])
                    ->where('id_apps', $data['id_apps'])
                    ->first();

    if($resultAccess)
      return 'OK';
    else
      return 'ERROR';
  }


  public function admin()
  {
    return DB::select('select * from users limit 10');
  }
  public function adminProfiles()
  {
    return Profile::All();
  }
  public function adminStatus()
  {
    return DB::select('select * from status');
  }
  public function adminElements()
  {
    return DB::select('select * from elements');
  }
  public function adminApps()
  {
    return DB::select('select * from apps');
  }
  public function adminTypes()
  {
    return DB::select('select * from types');
  }
  public function adminCustomers()
  {
    return DB::select('select * from customers limit 10');
  }
  public function adminAddress()
  {
    return DB::select('select * from address limit 10');
  }
  public function adminContacts()
  {
    return DB::select('select * from contacts limit 10');
  }
  public function adminPayments()
  {
    return DB::select('select * from payment_methods limit 10');
  }
  public function adminNotes()
  {
    return DB::select('select * from notes limit 10');
  }
  public function adminAccessApps()
  {
    return DB::select('select * from access_apps');
  }
  public function adminAccessAppElements()
  {
    return DB::select('select * from access_app_elements');
  }

  public function getProfileInfo()
  {
    return User::find(Auth::user()->id);
  }
  public function updateProfileInfo(Request $request)
  {
    $user = User::find(Auth::user()->id);
    $user->password = bcrypt($request->password);
    $user->save();
    return 'OK';
  }



  public function getAdminForm(Request $request)
  {
    $dynamicForm = new FormsController();
    $data = $dynamicForm->getFormType($request->table);
    return $data;
    return view('buildings.newbuildingform',['form' => $data]);
  }
  public function insertAdminForm(Request $request)
  {
    $data = $request->all();
    unset($data['table']);
    DB::table($request['table'])->insert($data);
  }
}
