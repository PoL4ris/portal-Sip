<!DOCTYPE html>
<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <title></title>
  <meta name='Generator' content='Cocoa HTML Writer'>
  <meta name='CocoaVersion' content='1038.35'>
  <style type='text/css'>
    p.p1 {margin: 0px 0px 0px 0px; font: 12px Avenir; }
    p.p2 {margin: 0px 0px 0px 0px; font: 12px Avenir; min-height: 14px}
    p.p3 {margin: 0px 0px 0px 0px; font: 12px Avenir; }
    p.p4 {margin: 0px 0px 0px 0px; font: 12px Avenir; min-height: 14px}
    span.s1 {font: 12.0px Avenir}
  </style>
</head>
<body>
<p class='p1'><strong><img src="http://www.silverip.com/silverip-tiny-logo-clear.png" alt=" SilverIP Communications" width="165" height="70"></strong><br>
  <br>
</p>
<p class='p1'>Dear {{ trim($customer->first_name) }} {{ trim($customer->last_name) }},</p>
<p class='p2'></p>
@if($chargeDetails['PaymentType'] == 'Credit Card')
    <p class='p1'>The credit card we have on file
        @if(isset($chargeDetails['PaymentTypeDetails']) && isset($chargeDetails['PaymentTypeDetails']['last four']))
            ending in {{ substr($chargeDetails['PaymentTypeDetails']['last four'], -4) }}
        @endif
            was declined for this month's charge. To update or change your card please login to the <a href="https://myaccount.silverip.net">MyAccount</a> portal.</p>
@elseif($chargeDetails['PaymentType'] == 'Checking Account')
    <p class='p1'>We were unable to deduct your service charge from the checking account ending in {{ substr($chargeDetails['PaymentTypeDetails']['last four'], -4) }}. To update or change your card please login to the <a href="https://myaccount.silverip.net">MyAccount</a> portal.</p>
@else
    <p class='p1'>We were unable to deduct your service charge from the account ending in {{ substr($chargeDetails['PaymentTypeDetails']['last four'], -4) }}. To update or change your card please login to the <a href="https://myaccount.silverip.net">MyAccount</a> portal.</p>
@endif
<p class='p1'></p>
<p class='p1'><strong>Please Note:</strong> If this the first time you are logging in to the MyAccount site your username is the email address you initially registered with and your password is the ten digit phone number <strong>(e.g. 3121112222 without dashes or periods)</strong> that you initially registered with. We encourage you to change this password when you login for the first time.</p>
<p class='p1'></p>
<p class='p1'>If you have any questions or concerns, please contact us at 312-600-3800 or email us at help@silverip.com at your earliest convenience.</p>
<p class='p4'></p>
<p class='p3'>Sincerely,</p>
<p class='p2'></p>
<p class='p1'>SilverIP Customer Support<br>help@silverip.com<br>312-600-3800</p>
<p class='p1'>&nbsp;</p>
</body>
</html>
