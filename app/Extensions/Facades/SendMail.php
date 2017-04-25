<?php

namespace App\Extensions\Facades;

use Illuminate\Support\Facades\Facade;


class SendMail extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'sendmail';
  }
}
