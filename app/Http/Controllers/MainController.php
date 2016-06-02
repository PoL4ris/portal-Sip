<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use DB;
use Auth;
use App\Models\Network\networkTab;
use App\Models\Reason;


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
  public function test()
  {
    return view('test');

  }
  public function menuMaker()
  {
    //PROFILES MENU
//      return DB::select('SELECT u.first_name, u.last_name, u.email, u.alias, p.name, a.name, a.url, a.icon
//                          FROM users U
//                            JOIN profiles P
//                              ON U.id_profiles = P.id
//                            JOIN access_apps AA
//                              ON AA.id_profiles = P.id
//                            JOIN Apps A
//                              ON A.id = AA.id_apps
//                            WHERE U.id = ' . Auth::user()->id . ' ORDER BY A.id ASC;
//                          ');
    return DB::select('SELECT u.first_name, u.last_name, u.email, u.alias, p.name, a.name, a.url, a.icon
                        FROM users U
                          JOIN profiles P
                            ON U.id_profiles = P.id
                          JOIN access_apps AA
                            ON AA.id_profiles = P.id
                          JOIN Apps A
                            ON A.id = AA.id_apps
                              GROUP BY a.url
                               ORDER BY A.id ASC;
                        ');
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

  public function getTableData(Request $request)
  {
    switch ($request->table)
    {
      case 'reasons':
        return Reason::all();
    }
    
  }
  public function getReasonsData()
  {
    return Reason::all();
  }
}

