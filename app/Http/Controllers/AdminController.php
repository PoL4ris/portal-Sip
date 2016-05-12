<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Lib\FormsController;
use App\Models\Profiles;





class AdminController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }

  //FUNCTIONS
  public function admin()
  {
    return DB::select('select * from user');
  }
  public function adminProfile()
  {
    return Profiles::All();
  }
  public function insertAdminForm(Request $request)
  {

    $dynamicForm = new FormsController();
    $data = $dynamicForm->getFormType($request->table);
    return $data;
    return view('buildings.newbuildingform',['form' => $data]);
  }
}
