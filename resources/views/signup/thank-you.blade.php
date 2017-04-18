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
        <div id="thankYou">
            <img src="signup-files/img/SilverIP-Logo-165x65.png" class="centerLogoImage"/>
            <center>
                <br/>
                <h1>Thank you!</h1>
                <div class="title"><img src="signup-files/img/ok-48.png" style="width: 24px; height: 24px; vertical-align: middle;"/>&nbsp;&nbsp;
                    Your account has been successfully created</div>
                <br/>
                <div id="messageSticker">
                    <table width="95%">
                        <tr>
                            <td>
                                <h3>To activate your service please press the  <b>"Activate Now"</b>  button below.</h3><br/>

                                After you press the 'Activate Now' button if you have difficulty with service activation please follow the following steps:<br/><br/>

                                1. If you use a router please restart it by unplugging it from the power source and plugging it back in.<br/>
                                2. If you do not use a router or wireless device and are plugged directly in, restart your computer to activate your new service.

                            </td>
                        </tr>
                    </table>
                </div>
                <div class="divRow" id="button-row" style="width: 700px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="activate" value=" Activate Now "></button></div>
            </center>
            <form id="activationForm" method="post" action="">
                <input type="hidden" name="ActivationCode" value=""/>
                <input type="hidden" name="PostType" value="Activation"/>
                <input type="hidden" name="Universal" value="90000"/>
                <input type="hidden" name="returnURL" value="http://cnn.com" />
                <input type="hidden" name="PortID" value=""/>
            </form>
        </div>
    </body>
</html>
