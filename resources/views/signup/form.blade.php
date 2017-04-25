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
        <div id="formContainer">
            <center>
                <table>
                    <tr>
                        <td><img src="signup-files/img/SilverIP-Logo-165x65.png" class="centerLogoImage" style="padding-bottom: 10px;"/> </td>
                    </tr>
                    <tr>
                        <td><h2>{{ $locName }} Account Registration<br/></h2></td>
                    </tr>
                </table>
            </center>
            <div id="backgroundBox">
                <form name="signup" action="" class="formGen" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="token">
                    <input type="hidden" name="PostType" value="SignupForm" />
                    <center>
                        <div class="sectionTitle">
                            <div>
                                Contact Information
                            </div>
                            <div class="titleCaption">
                                Please enter your contact information
                            </div>
                        </div>
                        <div class="contactInfoCol">
                            <table width='90%'>
                                <tr style="vertical-align:top">
                                    <td width='50%'>
                                        <div class="formRow" id="fr_firstname" >
                                            <label for="first_name">First Name:</label>
                                            <input type="text" name="first_name" id="first_name" class="textField required" />
                                        </div>
                                        <div class="formRow" id="fr_lastname" >
                                            <label for="last_name">Last Name:</label>
                                            <input type="text" name="last_name" id="last_name" class="textField required" />
                                        </div>
                                        <div class="formRow" id="fr_email" >
                                            <label for="email">E-mail:</label>
                                            <input type="text" name="email" id="email" class="textField required email" />
                                        </div>
                                        <div class="formRow" id="fr_phonenumber" >
                                            <label for="phone_number">Phone Number:</label>
                                            <input type="text" name="phone_number" id="phone_number" class="textField required phone" />
                                        </div>
                                    </td>
                                    <td width='50%'>
                                        <div class="formRow" id="fr_streetaddress" >
                                            <label for="street_address">Street Address:</label>
                                            @if(count($addressList) > 1)
                                            <select name="street_address" id="street_address" class="select required">
                                                <option value="0">Select your address</option>
                                                @foreach($addressList as $address)
                                                <option  value="{{ $address->address }}">{{ $address->address }}</option>
                                                @endforeach
                                            </select>
                                            @else
                                            <input type="text" name="street_address" id="street_address" class="textField required" value="{{ $addressList->first()->address }}" readonly="readonly"  />
                                            @endif
                                        </div>
                                        <div class="formRow" id="fr_unit" >
                                            <label for="unit">Apartment/Unit:</label>
                                            @if(count($addressList) > 1)
                                            <select name="unit" id="unit" class="select required">
                                                <option  value="">No address selected</option>
                                            </select>
                                            @elseif(count($unitNumbers) > 0)
                                            <select name="unit" id="unit" class="select required">
                                                <option  value="">Please select</option>
                                                <?php $unitCount = 1; ?>
                                                @foreach($unitNumbers as $unit)
                                                <option  value="{{ $unit }}">{{ $unit }}</option>
                                                <?php $unitCount++; ?>
                                                @endforeach
                                            </select>
                                            @else
                                            <input type="text" name="unit" id="unit" class="textField required" value="{{ $addressList->first()->unit }}" />
                                            @endif
                                        </div>
                                        <div class="formRow" id="fr_city" >
                                            <label for="city">City:</label>
                                            <input type="text" name="city" id="city" class="textField required" value="Chicago" readonly="readonly"  />
                                        </div>
                                        <div class="formRow" id="fr_state" >
                                            <label for="state">State:</label>
                                            <input type="text" name="state" id="state" class="textField required state" value="IL" readonly="readonly"  />
                                        </div>
                                        <div class="formRow" id="fr_zip" >
                                            <label for="zip">Zip:</label>
                                            <input type="text" name="zip" id="zip" class="textField required zip" value="{{ $addressList->first()->zip }}" readonly="readonly"  />
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <br/>
                        <div class="sectionTitle" >
                            <table>
                                <tr><td><div>Internet Plan</div></td><td style="padding: 0px 0px 0px 10px; vertical-align: middle;">

                                    @if(count($servicePlanInfo) == 1 && $splashMode == 'bulk')
                                    <div class="formRow" id="fr_serviceplan" >
                                        <input type="hidden" name="service_plan" id="service_plan" class="servicePlan required service_plan" value="{{ key($servicePlanInfo) }}-included" readonly="readonly"  />
                                    </div>
                                    @else
                                    <div class="formRow" id="fr_serviceplan" >
                                        <input type="hidden" name="service_plan" id="service_plan" class="servicePlan required service_plan" readonly="readonly"  />
                                    </div>
                                    @endif
                                    </td></tr>
                            </table>
                            <div class="titleCaption">
                                Select your Internet plan
                            </div>
                        </div>

                        @if(count($servicePlanInfo) === 1 && $splashMode == 'bulk')
                        <div class="servicePlanCol" style="height: 150px;">
                            @else
                            <div class="servicePlanCol" style="height: {{ (ceil(count($servicePlanInfo) / 4) * 213) +  145 }}px;">
                                @endif
                                <table width='90%'>
                                    <!-- Show a single selected plan if the building is bulk with a single plan -->
                                    @if(count($servicePlanInfo) === 1 && $splashMode == 'bulk')
                                    <tr>
                                        <td><center>
                                            <div class="defaultPlanContainer" id="{{ key($servicePlanInfo) }}">
                                                <table width="100%" >
                                                    <tr>
                                                        <td>
                                                            <center>
                                                                <div class="defaultPlanName">
                                                                    {{ key($servicePlanInfo) }} Mbps
                                                                </div>
                                                            </center>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <center>
                                                                <div class="defaultPlanFeatures">
                                                                    <?php $i = 0; ?>
                                                                    @foreach($servicePlanInfo[key($servicePlanInfo)]['info'] as $packageInfoLine)
                                                                    @if($i == 0)
                                                                    {{ $packageInfoLine }}
                                                                    <?php $i++; ?>
                                                                    @else
                                                                    {{ '&nbsp;|&nbsp;' . $packageInfoLine }}
                                                                    @endif
                                                                    @endforeach
                                                                </div>
                                                            </center>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><center><div class="defaultPlanHorLine"></div></center></td>
                                                    </tr>
                                                </table>
                                                <center>
                                                    <table>
                                                        <tr>
                                                            <td><div class="defaultPlanPrice">Included</div></td>
                                                            <td><div class="defaultPlanPriceInfo">(included in your monthly assessment)</div></td>
                                                        </tr>
                                                    </table>
                                                </center>
                                            </div>
                                            </center></td>
                                    </tr>
                                    @else
                                    <!-- Otherwise print the prepay slogan then show the included plan (if any) and upgrades -->
                                    <tr>
                                        <td colspan="4">
                                            <center>
                                                <div class="redAlert"><img src="signup-files/img/red-sun.png" style="width: 30px; height: 30px; vertical-align: middle;"/> Prepay annually and get one month free. No bills. Set it and forget it!</div>
                                            </center>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php $planCount = 0; ?>
                                        @foreach($servicePlanInfo as $packageId => $packageProps)

                                        @if($planCount % 4 == 0)
                                    </tr><tr>
                                    @endif
                                    <?php  $packagaeIdClean = preg_replace("/[^0-9]/", "", $packageId); ?>
                                    <td><center>
                                        @if($servicePlanInfo[$packageId]['type'] == 'included')
                                        <div class="includedPlanContainer" id="{{ $packageId }}">
                                            <div class="planSpeed">{{ $packageId }} Mbps</div>
                                            <div class="planFeatures">
                                                <ul>
                                                    @foreach ($servicePlanInfo[$packageId]['info'] as $packageInfoLine)
                                                    <li>{{ $packageInfoLine }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <table width="110px">
                                                <tr>
                                                    <td><center><div class="planIncluded">INCLUDED</div></center></td>
                                                </tr>
                                                <tr><td colspan="2">
                                                    <center><div class="selectPlanButton">Select Plan</div></center>
                                                    </td></tr>
                                            </table>
                                        </div>
                                        @else
                                        <?php
$monthlyAmount = $servicePlanInfo[$packageId]['monthly'];
$monthlyAmount = (strpos($monthlyAmount, '.') === true) ? $monthlyAmount : $monthlyAmount.'.00';
$perMonthPrice = explode('.', $monthlyAmount);
$perMonthPrice = $perMonthPrice[0];
$annualAmount = $servicePlanInfo[$packageId]['annual'];
$annualAmount = (strpos($annualAmount, '.') === true) ? $annualAmount : $annualAmount.'.00';
?>

                                        <input type="hidden" name="{{ $packageId }}-monthly" id="{{ $packagaeIdClean }}-monthly" value="{{ $monthlyAmount }}" readonly="readonly" />
                                        <input type="hidden" name="{{ $packageId }}-annual" id="{{ $packagaeIdClean }}-annual" value="{{ $annualAmount }}" readonly="readonly" />
                                        <div class="planContainer" id="{{ $packagaeIdClean }}">
                                            <div class="planSpeed">{{ $packageId }} Mbps</div>
                                            <div class="planFeatures">
                                                <ul>
                                                    @foreach ($servicePlanInfo[$packageId]['info'] as $packageInfoLine)
                                                    <li>{{ $packageInfoLine }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <table width="110px">
                                                <tr>
                                                    <td><div class="planPrice">
                                                        {{ $perMonthPrice }}
                                                        </div></td>
                                                    <td><div class="planCycle">per month</div></td>
                                                </tr>
                                                <tr><td colspan="2">
                                                    <center><div class="selectPlanButton">Select Plan</div></center>
                                                    </td></tr>
                                            </table>
                                        </div>
                                        @endif
                                        </center></td>
                                    <?php $planCount++; ?>
                                    @endforeach
                                    </tr>
                                    @endif
                                </table>
                                <div class="paymentOptionBox">
                                    <table width='100%'>
                                        <tr>
                                            <td colspan="2">
                                                <div id="paymentOptionMessage">Please select to pay monthly or prepay annually</div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table width='80%'>
                                        <tr>
                                            <td>
                                                <center><div class="payMonthlyButton" id="monthly-pay">Pay Monthly</div></center>
                                            </td>
                                            <td>
                                                <center><div class="payAnnuallyButton" id="annual-pay">Prepay Annually</div></center>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <br/>
                            <div class="sectionTitle">
                                <table>
                                    <tr><td><div>Select a Wireless Router (optional)</div></td><td style="padding: 0px 0px 0px 10px; vertical-align: middle;">
                                        <div class="formRow" id="fr_wirelessrouter" >
                                            <input type="hidden" name="wireless_router" id="wireless_router" class="wirelessRouter wireless_router" readonly="readonly"  />
                                        </div>
                                        </td>
                                    </tr>
                                </table>
                                <div class="titleCaption">
                                    Use your existing wireless router or buy a new one. This is a products we love, since it outperforms other brands consistently. We buy in bulk and pass the discount to you, our valued customers.
                                </div>
                            </div>
                            <div class="wirelessRouterCol">
                                <table width='700px'>
                                    <tr>
                                        <td>
                                            <div class="noRouterContainer" id="NoRouter">
                                                <table width="100%">
                                                    <tr>
                                                        <!--<td colspan="2">-->
                                                        <td>
                                                            <center>
                                                                <div class="noRouter">I Do Not Need <br/>a Router</div>
                                                            </center>
                                                            <!--<div>I Do Not Need <br/>a Router</div>-->
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                        <td style="padding-left: 12px;">
                                            <div class="routerContainer" id="FastWiFi">
                                                <center>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <img src="signup-files/img/EA6300_Top.jpg" width="180" height="101"><br/>
                                                            </td>
                                                            <td rowspan="2">
                                                                <center><img src="signup-files/img/cisco-logo.jpg" width="50" height="50"><br/>
                                                                    EA6300</center><br/>
                                                                <div class="routerMSRP">$139</div>
                                                                <div class="routerPrice">$99</div>
                                                                <div class="routerPriceInfo">total</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <img src="signup-files/img/EA6300_Back.jpg" width="180" height="43"><br/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <div class="routerFeatureBox">
                                                                    <div class="routerFeatureKey">Dual Band</div>
                                                                    <div class="routerFeatureVal">802.11AC</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="routerFeatureBox">
                                                                    <div class="routerFeatureKey">USB</div>
                                                                    <div class="routerFeatureVal">3.0</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="routerFeatureBox" style="display: table-cell; background-color: #77BC00; vertical-align: middle; font-size: 17px;">
                                                                    <div>Recertified</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="routerFeatureBox">
                                                                    <div class="routerFeatureKey">WiFi</div>
                                                                    <div class="routerFeatureVal">Smart Apps</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2"><center><div class="routerLearnMore"><a href="http://www.linksys.com/en-eu/products/routers/ea6300"  target="_blank">Learn More</a></div></center></td>
                                                        </tr>
                                                    </table></center>
                                                <br/>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br/>

                            <div class="sectionTitle" id="orderDetailsTitle">
                                <div>Checkout Summary
                                    <div class="formRow" id="fr_totalcharges" style="display: none">
                                        <input type="hidden" name="total_charges" id="total_charges" class="totalCharges required" value="0" readonly="readonly"  />
                                    </div>
                                </div>
                                <div class="titleCaption" id="orderDetailsCaption">
                                    Your digital phone service will be charged after we install the converter box.
                                </div>
                            </div>
                            <div class="orderDetailsCol">
                                <table width='85%' border="0">
                                    <tr style="vertical-align:top">
                                        <td width='75%'>
                                            <table width='100%'>
                                                <tr rowspan="5">
                                                    <td colspan="2">
                                                        <div class="totalCharges">
                                                            <table width='100%'>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="serviceChargeUpgradeIH">Internet Service:</div></td>
                                                                    <td><div class="lineItemAmount" id="serviceChargeUpgradeIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="WirelessRouterIH">Wireless Router:</div></td>
                                                                    <td><div class="lineItemAmount" id="WirelessRouterIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="VoipPlanIH">Digital Phone Service:</div></td>
                                                                    <td><div class="lineItemAmount" id="VoipPlanIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="VoipFeaturesIH">Digital Phone Features:</div></td>
                                                                    <td><div class="lineItemAmount" id="VoipFeaturesIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="VoipConverterBoxIH">VoIP Converter Box (one-time purchase):</div></td>
                                                                    <td><div class="lineItemAmount" id="VoipConverterBoxIA">$49.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemPromoHeader" id="VoipSubsidyIH">Instant Rebate on Converter Box:</div></td>
                                                                    <td><div class="lineItemPromoAmount" id="VoipSubsidyIA">- $20.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="TaxIH">Taxes:</div></td>
                                                                    <td><div class="lineItemAmount" id="TaxIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="SurchargeIH">Surcharge:</div></td>
                                                                    <td><div class="lineItemAmount" id="SurchargeIA">$0.00</div></td>
                                                                </tr>

                                                                @foreach ($activationFees as $fee)
                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="InstallationIH">{{ $fee->name }}:</div></td>
                                                                    <td><div class="lineItemAmount feeAmount" id="InstallationIA" value="{{ $fee->amount }}">${{ number_format($fee->amount,2) }}</div>
                                                                        </td>
                                                                </tr>
                                                                @endforeach

                                                                <tr>
                                                                    <td><div class="lineItemHeader" id="InstallationIH">Installation Fee:</div></td>
                                                                    <td><div class="lineItemAmount feeAmount" id="InstallationIA" value="0.00">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><hr></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemTotalHeader" id="TotChargesIH">Amount Due Now:</div></td>
                                                                    <td><div class="lineItemTotalAmount" id="TotChargesIA">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemTotalHeader" id="TotVoipChargesIH" style="font-weight: normal; display: none;">1st Month's Charge (Phone service &amp; adapter):</div></td>
                                                                    <td><div class="lineItemTotalAmount" id="TotVoipChargesIA" style="font-weight: normal; display: none;">$0.00</div></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><div class="lineItemTotalHeader" id="MonthlyChargesIH" style="font-weight: normal;">Ongoing Monthly Charge:</div></td>
                                                                    <td><div class="lineItemTotalAmount" id="MonthlyChargesIA" style="font-weight: normal;">$0.00</div></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br/>
                            <table width ="750px">
                                <tr>
                                    <td>
                                        <div class="formRow" id="fr_recurringcharges" style="display: none">
                                            <input type="hidden" name="recurring_charges" id="recurring_charges" class="recurringCharges required" value="0" readonly="readonly"  />
                                            <input type="hidden" name="total_recurring_charge_box" id="total_recurring_charge_box" value="" readonly="readonly" />
                                        </div>
                                        <div class="formRow" id="fr_delayedcharges" style="display: none">
                                            <input type="hidden" name="delayed_charges" id="delayed_charges" class="delayedCharges required" value="0" readonly="readonly"/>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="sectionTitle" id="paymentInfoTitle">
                                <div>
                                    Payment Information
                                </div>
                                <div class="titleCaption" id="paymentInfoCaption">
                                    Please enter your credit card information
                                </div>
                            </div>
                            <div class="paymentInfoCol">
                                <table width='85%'>
                                    <tr style="vertical-align:top">
                                        <td width='75%'>
                                            <div class="formRow" id="fr_cctype" >
                                                <label for="cc_type">Credit Card Type:</label>
                                                <select name="cc_type" id="cc_type" class="select required">
                                                    <option value="">Select a card type</option>
                                                    <option value="VS">Visa</option>
                                                    <option value="MC">MasterCard</option>
                                                    <option value="AX">American Express</option>
                                                    <option value="DS">Discover</option>
                                                </select>
                                            </div>
                                            <div class="formRow" id="fr_ccnumber" >
                                                <label for="ccnumber">Credit Card Number:</label>
                                                <input type="text" name="cc_number" id="cc_number" class="textField required" />
                                            </div>
                                            <div class="formRow" id="fr_ccexpmonth" >
                                                <label for="cc_exp_month">Expiration Month:</label>
                                                <select name="cc_exp_month" id="cc_exp_month" class="select required">
                                                    <option  value="">Select the expiration month</option>
                                                    <option  value="01">Jan</option>
                                                    <option  value="02">Feb</option>
                                                    <option  value="03">Mar</option>
                                                    <option  value="04">Apr</option>
                                                    <option  value="05">May</option>
                                                    <option  value="06">Jun</option>
                                                    <option  value="07">Jul</option>
                                                    <option  value="08">Aug</option>
                                                    <option  value="09">Sep</option>
                                                    <option  value="10">Oct</option>
                                                    <option  value="11">Nov</option>
                                                    <option  value="12">Dec</option>
                                                </select>
                                            </div>
                                            <div class="formRow" id="fr_ccexpyear" >
                                                <label for="cc_exp_year">Expiration Year:</label>
                                                <select name="cc_exp_year" id="cc_exp_year" class="select required">
                                                    <option  value="">Select the expiration year</option>
                                                    <option  value="2012">2012</option>
                                                    <option  value="2013">2013</option>
                                                    <option  value="2014">2014</option>
                                                    <option  value="2015">2015</option>
                                                    <option  value="2016">2016</option>
                                                    <option  value="2017">2017</option>
                                                    <option  value="2018">2018</option>
                                                    <option  value="2019">2019</option>
                                                    <option  value="2020">2020</option>
                                                    <option  value="2021">2021</option>
                                                    <option  value="2022">2022</option>
                                                    <option  value="2023">2023</option>
                                                    <option  value="2024">2024</option>
                                                    <option  value="2025">2025</option>
                                                </select>
                                            </div>
                                            <div class="formRow" id="fr_ccseccode" >
                                                <label for="ccseccode">Security Code:</label>
                                                <input type="text" name="cc_sec_code" id="cc_sec_code" class="textField required ^[0-9]{3,4}" />
                                            </div>
                                        </td>
                                        <td>
                                            <img src="signup-files/img/payment_methods.jpg" width="188" height="38"><br/>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <table>
                                <tr>
                                    <td>
                                        <center>
                                            <div class="formRow" id="fr_tandcCheckBox" >
                                                <input type="checkbox" name="t_and_c_check_box" id="t_and_c_check_box" class="checkBox required" value="1"/>
                                                I agree with the <a href="https://secure.silverip.net/tandc.html" target="_blank">Terms and Conditions</a>.
                                            </div>
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%">
                                        <center>
                                            <div class="formRow" style="padding: 0px 0px 5px 0px">
                                                <input type="submit" value=" Register " id="submit" />
                                            </div>
                                        </center>
                                    </td>
                                </tr>
                            </table>
                            </center>
                        </form>
                    </div>
            </div>
            </body>
        </html>
