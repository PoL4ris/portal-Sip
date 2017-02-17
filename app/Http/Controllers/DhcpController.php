<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\DhcpHandler;

class DhcpController extends Controller
{
  public function __construct(){
  }

  public function processLease(Request $request)
  {
    $input = $request->all();
    $dhcpHandler = new DhcpHandler();
    $response = $dhcpHandler->processLeaseRequest($input);
    
    dd($response);
  }

}
