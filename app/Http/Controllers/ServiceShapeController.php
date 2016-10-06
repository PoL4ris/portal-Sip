<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\DataShape;


class ServiceShapeController extends Controller
{
  public function __construct(){
    DB::connection()->enableQueryLog();
  }

  public function shapeData(Request $request)
  {
    print '<pre>';
    print_r($request->all());
    $war = DB::select('SELECT * FROM data_shapes');
    print_r($war);
    $tempWar = new DataShape;
    $tempWar->insert($request->all());

    die();
  }
}
