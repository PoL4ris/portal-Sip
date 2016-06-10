<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Html;
use App\Http\Requests;
use App\Models\Building\Building;
use App\Models\Building\ServiceLocation;
use App\Models\Building\BuildingProperty;
use App\Models\Building\BuildingContact;
use App\Models\Building\Neighborhood;
use App\Models\Type;
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

    $offset = 0;
    $limit = 200;

    if ($request->id)
    {
      $building = Building::where('id', $request->id)->get();
      $bldType = Type::where('id', $building[0]->id_types)->get();
      $building[0]->typename = $bldType[0]->name;
    }
    else
      $building = Building::orderBy('id', 'desc')->get();

    $bldNeigh = Neighborhood::where('id', $building[0]->id_neighborhoods)->get();
    $neighborhoodList = $this->getNeighborhoodList();
//    $typesList = $this->getTypesList();

    $buildingsList = DB::table('buildings')->skip($offset)->take($limit)->get();

    $buildingsTypes = $this->getBuildingsType();
    $buildingsInfo = $this->getBuildingsInfo($building[0]->id);
    $building[0]->neighborhoodname = $bldNeigh[0]->name;


    $data = ['building'     => $building[0],
             'neighborhood' => $neighborhoodList,
//             'types'        => $typesList,
             'exist'        => $buildingsInfo['exist'],
             'properties'   => $buildingsInfo['properties'],
             'contact'      => $buildingsInfo['contact'],
             'buildingList' => $buildingsList,
             'retail'       => $buildingsTypes['retail'],
             'comer'        => $buildingsTypes['comer'],
             'offset'       => $offset,
             'limit'        => $limit
    ];

    
    return $data;
  }
  public function newbuildingform()
  {
    $dynamicForm = new FormsController();
    $data = $dynamicForm->getFormType('buildings');
    return $data;
    return view('buildings.newbuildingform',['form' => $data]);
  }


  //Building GET's
  public function getNeighborhoodList()
  {
    return Neighborhood::all();
  }
  //NOT IN USE ANYMORE id_types changed to TYPE
  public function getTypesList()
  {
    return Type::all();
  }
  public function getBuildingsSearchSimple(Request $request)
  {

      $txt = $request['querySearch'];

      if(empty($txt))
        return;

      $buildingData = Building::where('name', 'LIKE', '%'. $txt .'%')->get();
      //BUILDING TABLE

//      $buildingData = ServiceLocation::where('Name', 'LIKE', '%'. $txt .'%')->get();

      return $buildingData;
      
      return json_encode($buildingData);


  }
  public function getBuildingsType()
  {
    //RETAIL = 10
    //COMERCIAL = 11
    $retail = Building::where('type','Retail')->get();
    $comer = Building::where('type','Bulk')->get();
    $data = array('retail' => $retail, 'comer' => $comer);

    return $data;
  }
  public function getBuildingsInfo($id)
  {
    $exist = true;
    $properties = BuildingProperty::where('building_properties.id_buildings',$id )->get();
    $contact = BuildingContact::where('building_contacts.id_buildings',$id)->get();

    if (!$properties->first() && !$contact->first())
      $exist = false;

    $data = array('properties' => $properties, 'contact' => $contact, 'exist' => $exist);

    return $data;
  }
  public function getBuildingsList(Request $request)
  {

      $offset = $request['offset'];
      $limit = 20;

      $buildingList = DB::table('buildings')->skip($offset)->take($limit)->get();

      if(!empty($buildingList))
        return json_encode($buildingList);

  }

  //Building Insert's
  public function insertBuildingData(Request $request)
  {
    $input = $request->all();
    $input['img_building'] = '1.png';

    DB::table('buildings')->insert($input);

    return redirect('/buildings');
  }
  //Building Update's
  public function updateBuilding(Request $request)
  {
    $data = $request->all();
    Building::where('id', $request->id)->update($data);
    return $this->buildings($request);
  }



}






