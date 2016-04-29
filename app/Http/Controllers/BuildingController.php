<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Html;
use App\Http\Requests;
use App\Models\Building\Building;
use App\Models\Building\ServiceLocation;
use App\Models\Building\Properties;
use App\Models\Building\Contact;
use App\Models\Building\Neighborhood;
use App\Http\Controllers\Lib\FormsController;
use Redirect;

class BuildingController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }
  public function dashboard()
  {
    $offset = 0;
    $limit = 20;
    return view('buildings.dashboard', ['offset' => $offset, 'limit' => $limit]);
  }

  public function buildings(Request $request)
  {
    // Params to display bld's list's
    $offset = 0;
    $limit = 20;

    //Check if specific Building.
    //Get info Building
    if ($request->id)
//      $building = Building::where('id', $request->id)->get();
      $building = ServiceLocation::where('LocID', $request->id)->get();
//BUILDING TABLE
    else
      $building = ServiceLocation::orderBy('LocID', 'desc')->get();
//      $building = Building::orderBy('id', 'desc')->get();
//BUILDING TABLE

    //Get nbg->building->name
//    $bldNeigh = Neighborhood::where('id', $building[0]->id_neighborhood)->get();
//BUILDING TABLE

    //List of nbg's
//    $neighborhoodList = $this->getNeighborhoodList();
    //BUILDING TABLE
    //BuildingsDataList
    $buildingsList = DB::table('serviceLocation')->skip($offset)->take($limit)->get();
//    $buildingsList = DB::table('building')->skip($offset)->take($limit)->get();
//BUILDING TABLE
    //BuildingsType
//    $buildingsTypes = $this->getBuildingsType();
//BUILDING TABLE
//    $buildingsInfo = $this->getBuildingsInfo($building[0]->id);
//BUILDING TABLE
//    $building[0]->neighborhoodname = $bldNeigh[0]->name;
//BUILDING TABLE

    return view('buildings.buildings', ['building' => $building[0],
//                'neighborhood' => $neighborhoodList,
//BUILDING TABLE
//                'exist' => $buildingsInfo['exist'],
//                'properties' => $buildingsInfo['properties'],
//                'contact' => $buildingsInfo['contact'],
//BUILDING TABLE
                'buildingList' => $buildingsList,
//                'retail' => $buildingsTypes['retail'],
//                'comer' => $buildingsTypes['comer'],
//BUILDING TABLE
                'offset' => $offset,
                'limit' => $limit
              ]);
  }
  public function newbuildingform()
  {
    $dynamicForm = new FormsController();
    $data = $dynamicForm->getFormType('building');
    return view('buildings.newbuildingform',['form' => $data]);
  }


  //Building API
  //Building GET's
  public function getNeighborhoodList()
  {
    return Neighborhood::all();
  }
  public function getBuildingsSearchSimple(Request $request)
  {
    if($request->ajax())
    {
      $txt = $request['querySearch'];

      if(empty($txt))
        return;

//      $buildingData = Building::where('name', 'LIKE', '%'. $txt .'%')->get();
//BUILDING TABLE

      $buildingData = ServiceLocation::where('Name', 'LIKE', '%'. $txt .'%')->get();

      return json_encode($buildingData);
    }
    return "ERROR:";

  }
  public function getBuildingsType()
  {
    $retail = Building::where('type','retail')->get();
    $comer = Building::where('type','comercial')->get();
    $data = array('retail' => $retail, 'comer' => $comer);

    return $data;
  }
  public function getBuildingsInfo($id)
  {
    $exist = true;
    $properties = Properties::where('building_properties.id_building',$id )->get();
    $contact = Contact::where('building_contact.id_building',$id)->get();

    if (!$properties->first() && !$contact->first())
      $exist = false;

    $data = array('properties' => $properties, 'contact' => $contact, 'exist' => $exist);

    return $data;
  }
  public function getBuildingsList(Request $request)
  {
    if($request->ajax())
    {
      $offset = $request['offset'];
      $limit = 20;

      $buildingList = DB::table('building')->skip($offset)->take($limit)->get();

      if(!empty($buildingList))
        return json_encode($buildingList);
    }
    else
      return '';
  }

  //Building Insert's
  public function insertBuildingData(Request $request)
  {
    $input = $request->all();
    $input['img_building'] = '1.png';

    DB::table('building')->insert($input);

    return redirect('/buildings');
  }
  //Building Update's
  public function updateBuilding(Request $request)
  {

    $input = $request->all();
    $input['img_building'] = '1.png';
    print '<pre>';
    print_r($input);
    die();
  }



}
