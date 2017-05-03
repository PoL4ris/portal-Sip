<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use DB;
use Auth;
//use App\Models\Network\networkTab;
use App\Models\NetworkTab;
use App\Models\Reason;
use App\Models\App;
use App\Models\AccessApp;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Address;
use App\Models\Building;
use App\Models\Status;
use App\Models\User as Users;


class MainController extends Controller
{

  public function __construct() {
    $this->middleware('auth');
    DB::connection()->enableQueryLog();

  }

  public function main(Request $request)
{
  return view('main.snippet.main');
}

  public function homeView()
  {
    return view('index');
  }
  public function test()
  {
    return view('test');

  }
  public function menuMaker()
  {

  $result = Users::with('accessApps', 'accessApps.apps')->find(Auth::user()->id);
  return $result;

  print '<pre>';
  dd($result->toArray());
//  print_r($menuMaker->toArray());
  die();

    return Users::with('accessApps', 'accessApps.apps')->find(Auth::user()->id);

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

  public function getCustomerCodeSearch(Request $request){
    $stringA = explode("#", $request->string)[0];
    $string = explode(" ", $stringA)[0];

    return Address::with('customer')
                    ->where('code', 'LIKE','%'.$string.'%' )
                    ->orWhere('unit', 'LIKE','%'.$string.'%' )
                    ->take(20)
                    ->get();
  }
  public function getCustomersSearch(Request $request){
    $stringA = explode("#", $request->string)[0];
    $string = explode(" ", $stringA)[0];
    return Customer::where('first_name', 'LIKE','%'.$string.'%')
                      ->orWhere('last_name', 'LIKE','%'.$string.'%')
                      ->orWhere('email', 'LIKE','%'.$string.'%')
                      ->take(20)
                      ->get();
  }
  public function getCustomersPreview(Request $request)
  {
    $input = $request->all();
    print '<pre>';

    $customersData = Customer::with('building', 'address', 'contact', 'services', 'type')
                      ->where('first_name', 'LIKE', '%' . $input['string'] . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $input['string'] . '%')
                      ->orWhere('email', 'LIKE', '%' . $input['string'] . '%')
                      ->take(2)
                      ->get();

    print_r($customersData->toArray());















    die();
  }
  public function getBuildingsSearch(Request $request){
    $stringA = explode("#", $request->string)[0];
    $string = explode(" ", $stringA)[0];
    return Building::where('name', 'LIKE','%'.$string.'%')
                      ->orWhere('nickname', 'LIKE','%'.$string.'%')
                      ->orWhere('code', 'LIKE','%'.$string.'%')
                      ->orWhere('legal_name', 'LIKE','%'.$string.'%')
                      ->take(20)
                      ->get();
  }

  public function getCustomerPoundSearch(Request $request)
  {
    $string = explode("#", $request->string);
    if (isset($string[1]))
    {
      return Address::with('customer')
        ->where('code', 'LIKE','%'.explode(" ", $string[0])[0].'%' )
        ->where('unit', 'LIKE','%'.$string[1].'%' )
        ->take(20)
        ->get();
    }
    return 'ERROR';
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
    $salesData = DB::select('SELECT Priority, Status, City, 
                                    Neighborhood, Code, INT_Wiring, 
                                    ShortName, ContactName, ContactPhone, 
                                    ContactEmail, MgmtCo, BuiltDate, Units, 
                                    Floors 
                                      FROM salesPropertyInfo 
                                        ORDER BY Priority ASC');

    return view('sales.dashboard', ['salesdata' => $salesData]);
  }

  public function networkDashboard()
  {
    return NetworkTab::get();


//    return view('network.dashboard', ['networkdata' => $data]);
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
  public function getStatus(){
    return Status::all();
  }
  public function getBuildingLocations(){
    return Address::groupBy('id_buildings')->get();
  }

  public function dummyRouteController(){
    $data = array();
    $data['open_tickets'] = Ticket::where('status','!=','closed')->count();
    return $data;
  }

}

