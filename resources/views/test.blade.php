<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
  <div class="col-lg-12 display-content-main-box" id="customer-box-b">

    <div id="customer-view-0">

      <div class="color-name-header btn-danger">
        ADMIN USERS
      </div>



      <div class="panel panel-default">
        <div class="table-responsive">
          <form action=""></form>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="token">
            <input type="text" placeholder="pablo" name="pablo" id="pabloID">
            <input type="submit" onclick="pablo();">

        </div>
      </div>




    </div>
  </div>


  <script>

function  pablo (){

var valor = $('#pabloID').val();
var token = $('#token').val();


    $.ajax(
      {type:"POST",
        url:"/getAdminUsers",
        data:{'pablo': valor, '_token' :token},
        success: function(data) {

          console.log('retsurn');
        }
      }
    );


}

  </script>