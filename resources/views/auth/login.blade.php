@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} inputthing">
                            {{--<label class="col-md-4 control-label">E-Mail Address</label>--}}

                            <div class="col-md-12">
                                <input type="email" class=" list-group-item" name="email" value="{{ old('email') }}" placeholder="E-Mail Address" style="border-bottom: none;">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} inputthing">
                            {{--<label class="col-md-4 control-label">Password</label>--}}

                            <div class="col-md-12">
                                <input type="password" class=" list-group-item" name="password" placeholder="Password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group inputthing">
                            <div class="">
                                <button type="submit" class="btn btn-primary login-btn">
                                    <i class="fa fa-btn fa-sign-in"></i>Login
                                </button>

                                {{--<a class="btn btn-link" href="{{ url('/password/reset') }}">Forgot Your Password?</a>--}}
                                <br />
                                <br />
                                <p style="text-align: center;color: #5e5e5e;">Google Sign-In</p>

                                @if(isset($params))
                                  <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="login-btn btn btn-lg  btn-block google" type="submit" style="background: #ff4f70;">Google</a>
                                <p style="text-align: center;color: #ff4f70;">{{ $params }}</p>
                              @else
                                  <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="login-btn btn btn-lg  btn-block google" type="submit">Google</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="copy">Silver<i>IP </i>  Communications © 2017</div>
        </div>
    </div>
</div>
@endsection
