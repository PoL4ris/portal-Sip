<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Html;
use App\Http\Requests;
//Models
use App\Models\Building;
use App\Models\ServiceLocation;
use App\Models\BuildingProperty;
use App\Models\BuildingPropertyValue;
use App\Models\BuildingContact;
use App\Models\Neighborhood;
use App\Models\Type;
use App\Models\Address;
use App\Http\Controllers\Lib\FormsController;
use Redirect;

class BuildingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     *
     * @return  s
     */
    public function buildings(Request $request)
    {
        return $this->buildingData($request);
    }

    /**
     * @param Request $request
     * id = id building to find and return.
     * @return Building Requested data
     */
    public function buildingData(Request $request)
    {
        return Building::with('neighborhood', 'contacts', 'properties')->find($request->id ? $request->id : 23);
    }

    /**
     * @param Request $request
     * query = string to look for in where clauses.
     * @return list of buildings.
     */
    public function getFilterBld(Request $request)
    {
        return Building::where('code', 'like', '%' . $request['query'] . '%')
                       ->orWhere('name', 'like', '%' . $request['query'] . '%')
                       ->orderBy('id', 'desc')->get();
    }


    /**
     * @param $id
     * id = building to find.
     * @return building properties
     */
    public function getBuilding($id)
    {
        $data = Building::with('address', 'neighborhood', 'contacts')->find($id);
        $data->properties = BuildingPropertyValue::join('building_properties',
                                                        'building_property_values.id_building_properties',
                                                        '=',
                                                        'building_properties.id')
                                                 ->where('building_property_values.id_buildings', '=', $id)
                                                 ->select('*', 'building_property_values.id as idBpv')
                                                 ->get();
        return $data;

    }

    /**
     * @param Request $request
     * id = property id to find and return
     * @return property data
     */
    public function getBuildingProperty(Request $request)
    {
        return BuildingProperty::find($request->id);
    }

    //Building GET's
    /**
     * @param Request $request
     *
     * @return List of buildings.
     */
    public function getBuildingsList(Request $request)
    {
        if ($request->type) {
            if ($request->type == 1)
                return Building::where('type', 'like', 'commercial')
                               ->orderBy('id', 'desc')
                               ->get();
            else
                return Building::where('type', '!=', 'commercial')
                               ->orderBy('id', 'desc')
                               ->get();
        }
        return Building::orderBy('id', 'desc')->get();
    }

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
        if (empty($txt))
            return;

        return Building::where('name', 'LIKE', '%' . $txt . '%')
            ->orWhere('alias', 'LIKE', '%' . $txt . '%')
            ->orWhere('nickname', 'LIKE', '%' . $txt . '%')
            ->orWhere('code', 'LIKE', '%' . $txt . '%')
            ->orWhere('units', 'LIKE', '%' . $txt . '%')
//                      ->limit(10)
            ->get();
    }

    public function getBuildingsType()
    {
        //RETAIL = 10
        //COMERCIAL = 11
        $retail = Building::where('type', 'Retail')->get();
        $comer = Building::where('type', 'Bulk')->get();
        $data = array('retail' => $retail, 'comer' => $comer);

        return $data;
    }

    public function getBuildingsInfo($id)
    {
        $exist = true;
        $properties = BuildingProperty::where('id', $id)->get();
        $contact = BuildingContact::where('id', $id)->get();

        if (!$properties->first() && !$contact->first())
            $exist = false;

        $data = array('properties' => $properties, 'contact' => $contact, 'exist' => $exist);

        return $data;
    }

    public function getBuildingProperties()
    {
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

    public function insertBuildingProperties(Request $request)
    {
        $data = $request->all();

        $record = new BuildingPropertyValue;
        $record->id_buildings = $data['id_buildings'];
        $record->id_building_properties = $data['id_building_properties'];
        $record->value = $data['value'];
        $record->save();

        return $this->getBuilding($data['id_buildings']);
    }

    public function insertBuildingContacts(Request $request)
    {
        $data = $request->all();
        BuildingContact::insert($data);

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

    public function updateBldContactTable(Request $request)
    {
        $objeto = [$request->field => $request->value];
        BuildingContact::where('id', $request->id)->update($objeto);
        return 'OK';
    }

}






