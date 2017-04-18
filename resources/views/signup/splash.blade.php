<!DOCTYPE html>
<html lang="en-us" class="no-js">
    <head>
        <meta charset="utf-8">
        <title>:: SilverIP ::</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href="signup-files/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link href="signup-files/css/bootstrap-ui.min.css" rel="stylesheet" crossorigin="anonymous">
        <link href="signup-files/css/splash-style.css" rel="stylesheet" crossorigin="anonymous">
    </head>
    <body>
        <div class="bg-content-white">
            <div class="col-lg-12 loading-container" id="loading-container-a">
                <div id="logo-loading">
                    <img src="signup-files/img/logoLogin.png" alt="" class="sm-img">
                    <!-- <img src="signup-files/img/SilverIP-Logo-165x80.png" alt="" > -->
                </div>
            </div>
            <div class="col-lg-12 loading-container" id="loading-container-b">
                <div id="txt-btn-loading">
                    <div class="w-cl w-htext" id="intro-h4">
                        <span>Welcome to SilverIP</span>
                        <p>To start click Connect</p>
                    </div>
                    <form name="redirect" action="http://silverip-portal-2.net/signup" method="post">
                        <!--<form name="redirect" action="http://localhost/130C/index.php" method="post">-->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="token">
                        <input type="hidden" name="IPAddress" value="10.12.45.200"/>
                        <input type="hidden" name="MacAddress" value="94:10:3E:B9:12:DA"/>
                        <input type="hidden" name="ServiceRouter" value="test-signup-rtr"/>
                        <input type="hidden" name="#LinkOrig" value="http://cnn.com"/>
                        <input type="hidden" name="#LinkLogin" value="http://50.31.16.132/login.html"/>
                        <input type="hidden" name="PostType" value="Router"/>
                        <input type="submit" class="w-cl w-btn" value="Connect">
                    </form>
                    <div class="col-lg-12 righthead">
                        <!-- <div class="container text-center"> -->
                            <i class="icon-earphones-alt"></i>
                            <span>24/7 Support: help@silverip.com</span>
                        <!-- </div> -->
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
