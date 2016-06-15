<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use DB;
use Auth;
use App\Models\Network\networkTab;
use App\Models\Reason;
use App\Models\App;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Building\Building;


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

//    return DB::select('SELECT u.first_name, u.last_name, u.email, u.alias, p.name, a.name, a.url, a.icon
//                        FROM users U
//                          JOIN profiles P
//                            ON U.id_profiles = P.id
//                          JOIN access_apps AA
//                            ON AA.id_profiles = P.id
//                          JOIN Apps A
//                            ON A.id = AA.id_apps
//                              GROUP BY a.url
//                               ORDER BY A.id ASC;
//                        ');
    return App::all();
  }
  public function getUserData()
  {
    return Auth::user();
  }
  public function adminusers()
  {
    $usersData = User::orderBy('id', 'ASC')->get();
    return $usersData;

    return view('admin.users', ['users' => $usersData]);
  }
  public function getClientsSearch(Request $request){
    //3 = Client
    return Customer::where('id_types', 3)->where(function ($query) use ($request) {
                      $query->where('first_name', 'LIKE','%'.$request->string.'%')
                            ->orWhere('last_name', 'LIKE','%'.$request->string.'%')
                            ->orWhere('email', 'LIKE','%'.$request->string.'%');
                            })->take(200)
                              ->get();
  }
  public function getCustomersSearch(Request $request){
    return Customer::where('first_name', 'LIKE','%'.$request->string.'%')
                      ->orWhere('last_name', 'LIKE','%'.$request->string.'%')
                      ->orWhere('email', 'LIKE','%'.$request->string.'%')
                      ->take(200)
                      ->get();
  }
  public function getTicketsSearch(Request $request){
    return Ticket::where('ticket_number', 'LIKE','%'.$request->string.'%')
                    ->where('status','!=', 'closed')
                    ->take(200)
                    ->get();
  }
  public function getBuildingsSearch(Request $request){
    return Building::where('name', 'LIKE','%'.$request->string.'%')
                      ->orWhere('nickname', 'LIKE','%'.$request->string.'%')
//                      ->orWhere('address', 'LIKE','%'.$request->string.'%')
                      ->orWhere('code', 'LIKE','%'.$request->string.'%')
                      ->orWhere('legal_name', 'LIKE','%'.$request->string.'%')
                      ->take(200)
                      ->get();
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
    return networkTab::get();


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

