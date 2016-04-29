<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use App\Models\Network\networkTab;
use DB;


class MainController extends Controller
{

  public function __construct() {
    $this->middleware('auth');
  }

    public function main(Request $request)
    {
      return view('main.snippet.main');
    }

    public function dummy()
    {
      return view('dummy');

    }
    
    public function adminusers()
    {
      $usersData = User::orderBy('id', 'ASC')->get();
      return $usersData;

      return view('admin.users', ['users' => $usersData]);
    }

    public function updateUser(Request $request)
    {

      if(empty($request->password))
        DB::table('users')->where('id', $request->id)->update(array('name' => $request->name, 'email' => $request->email, 'access' => strtolower($request->access), 'role' => strtolower($request->role), 'updated_at' => date("Y-m-d H:i:s")));
      else
        DB::table('users')->where('id', $request->id)->update(array('name' => $request->name, 'email' => $request->email, 'password' => password_hash($request->password, PASSWORD_BCRYPT), 'access' => strtolower($request->access), 'role' => strtolower($request->role), 'updated_at' => date("Y-m-d H:i:s")));
      
      return redirect('/adminusers');
    }
    public function salesdashboard()
    {
      $salesData = DB::select('SELECT Priority, Status, City, Neighborhood, Code, INT_Wiring, ShortName, ContactName, ContactPhone, ContactEmail, MgmtCo, BuiltDate, Units, Floors 
                                FROM salesPropertyInfo 
                                  ORDER BY Priority ASC');

      return view('sales.dashboard', ['salesdata' => $salesData]);
    }

    public function networkDashboard()
    {
      $data = networkTab::get();


      return view('network.dashboard', ['networkdata' => $data]);
    }

}

