<?php

namespace App\Http\Controllers\Lib;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\Http\Controllers\Lib\UtilsController;


class FormsController extends Controller
{
  public $util;

  public function __construct()
  {
    $this->util = new UtilsController();
  }

  public function getFormType($type)
  {
    if (empty($type))
      return;

    $data = $this->createGenericForm($type);

    return $data;
  }
  public function createGenericForm($tabla, $id = null, $type = "insert")
  {

    $extra = !empty($id) ? "&id={$id}" : "";

    $form = "";
//    $form = "<form class='validation-form'  action='/insert{$tabla}Data' method='post' enctype='multipart/form-data' >";

    $object = DB::select(DB::raw("SHOW FULL COLUMNS FROM {$tabla}"));
    $query = json_decode(json_encode($object), True);

    $data = $query;
    $date = date("Y-m-d H:i:s");

//    if($type == "update")
//    {
//      $sqlInfo = "SELECT * FROM {$tabla} WHERE id='{$id}'";
//      $infoQuery = $this->db->query($sqlInfo);
//      $info = $infoQuery->mysqli_fetch_array();
//    }

    foreach($data as $row)
    {
      if($row['Field'] != "id")
      {
        $foreign = explode("id_",$row['Field']);
        $img = explode("img_",$row['Field']);
        $ajax = explode("_ajax",$row['Field']);
        $isAjax = "";

        if(count($ajax) > 1)
          $isAjax = 'ajax-search';

        $description = empty($row['Comment']) ? $row['Field'] : $row['Comment'];
        $validation = empty($row['Comment']) ? $row['Field'] : $row['Comment'];
        $serviceType = empty($row['Comment']) ? $row['Field'] : $row['Comment'];

        $description = $this->util->transformName($description);
        $validation = $this->util->getValidationType($validation);
        $serviceType = $this->util->getServiceType($serviceType);

        $flag = true;

        if(count($foreign) > 1)
        {
          if($this->util->tableExist($foreign[1]))
          {
            $queryForeignObject = DB::table($foreign[1])->get();
            $queryForeign = json_decode(json_encode($queryForeignObject), True);

            $combo = "";

            foreach($queryForeign as $rowForeign )
            {
              $selected = "";
//              if($type == "update")
//            {
//              if($rowForeign['id'] == $info[$row['Field']])
//              {
//                $selected = " selected=selected";
//              }
//            }

              $combo.="<option value='{$rowForeign['id']}' {$selected}>{$rowForeign['name']}</option>";
            }


            $form .= "<div class='form-group col-md-3'>
                        <div class='row-abc'>
                          <label class='descripcion'>{$description}</label>
                          <p class='input'>
                            <select name='{$row['Field']}' class='  form-control'>{$combo}</select>
                          </p>
                        </div>
                      </div>";

            $flag = false;
          }
        }
        else if(count($img) > 1)
        {
          $form .= "<div class='form-group col-md-3 form-group-file'>
                      <div class='row-abc'>
                        <p class='descripcion'>{$description}</p>
                        <p class='input'>
                          <input type='file'
                                 name='{$row['Field']}'
                                 class='{$row['Type']}  form-control inp-img-form'
                                 data-classinput='form-control inline v-middle input-s'
                                 data-classbutton='btn btn-default'
                                 data-icon='false'
                                 ui-jq='filestyle'
                                 style='position: absolute; clip: rect(0px, 0px, 0px, 0px);'
                                 tabindex='-1'
                          />
                          <img  src='#' alt='' class='prvw-img-form'/>
                        </p>
                      </div>
                    </div>";

          $flag = false;
        }

        if($flag)
        {
          $val = !empty($info) ? $info[$row['Field']] : "";

          if($row['Type'] == "text")
          {
            $form .= "<div class='form-group col-md-3'>
                        <div class='row-abc'>
                          <p class='descripcion'>{$description}</p>
                          <p class='input'>
                            <textarea name='{$row['Field']}' class='{$row['Type']} $validation  form-control'>{$val}</textarea>
                          </p>
                        </div>
                      </div>";
          }
          else if($row['Type'] == "timestamp")
          {
            $form .= "<div class='form-group col-md-3'>
                        <div class='row-abc'>
                          <label class='descripcion'>{$description}</label>
                          <p class='input {$row['Type']}-ico'>
                            <input type='text' name='{$row['Field']}' class='{$row['Type']} $validation $isAjax form-control'  value='{$date}' readonly />
                          </p>
                        </div>
                      </div>";
          }
        else
          {
            $form .= "<div class='form-group col-md-3'>
                        <div class='row-abc'>
                          <label class='descripcion'>{$description}</label>
                          <p class='input {$row['Type']}-ico'>
                            <input type='text' name='{$row['Field']}' class='{$row['Type']} $validation $isAjax form-control'  value='{$val}' />
                          </p>
                        </div>
                      </div>";
          }
        }
      }
    }

//    if($type == "update")
//      $form .= '<input type="submit" value="Actualizar"/>';
//    else
//      $form .= '<div class=\'form-group col-md-12\'><div class="row-abc"><p class="input"><input type="submit" value="Insertar" class="btn m-b-xs w-xs btn-primary" ng-click="submitForm()" /></p></div></div>';

//    $form .= '</form>';
    $form .= "<input type='hidden' value='$tabla' name='table' readonly />";

  return $form;
  }
}
