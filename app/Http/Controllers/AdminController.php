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
  public function adminProfile()
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
