<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Automatic Activation</title>

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
            <center>
                <br/>
                <div class="title"><img src="signup-files/img/ok-48.png" style="width: 24px; height: 24px; vertical-align: middle;"/>&nbsp;&nbsp;
                    Please wait ...</div>
                <br/>
                <div id="messageSticker">
                    <table width="95%">
                        <tr>
                            <td>
                                <h3>Your port was reset. Please wait while we activate it again.</h3><br/>
                            </td>
                        </tr>
                    </table>
                </div>
{{--                <div class="divRow" id="button-row" style="width: 700px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="activate" value=" Activate Now "></button></div>--}}
            </center>
            <br/>
            <br/>
            <br/>
            <br/>
            @if(isset($debugMessage))
            <p>{{ $debugMessage }}</p>
            @endif
        </div>
        <form id="activationForm" method="post" action="/activate">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="token">
            <input type="hidden" name="ActivationCode" value="{{ $activationCode }}"/>
            <input type="hidden" name="PostType" value="Activation"/>
            <input type="hidden" name="Universal" value="{{ $timer }}"/>
            <input type="hidden" name="returnURL" value="{{ $returnURL }}" />
            <input type="hidden" name="PortID" value="{{ $portId }}"/>
            <input type="hidden" name="PortID" value="{{ $portId }}"/>
        </form>

        <script type="text/javascript">
            jQuery(function ($) {

                var act_overlay = {
                    show: function (cdown, targetURL) {

                        // For activation:
                        $('body').append('<div id="overlay"></div><div id="preloader">Activating ...</div>');
                        var count = cdown;
                        countdown = setInterval(function () {
                            if (count == 0) {
                                $('#preloader').html('Still activating ... ');
                                window.location.href = targetURL;
                                return;
                            }
                            $('#preloader').html('Activating ... ' + count);

                            count--;
                        }, 1000);
                    },
                    hide: function () {
                        $('#overlay,#preloader').remove();
                    }
                }

                var actForm = $('#activationForm');
                var pageRedirTimer = actForm.find('input[name=Universal]').val();
                var returnURL = actForm.find('input[name=returnURL]').val();
                                act_overlay.show(pageRedirTimer / 1000, returnURL);
                $('#activationForm').submit();
            });
        </script>
    </body>
</html>
