<?php

namespace App\Http\Controllers\Lib;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class UtilsController extends Controller
{
    //
  function transformName($string){

    $str = explode('-',$string);
    return $str[0];

  }
  function getValidationType($string){
    $str = explode('-',$string);
    return !empty($str[1]) ? ("validation-" . $str[1] ) : "";
  }
  function getServiceType($string){
    $str = explode('-',$string);
    return !empty($str[2]) ? $str[2] : "";
  }
  function tableExist($tabla){

    $query = DB::select(DB::raw("SHOW TABLES LIKE '{$tabla}'"));

    if($query)
      return true;
    else
      return false;

  }
}
