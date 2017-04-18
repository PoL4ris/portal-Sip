<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ $locName }} Account Registration</title>

        <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700|Pontano+Sans' rel='stylesheet' type='text/css'/>
        <link rel="stylesheet" type="text/css" href="signup-files/css/quicksand.css" />
        <link rel="stylesheet" type="text/css" href="signup-files/css/sansation.css" />
        <link rel="stylesheet" type="text/css" href="signup-files/css/styles.css" />
        <script type="text/javascript" src="signup-files/js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="signup-files/js/forms.js"></script>
    </head>
    <body>
        <div id="messageContainer">
            <img src="signup-files/img/SilverIP-Logo-165x65.png" class="centerLogoImage"/><br/>
            <h2>Internet Service Signup</h2>
            <br/>
            <div id="buildingSticker">
                <center>
                    <table width='95%'>
                        <tr>
                            <td width='30%'>
                                <img src='{{ $buildingLogo }}' class="logoImage" alt="" height="200px" style="padding:5px;"/>
                            </td><td>
                            <h1>{{ $locName }}</h1>
                            <h2>{{ $locSubtitle }}</h2>
                            <br/>
                            <br/>
                            <br/>
                            <h3>24/7 Hotline: &nbsp;&nbsp;&nbsp;&nbsp;{{ $phoneNumber }}</h3>
                            <h3>24/7 Support: &nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:help@silverip.com">help@silverip.com</a></h3>
                            </td></tr></table>
                </center>
            </div>
            <br/>
            <div class="welcomeBody">
                Welcome to the SilverIP signup process.<br/>
                <br/>
                To get started, click on the "Proceed" button below. You will be given the option to select from one of our offered plans and setup your account.
                <br/>
            </div>
            <center><div class="divRow">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/form"><button id="proceed" value=" Proceed"></button></a></div></center>
            <br/>
        </div>
    </body>
</html>
