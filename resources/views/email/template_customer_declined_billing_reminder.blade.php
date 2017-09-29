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

        .content-mail {
            width: 700px;
            margin: 0 auto;
        }

        .banner {
            background: #ddd;
            text-align: center;
            font-size: 16px;
            padding: 4px 0;
        }

        .table-line {
            margin: 40px 0px 20px 0px;
            width: 100%;
        }

        .line-black {
            border-bottom: 1px solid #ddd;
        }

        .silver-logo {
            float: right;
            padding-right: 25px;

        }

        .pull-left {
            float: left;
        }
    </style>
</head>
<body>


<div class="content-mail">

    <p class='p2'></p>
    <p class='p1'>Dear {{ trim($customer->first_name) }} {{ trim($customer->last_name) }},</p>
    <p class='p2'></p>
    <p class='p1'>
        This is a reminder that you have a past due balance on your SilverIP account.
        {{--<br>--}}
        Our previous attempt to charge your account was unsuccessful.
        {{--<br>--}}
        If you need to update your credit card on file please visit the <a href="https://myaccount.silverip.net">MyAccount</a>
        portal.
    </p>
    <br>
    <p class='p1'>
        <strong>
            Please Note:
            <br>
        </strong>
        If this the first time you are logging in to the MyAccount site your username is the email address you initially
        registered with and your password is the ten digit phone number
        <strong>
            (e.g. 3121112222 without dashes or periods)
        </strong>
        that you initially registered with. We encourage you to change this password when you login for the first time.
    </p>
    <p class='p2'></p>
    <br>
    <p class='p1 banner'>
        <strong>
            Past Due Items
        </strong>
    </p>
    <br>
    <p class='p1 pull-left'>
        {{ trim($customer->first_name) }} {{ trim($customer->last_name) }}<br>
        {{ $address->address }}
        @if($address->unit != '')
            # {{ $address->unit }}<br>
        @else
            <br>
        @endif
        {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
    <div class="silver-logo">
        <img src="http://www.silverip.net/logo.png" alt=" SilverIP Communications"
             width="165" height="37">
    </div>
    </p>
    <br>
    {{--<hr>--}}
    <br>
    <table class="table-line">
        <thead>
        <tr>
            <td width="300px"><p class="p1"><strong>Product</strong></p></td>
            <td width="100px"><p class="p1"><strong>Invoice #</strong></p></td>
            <td width="150px"><p class="p1"><strong>Period</strong></p></td>
            <td width="100px"><p class="p1"><strong>Amount</strong></p></td>
        </tr>
        <tr>
            <td colspan="100%" class="line-black"></td>
        </tr>
        </thead>
        @foreach($charges as $charge)
            <tr>
                <td>
                    <p class="p1">{{ $charge['product_desc'] }}</p>
                </td>
                <td>
                    <p class="p1">{{ $charge['invoice_id'] }}</p>
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
            <td colspan="100%"><br></td>
        </tr>
        <tr>
            <td><p class="p1">Sales Tax</td>
            <td><p class="p1"></td>
            <td><p class="p1"></td>
            <td><p class="p1">$0.00</p></td>
        </tr>
        <tr>
            <td><p class="p1">Other</td>
            <td><p class="p1"></td>
            <td><p class="p1"></td>
            <td><p class="p1">$0.00</p></td>
        </tr>
        <tr>
            <td colspan="100%" height="2px"  bgcolor="#FFFFFF"></td>
        </tr>
        <tr>
            <td colspan="100%" class="line-black"></td>
        </tr>
        <tr>
            <td><p class="p1"><strong>Total Amount Due</strong></p></td>
            <td><p class="p1"></td>
            <td><p class="p1"></td>
            <td><p class="p1"><strong>${{ number_format($total, 2, '.', '') }}</strong></p></td>
        </tr>
    </table>
    <br>
    <hr>
    <p class='p1'>&nbsp;</p>
    <p class='p3'><span class='s1'>If you have any questions or concerns, please contact us at 312-600-3800 or email us at <a href="mailto:help@silverip.com">help@silverip.com<a></a> at your earliest convenience.</span>
    </p>
    <p class='p4'></p>
    <p class='p3'>Sincerely,</p>
    <p class='p2'><br></p>
    <p class='p1'> SilverIP Customer Support<br>help@silverip.com<br>312-600-3800</p>
    {{--<hr>--}}
    <p class='p1'>&nbsp;</p>
</div>
</body>
</html>
