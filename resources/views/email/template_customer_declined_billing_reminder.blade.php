<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title></title>
    <meta name='Generator' content='Cocoa HTML Writer'>
    <meta name='CocoaVersion' content='1038.35'>
    <style type='text/css'>
        p.p1 {
            margin: 0px 0px 0px 0px;
            font: 12px Avenir;
        }

        p.p2 {
            margin: 0px 0px 0px 0px;
            font: 12px Avenir;
            min-height: 14px
        }

        p.p3 {
            margin: 0px 0px 0px 0px;
            font: 12px Avenir;
        }

        p.p4 {
            margin: 0px 0px 0px 0px;
            font: 12px Avenir;
            min-height: 14px
        }

        span.s1 {
            font: 12.0px Avenir
        }
    </style>
</head>
<body>
<p class='p1'><strong><img src="http://www.silverip.net/logo.png" alt=" SilverIP Communications"
                           width="165" height="37"></strong><br>
    <br>
</p>
<p class='p1'>Dear {{ trim($customer->first_name) }} {{ trim($customer->last_name) }},</p>
<p class='p2'></p>
<p class='p1'>This is a reminder that you have a past due balance on your SilverIP account. Our previous attempt to charge your account was unsuccessful. If you need to update your credit card on file please visit the <a href="https://myaccount.silverip.net">MyAccount</a> portal.</p>
<p class='p2'></p>
<p class='p1'><strong>Please Note:</strong> If this the first time you are logging in to the MyAccount site your username is the email address you initially registered with and your password is the ten digit phone number <strong>(e.g. 3121112222 without dashes or periods)</strong> that you initially registered with. We encourage you to change this password when you login for the first time.</p>
<p class='p2'></p>
<p class='p1'><strong>Past Due Invoice</strong></p>
<p class='p1'>{{ trim($customer->first_name) }} {{ trim($customer->last_name) }}<br>
    {{ $address->address }}<br>
    {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
</p>
<table width="350" border="0" cellspacing="1" cellpadding="1">
    <tr>
        <td width="184"><p class="p1"><strong>Product</strong></p></td>
        <td width="109"><p class="p1"><strong>Period</strong></p></td>
        <td width="109"><p class="p1"><strong>Amount</strong></p></td>
    </tr>
    @foreach($charges as $charge)
        <tr>
            <td>
                <p class="p1">{{ $charge['product_desc'] }}</p>
            </td>
            <td>
                @if($charge['product_frequency'] == 'monthly')
                    <p class="p1">{{ date('M Y', strtotime($charge['start_date'])) }}</p>
                @elseif($charge['product_frequency'] == 'annual')
                    <p class="p1">{{ date('Y', strtotime($charge['start_date'])).' - '.date('Y', strtotime($charge['end_date'])) }}</p>
                @endif
            </td>
            <td>
                <p class="p1">${{ number_format($charge['product_amount'], 2, '.', '') }}</p>
            </td>
        </tr>
    @endforeach
    <tr>
        <td><p class="p1">Sales Tax</td>
        <td><p class="p1"></td>
        <td><p class="p1">$0.00</p></td>
    </tr>
    <tr>
        <td><p class="p1">Other</td>
        <td><p class="p1"></td>
        <td><p class="p1">$0.00</p></td>
    </tr>
    <tr>
        <td height="2px" colspan="3" bgcolor="#FFFFFF">
            <hr>
        </td>
    </tr>
    <tr>
        <td><p class="p1"><strong>Total Amount Due</strong></p></td>
        <td><p class="p1"></td>
        <td><p class="p1"><strong>${{ number_format($total, 2, '.', '') }}</strong></p></td>
    </tr>
</table>
<p class='p1'>&nbsp;</p>
<p class='p3'><span class='s1'>If you have any questions or concerns, please contact us at 312-600-3800 or email us at help@silverip.com at your earliest convenience.</span></p>
<p class='p4'></p>
<p class='p3'>Sincerely,</p>
<p class='p2'><br></p>
<p class='p1'> SilverIP Customer Support<br>help@silverip.com<br>312-600-3800</p>
<hr>
<p class='p1'>&nbsp;</p>
</body>
</html>
