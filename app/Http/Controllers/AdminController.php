<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Lib\FormsController;
use App\Models\Profiles;
use App\Models\Status;





class AdminController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }

  //FUNCTIONS
  public function admin()
  {
    return DB::select('select * from users');
  }
  public function adminProfiles()
  {
    return Profiles::All();
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
    return DB::select('select * from customers');
  }
  public function adminAddress()
  {
    return DB::select('select * from address');
  }
  public function adminContacts()
  {
    return DB::select('select * from contacts');
  }
  public function adminPayments()
  {
    return DB::select('select * from payments');
  }
  public function adminNotes()
  {
    return DB::select('select * from notes');
  }
  public function adminAccessApps()
  {
    return DB::select('select * from access_apps');
  }
  public function adminAccessAppElements()
  {
    return DB::select('select * from access_app_elements');
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
