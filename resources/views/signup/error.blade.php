<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>HEADER TEXT</title>

        <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700|Pontano+Sans' rel='stylesheet' type='text/css'/>
        <link rel="stylesheet" type="text/css" href="signup-files/css/quicksand.css" />
        <link rel="stylesheet" type="text/css" href="signup-files/css/sansation.css" />
        <link rel="stylesheet" type="text/css" href="signup-files/css/styles.css" />
        <script type="text/javascript" src="signup-files/js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="signup-files/js/forms.js"></script>
    </head>
    <body>
        <div id="messageContainer">
            <br/>
            <div class="title">
                <img src="signup-files/img/error-48.png" style="width: 24px; height: 24px; vertical-align: middle;"/>&nbsp;&nbsp;An error has occured</div>
            <br/>
            <center>
                <div id="messageSticker">
                    <table width="95%">
                        <tr>
                            <td>
                                <center><h3>We could not locate your network port</h3></center><br/>
                                <br/>
                                Please check your network settings to ensure that you do not have a static IP address set. If the problem persists try resetting your network connection settings to default, then unplug from the jack and plug back in.<br/>
                                <br/>
                                If you need support or believe you are receiving this page in error please contact our 24/7 Hotline: <b>PHONE NUMBER</b><br/>
                                <br/>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br/>
            <br/>
            <br/>
            <br/>
            @if(isset($debugMessage))
            <p>{{ $debugMessage }}</p>
            @endif
        </div>
    </body>
</html>
