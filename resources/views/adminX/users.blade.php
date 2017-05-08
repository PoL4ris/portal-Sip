@extends('main.main')
@section('bodyContent')

  <div class="col-lg-12 display-content-main-box" id="customer-box-b">

    <div id="customer-view-0">

      <div class="color-name-header btn-danger">
        ADMIN USERS
      </div>



      <div class="panel panel-default">
        <div class="table-responsive">
          <form action="{{ url('/userupdate') }}" method="POST" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <table ui-jq="dataTable" class="table table-striped b-t b-b" >
              <thead>
              <tr><th>ID</th><th>Name</th><th>Email/Username</th><th>Password</th><th>Access</th><th>Role</th><th>Created</th><th>Update</th><th>Modify</th></tr>
              </thead>
              <tbody>
              @foreach($users AS $x => $userdata)
                  <tr>
                    <td>{{ $userdata->id }}<input type="text" value="{{ $userdata->id }}" name="id" class="inputnone" ></td>
                    <td>
                      <label class="block-{{ $x }}-label" id="name-{{ $x }}">{{ $userdata->name }}</label>
                      <input type="text" value="{{ $userdata->name }}" name="name" class="block-{{ $x }}-edit inputnone input-size-users" >
                    </td>
                    <td>
                      <label class="block-{{ $x }}-label" id="email-{{ $x }}">{{ $userdata->email }}</label>
                      <input type="text" value="{{ $userdata->email }}" name="email" class="block-{{ $x }}-edit inputnone input-size-users" >
                    </td>
                    <td>
                      <label class="block-{{ $x }}-label" id="password-{{ $x }}">*****</label>
                      <input type="text" value="" name="password" class="block-{{ $x }}-edit inputnone input-size-users" placeholder="Type new password">
                    </td>
                    {{--<td>{{ $userdata->remember_token }}</td>--}}
                    <td>
                      <label class="block-{{ $x }}-label" id="access-{{ $x }}">{{ $userdata->access }}</label>
                      <input type="text" value="{{ $userdata->access }}" name="access" class="block-{{ $x }}-edit inputnone " >
                    </td>
                    <td>
                      <label class="block-{{ $x }}-label" id="role-{{ $x }}">{{ $userdata->role }}</label>
                      <input type="text" value="{{ $userdata->role }}" name="role" class="block-{{ $x }}-edit inputnone " >
                    </td>
                    {{--<td style="text-align: center;"><img src="{!! $userdata->avatar !!}" alt=""></td>--}}
                    <td>{{ $userdata->created_at }}</td>
                    <td>{{ $userdata->updated_at }}</td>
                    <td>
                      <button class="btn m-b-xs w-xs btn-info btn-edit" id="block-{{ $x }}" onclick="return false; ">Edit</button>
                      <button class="btn m-b-xs w-xs btn-success save-btn" id="save-block-{{ $x }}">Save</button>
                    </td>
                  </tr>
              @endforeach
              </tbody>
            </table>
          </form>
        </div>
      </div>




    </div>
  </div>









@stop