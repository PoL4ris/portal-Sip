<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SilverIP Unified Portal</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>


      body {
        font-family: "Lato";
        background: #F0F3F4;

      }
      .fa-btn {
        margin-right: 6px;
      }
      .navbar.navbar-default {
        border: medium none;
        border-radius: 5px;
        margin: 3% auto;
        overflow: hidden;
        width: 75%;
        background: none;
      }
      .navbar.navbar-default .container {
        width: 100%;
      }
      .navbar.navbar-default .containers {
        float: left;
        width: 100%;
      }
      .navbar.navbar-default .navbar-header {
        display: block;
        margin: 0 25%;
        overflow: hidden;
        padding: 0;
        width: 50%;
      }
      .navbar.navbar-default .navbar-brand {
        float: left;
        height: auto;
        margin: 0;
        padding: 0;
        text-align: center;
        width: 100%;
      }
      .navbar.navbar-default .navbar-brand img {
        margin: 10px auto;
        width: 15%;
      }
      .navbar.navbar-default .navbar-brand label {
        font-size: 16px;
        font-weight: normal;
        cursor: pointer;
      }
      .navbar.navbar-default .navbar-brand label label {
        color: #77bee4;
        font-size: 18px;
        margin-left: 5px;
        cursor: pointer;
      }
      .btn-primary{
        background: #38AAE2;
        border:none;
      }
      .btn-primary:hover{
        background: #61B6DD;
      }
      .inputthing{
        margin: 0 20% !important;
        width: 60%;
      }
      .inputthing input{
        border-radius: 2px !important;
        height: 50px;
        width: 100%;
      }
      .login-btn{
        border-radius: 2px;
        height: 45px;
        margin: 20px 5% 0;
        width: 90%;
      }
      .google{
        background: #4285F4;
        color: white;
      }
      .google:hover{
        background: #3867CA;
        color: white;
      }
      .help-block{text-align: center;}



    </style>
</head>
<body id="app-layout">

    <nav class="navbar navbar-default">
        <div class="containers">
            <div class="navbar-header">
                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                  <img src="/img/logoLogin.png" alt="SILVERIP" title="SILVERIP" >
                  <label>Silver<i>IP</i></label>
                </a>
            </div>
        </div>
    @yield('content')
    </nav>


    <!-- JavaScripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
