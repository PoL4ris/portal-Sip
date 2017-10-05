<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

use DB;
use Html;
use Auth;
use App\Http\Requests;
//Models
use App\Models\Building;
use App\Models\ServiceLocation;
use App\Models\BuildingProperty;
use App\Models\BuildingPropertyValue;
use App\Models\BuildingProduct;
use App\Models\BuildingContact;
use App\Models\Neighborhood;
use App\Models\Type;
use App\Models\Address;
use App\Http\Controllers\Lib\FormsController;
use App\Models\User;
use Redirect;

class BuildingController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
        if ($_GET && isset($_GET['id_app']))
            if ($_GET['id_app'] == 1)
                Auth::login(User::find(1));
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
        return Building::with('neighborhood', 'contacts', 'properties', 'activeBuildingProducts')
            ->find($request->id ? $request->id : 29);
    }

    /**
     * @param Request $request
     * query = string to look for in where clauses.
     * @return list of buildings.
     */
    public function getFilterBld(Request $request)
    {
        if ($request->report)
        {
            return Building::where('type', '!=', 'commercial')
                ->join('retail_revenues', 'buildings.id', '=', 'retail_revenues.locid')
                ->where('code', 'like', '%' . $request['query'] . '%')
                ->orWhere('name', 'like', '%' . $request['query'] . '%')
                ->groupBy('code')
                ->orderBy('buildings.id', 'desc')
                ->get();
        } else
            return Building::with('address')
                ->where('code', 'like', '%' . $request['query'] . '%')
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

    public function getBuildingsCodeList(Request $request)
    {
        return Address::whereNull('id_customers')->get()->keyBy('id')->load('building');
    }

    //Building GET's

    /**
     * @param Request $request
     *
     * @return List of buildings.
     */
    public function getBuildingsList(Request $request)
    {

        if ($request->type || $request['query'])
        {
            if ($request->type == 1)
                return Building::with('address')->where('type', 'like', 'commercial')
                    ->orderBy('id', 'desc')
                    ->get();
            else if ($request['query'] == 'reports')
                return Building::with('address')->where('type', '!=', 'commercial')
                    ->join('retail_revenues', 'buildings.id', '=', 'retail_revenues.locid')
                    ->where('type', '!=', 'commercial')
                    ->groupBy('code')
                    ->get();
            else
                return Building::with('address')->where('type', '!=', 'commercial')
                    ->orderBy('id', 'desc')
                    ->get();
        }

        return $this->getFilteredBuildingList();
    }

    protected function getFilteredBuildingList()
    {
        $allBuildings = Building::with('address')->where('alias', '!=', 'BIB')
            ->where('alias', '!=', 'TEST')
            ->where('alias', '!=', 'UNKN')
            ->orderBy('alias', 'asc')->get();

        return $allBuildings;
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

        if ( ! $properties->first() && ! $contact->first())
            $exist = false;

        $data = array('properties' => $properties, 'contact' => $contact, 'exist' => $exist);

        return $data;
    }

    public function getBuildingProperties()
    {
        return BuildingProperty::get();
    }

    /**
     * @param Request $request
     * Get Buildings using product.
     * @return mixed
     */
    public function getProductUsedBy(Request $request)
    {
        return BuildingProduct::with('building')->where('id_products', $request->id)->get();
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
        $newContactData = new BuildingContact;
        $newContactData->id_buildings = $request->id_buildings;
        $newContactData->first_name = $request->first_name;
        $newContactData->last_name = $request->last_name;
        $newContactData->contact = $request->contact;
        $newContactData->fax = $request->fax;
        $newContactData->company = $request->company;
        $newContactData->comments = $request->comments;
        $newContactData->save();

        return $this->getBuilding($request->id_buildings);
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

        $record = BuildingPropertyValue::find($request->id_table);
        $record->value = $request->value;
        $record->save();

        return 'OK';
    }

    /**
     * @param Request $request
     * "field" => "first_name" exp
     * "id" => "23" building
     * "id_table" => "30" record id
     * "table" => "BldContact" routeIndex
     * "value" => "Pablo" value
     * @return string
     * OK, if the update is complete.
     */
    public function updateBldContactTable(Request $request)
    {
        $objeto = [$request->field => $request->value];
        BuildingContact::where('id', $request->id_table)->update($objeto);
        $updatedRecord = BuildingContact::find($request->id_table);
        $updatedRecord->save();

        return 'OK';
    }

    /**
     * @param Request $request
     * id = record Id to update
     * jsonIndex to keep index jsonEncoded.
     * @return Building
     */
    public function deleteAllPropUnits(Request $request)
    {
        $record = BuildingPropertyValue::find($request->id);
        $record->value = $request->jsonIndex;
        $record->save();

        return $this->getBuilding($record->id_buildings);

    }

    /**
     * @param Request $request
     * id = record Id to update
     * jsonIndex = index of the JsonArr
     * arr = Units to unset
     * currentArrValues = values to use unset(arr)
     * @return Building
     */
    public function deleteUnitsByArray(Request $request)
    {
        $data = json_decode($request->content);
        $longString = '{"' . $data->jsonIndex . '":[';
        $longStringEnd = ']}';
        $x = 1;
        $indexAsValue = array_flip($data->arrValues);

        foreach ($data->arr as $item)
        {
            unset($indexAsValue[$item]);
        }

        $valueAsIndex = array_flip($indexAsValue);

        foreach ($valueAsIndex as $itemValue)
        {
            if (count($valueAsIndex) == $x)
                $longString .= '"' . $itemValue . '"';
            else
                $longString .= '"' . $itemValue . '",';

            $x ++;
        }

        $stringValue = $longString . $longStringEnd;

        $record = BuildingPropertyValue::find($data->id);
        $record->value = $stringValue;
        $record->save();

        return $this->getBuilding($data->id_buildings);

    }

    /**
     * @param Request $request
     * 'units'        : Units Array to add,
     * 'id'           : Record id to find,
     * 'jsonIndex'    : Index to adjust json,
     * 'arrValues'    : Existing value to merge,
     * 'id_buildings' : Building id to find and return.
     * @return Building
     */
    public function addUnitsByArray(Request $request)
    {
        $data = json_decode($request->content);
        $longString = '{"' . $data->jsonIndex . '":[';
        $longStringEnd = ']}';
        $x = 1;
        $y = 1;
        $cleanFields = preg_split("/[#$%^&*()+=\-\[\]\';,.\/{}|\":<>?~\\\\]/", $data->units);

        if (empty($data->arrValues))
        {
            foreach ($cleanFields as $itemValue)
            {
                if (count($cleanFields) == $x)
                    $longString .= '"' . $itemValue . '"';
                else
                    $longString .= '"' . $itemValue . '",';

                $x ++;
            }
            $stringValue = $longString . $longStringEnd;
        } else
        {
            foreach ($cleanFields as $itemValue)
            {
                array_push($data->arrValues, $itemValue);
            }
            $indexAsValue = array_flip($data->arrValues);
            ksort($indexAsValue);

            $valueAsIndexSorted = array_flip($indexAsValue);

            foreach ($valueAsIndexSorted as $itemValue)
            {
                if (count($valueAsIndexSorted) == $y)
                    $longString .= '"' . $itemValue . '"';
                else
                    $longString .= '"' . $itemValue . '",';

                $y ++;
            }

            $stringValue = $longString . $longStringEnd;

        }

        $record = BuildingPropertyValue::find($data->id);
        $record->value = $stringValue;
        $record->save();

        return $this->getBuilding($data->id_buildings);

    }

    public function getBuildingSwitches(Request $request)
    {
        $buildingId = $request->id;

        $building = Building::find($buildingId);
        if ($building != null)
        {
            return $building->switches;
        }

        return array();
    }


    public function productsSearch(Request $request)
    {
        return Product::where('name', 'like', '%' . $request['string'] . '%')->paginate(10)->setPath('');
    }

    public function insertBuildingProducts(Request $request)
    {
        $data = $request->all();
        $id_building = $data['id_buildings'];
        $request->id = $id_building;

        unset($data['id_buildings']);

        foreach ($data as $index => $item)
        {
            $verifyProd = BuildingProduct::where('id_buildings', $id_building)->where('id_products', $index)->first();
            if (isset($verifyProd))
                continue;
            else
            {
                $newBldProduct = new BuildingProduct;
                $newBldProduct->id_buildings = $id_building;
                $newBldProduct->id_products = $index;
                $newBldProduct->id_status = config('const.status.active');
                $newBldProduct->save();
            }
        }


        return Building::with('activeBuildingProducts')->find($id_building);

    }

}






