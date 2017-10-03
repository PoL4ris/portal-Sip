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
{{--<p class='p1'><strong><img src="http://www.silverip.com/silverip-tiny-logo-clear.png" alt=" SilverIP Communications"--}}
                           {{--width="165" height="70"></strong><br>--}}
    {{--<br>--}}
{{--</p>--}}
<p class='p1'>Dear {{ trim($customer->first_name) }} {{ trim($customer->last_name) }},</p>
<p class='p2'></p>
<p class='p1'>We hope you are enjoying your SilverIP service! This email is your receipt for the charges
    @if($chargeDetails['PaymentType'] == 'Credit Card')
        that we made on your credit card
    @elseif($chargeDetails['PaymentType'] == 'Checking Account')
        that we deducted from your checking account
    @else
        that we made on your account
    @endif
    ending in {{ substr($chargeDetails['PaymentTypeDetails']['last four'], -4) }}.</p>
<p class='p3'><span class='s1'>If you have any questions or concerns regarding this receipt please contact  SilverIP Customer Support.<span
                class='Apple-converted-space'></span></p>
<p class='p4'></p>
<p class='p3'>Sincerely,</p>
<p class='p2'><br></p>
<p class='p1'> SilverIP Customer Support<br>help@silverip.com<br>312-242-3794</p>
<hr>
<p class='p1'>&nbsp;</p>
<p class='p1'><strong>Monthly Invoice</strong></p>
<p class='p1'>{{ trim($customer->first_name) }} {{ trim($customer->last_name) }}<br>
    {{ $address->address }}<br>
    {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
</p>
<table width="350" border="0" cellspacing="1" cellpadding="1">
    <tr>
        <td width="184"><p class="p1"><strong>Summary of Charges</strong></p></td>
        <td width="109">&nbsp;</td>
    </tr>
    @foreach($lineItems as $lineItem)
        <tr>
            <td>
                <p class="p1">{{ ucfirst($lineItem['product_name']) }}</p>
            </td>
            <td>
                <p class="p1">${{ number_format($lineItem['product_amount'], 2, '.', '') }}</p>
            </td>
        </tr>
    @endforeach
    <tr>
        <td><p class="p1">Sales Tax</td>
        <td><p class="p1">$0.00</p></td>
    </tr>
    <tr>
        <td><p class="p1">Other</td>
        <td><p class="p1">$0.00</p></td>
    </tr>
    <tr>
        <td height="2px" colspan="2" bgcolor="#FFFFFF">
            <hr>
        </td>
    </tr>
    <tr>
        <td><p class="p1"><strong>Total Amount Charged</strong></p></td>
        <td><p class="p1"><strong>${{ number_format($invoice->amount, 2, '.', '') }}</strong></p></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><p class="p1">Paid {{ date('F j, Y') }}:</p></td>
        <td><p class="p1">{{ substr($chargeDetails['PaymentTypeDetails']['last four'], -4) }}</p></td>
    </tr>
</table>
<p class='p1'>&nbsp;</p>
<hr>
<p class='p1'>&nbsp;</p>
</body>
</html>
