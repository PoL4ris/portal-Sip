<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Html;
use App\Http\Requests;
use App\Models\Building\Building;
use App\Models\Building\ServiceLocation;
use App\Models\Building\BuildingProperty;
use App\Models\BuildingPropertyValue;
use App\Models\Building\BuildingContact;
use App\Models\Building\Neighborhood;
use App\Models\Type;
use App\Models\Address;
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
    $limit = 50;
    $building = $buildingList = '';

    if ($request->id)
      $building = $this->getBuilding($request->id);
    else
      $buildingList = Building::orderBy('id', 'desc')->skip($offset)->take($limit)->get();

    $data = ['building'     => $building ? $building : $this->getBuilding(rand(2,84)),
             'buildingList' => $buildingList ? $buildingList : '',
             'offset'       => $offset,
             'limit'        => $limit
    ];
    return $data;
  }
  public function getBuilding($id){
    $data = Building::with('address', 'neighborhood', 'contacts')->find($id);
    $data->properties = BuildingPropertyValue::join('building_properties', 'building_property_values.id_building_properties', '=', 'building_properties.id')
                      ->where('building_property_values.id_buildings', '=', $id)
                      ->select('*', 'building_property_values.id as idBpv')
                      ->get();

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

      return Building::where('name',        'LIKE', '%'. $txt .'%')
                      ->orWhere('alias',    'LIKE', '%'. $txt .'%')
                      ->orWhere('nickname', 'LIKE', '%'. $txt .'%')
                      ->orWhere('code',     'LIKE', '%'. $txt .'%')
                      ->orWhere('units',    'LIKE', '%'. $txt .'%')
//                      ->limit(10)
                      ->get();
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
    $properties = BuildingProperty::where('id',$id )->get();
    $contact = BuildingContact::where('id',$id)->get();

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
  public function getBuildingProperties(){
    return BuildingProperty::get();
  }
  //Building Insert's
  public function insertBuildingData(Request $request)
  {
    $input = $request->all();
    $input['img_building'] = '1.png';

    DB::table('buildings')->insert($input);

    return redirect('/buildings');
  }
  public function insertBuildingProperties(Request $request){
    $data = $request->all();

    $record = new BuildingPropertyValue();
    $record->id_buildings = $data['id_buildings'];
    $record->id_building_properties = $data['id_building_properties'];
    $record->value = $data['value'];
    $record->save();

    return $this->getBuilding($data['id_buildings']);
  }
  //Building Update's
  public function updateBuilding(Request $request)
  {
    $data = $request->all();
    Building::where('id', $request->id)->update($data);
    return $this->buildings($request);
  }
  public function updateBldPropValTable(Request $request)
  {
    $data = $request->all();

    $record = BuildingPropertyValue::find($data['id']);
    $record->value = $data['value'];
    $record->save();

    return 'OK';
  }

}






