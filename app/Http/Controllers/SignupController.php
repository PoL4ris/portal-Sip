<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
//use App\Models\Product;


class SignupController extends Controller
{
  public function __construct(){
    DB::connection()->enableQueryLog();
  }

  public function getSignupProducts(Request $request)
  {

//    return Product::with('getProductsInfo')->get();





    return
      DB::select('SELECT p.id AS ProdID, p.name AS prodname, p.amount, p.frequency,
                         ppv.id_products, ppv.value as speed, ppv.id AS ProdPropValID, ppv.id_product_properties,
                         pp.id AS ProdPropID, pp.description,
                         ppvl.id AS ProdPropValIDLeft, ppvl.value AS slogan, ppvl.id_product_properties AS ProdPropIDLeft
                          FROM  building_products bp
                            INNER JOIN buildings b
                              ON bp.id_buildings = b.id
                            INNER JOIN products p
                              ON bp.id_products = p.id
                            INNER JOIN product_property_values ppv
                              ON ppv.id_products = p.id
                            INNER JOIN product_properties pp
                              ON pp.id = ppv.id_product_properties
                            LEFT JOIN product_property_values ppvl
                              ON ppvl.id_products = p.id
                          WHERE bp.id_buildings = '. $request->id .'
                            AND ppv.id_product_properties = 1 
                            AND ppvl.id_product_properties = 4 
                          ORDER BY speed * 1 asc');
  }
}