jQuery.noConflict();

jQuery(function ($) {

    var overlay = {
        show: function () {
            $('body').append('<div id="overlay"></div><div id="preloader">Registering ...</div>');
        },
        hide: function () {
            $('#overlay,#preloader').remove();
        }
    };

    var act_overlay = {
        show: function (cdown, targetURL) {

            // CONFIG-OPTION: To switch between activation and redirection use one of the following options

            // For activation:
            $('body').append('<div id="overlay"></div><div id="preloader">Activating ...</div>');

            // For redirection:
            // $('body').append('<div id="overlay"></div><div id="preloader">Redirecting ...</div>');
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

    var proceedButton = $('#messageContainer .divRow').find('button');
    $('#messageContainer .divRow').zinePretifyForm();
    // $('#messageContainer').zinePretifyForm();

    // ## CONFIG-OPTION: To redirect to google ...

    // proceedButton.click(function(){
    // var pageRedirTimer = 1000;
    // // // alert(pageRedirTimer);
    // $('#thankYou form').submit();
    // act_overlay.show(pageRedirTimer/1000);
    // setTimeout(function() {
    // window.location.href = "http://www.google.com";
    // }, pageRedirTimer);
    // // }, 5000);
    // });

    // ## NOTE: Normal Proceed button
//    proceedButton.click(function () {
//        $('#messageContainer').fadeOut(function () {
//            $('#formContainer').fadeIn();
//        });
//    });

    $.ajaxSetup({
        cache: false
    });

    var activateButton = $('#thankYou').find('button');
    $('#thankYou').zinePretifyForm();
    activateButton.click(function () {

        var actForm = $('#thankYou form');
        var activationCode = actForm.find('input[name=ActivationCode]').val();
        var pageRedirTimer = actForm.find('input[name=Universal]').val();
        var returnURL = actForm.find('input[name=returnURL]').val();
        var portID = actForm.find('input[name=PortID]').val();
        var token = actForm.find('input[name=_token]').val();

        actForm.find('#button-row').hide();
        act_overlay.show(pageRedirTimer / 1000, returnURL);

        $(this).load('/activate', {
            '_token': token,
            'PostType': 'Activation',
            'ActivationCode': activationCode,
            'Universal': pageRedirTimer,
            'PortID': portID
        });

        //            setTimeout(function() {
        //                    window.location.href = returnURL;
        //            }, pageRedirTimer);

    });



    // var locAddress = $('#formContainer form').find('select[name=label4]');
    // locAddress.change(function () {
    // var locID = locAddress.val();
    // updateUnitList(locID);
    // });

    var registrationForm;

    $(document).ready(function () {
        // For debugging uncomment the ajaxSetup below
        $.ajaxSetup({
            error: function (x, e) {
                if (x.status == 0) {
                    alert('You are offline!!\n Please Check Your Network.');
                } else if (x.status == 404) {
                    alert('Requested URL not found.');
                } else if (x.status == 500) {
                    alert('Internel Server Error.');
                } else if (e == 'parsererror') {
                    alert('Error.\nParsing JSON Request failed:\nstatus: ' + x.status + '\nerror: ' + e + '\nresponseText: ' + x.responseText);
                } else if (e == 'timeout') {
                    alert('Request Time out.');
                } else if (x.status == 422) {

//                    dump(x.responseText);
                    displayValidationErrors(x.responseText)

                } else {
                    displayValidationErrors(x.responseText)
//                    alert('Unknow Error.\n' + x.responseText);
                }
            }
        });


        registrationForm = $('#formContainer .formGen');
        registrationForm.zinePretifyForm();
        registrationForm.submit(handleFormSubmit);
        registrationForm.find('input[type=submit]').click(handleFormSubmit);
        var payInfoTitle = $('#paymentInfoTitle');
        var paymentInfoCaption = $('#paymentInfoCaption');
        var payInfoCol = $('.paymentInfoCol');
        var orderDetailsTitle = $('#orderDetailsTitle');
        var orderDetailsCaption = $('#orderDetailsCaption');
        var orderDetailsCol = $('.orderDetailsCol');
        var VoipPlanIH = $('#VoipPlanIH');
        var VoipPlanIA = $('#VoipPlanIA');
        var VoipFeaturesIH = $('#VoipFeaturesIH');
        var VoipFeaturesIA = $('#VoipFeaturesIA');
        var VoipConverterBoxIH = $('#VoipConverterBoxIH');
        var VoipConverterBoxIA = $('#VoipConverterBoxIA');
        var VoipSubsidyIH = $('#VoipSubsidyIH');
        var VoipSubsidyIA = $('#VoipSubsidyIA');
        var TotVoipChargesIH = $('#TotVoipChargesIH');
        var TotVoipChargesIA = $('#TotVoipChargesIA');
        var TaxIH = $('#TaxIH');
        var TaxIA = $('#TaxIA');
        var SurchargeIH = $('#SurchargeIH');
        var SurchargeIA = $('#SurchargeIA');
        var InstallationIH = $('#InstallationIH');
        var InstallationIA = $('#InstallationIA');
        var TotChargesIH = $('#TotChargesIH');
        var TotChargesIA = $('#TotChargesIA');
        var MonthlyChargesIH = $('#MonthlyChargesIH');
        var MonthlyChargesIA = $('#MonthlyChargesIA');

        updatePaymentView();

        $('.noRouterContainer').click(function () {
            var self = $(this);
            var routerInputBox = $('input#wireless_router');
            var WirelessRouterIH = $('#WirelessRouterIH');
            var WirelessRouterIA = $('#WirelessRouterIA');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if ($('.routerContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() - 20);
                }
                $('.routerContainer').removeClass('active');
                self.addClass('active');
                routerInputBox.val(self.attr('id'));
                WirelessRouterIH.hide();
                WirelessRouterIA.hide();
            }
            updatePaymentView();
        });

        $('.routerContainer').click(function () {
            var self = $(this);
            var routerInputBox = $('input#wireless_router');
            var WirelessRouterIH = $('#WirelessRouterIH');
            var WirelessRouterIA = $('#WirelessRouterIA');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if (!$('.routerContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() + 20);
                }
                $('.routerContainer').removeClass('active');
                $('.noRouterContainer').removeClass('active');
                self.addClass('active');
                routerInputBox.val(self.attr('id'));
                WirelessRouterIH.fadeIn();
                WirelessRouterIA.fadeIn();
            }
            updatePaymentView();
        });



        $('.noVoipContainer').click(function () {
            var self = $(this);

            var voipInputBox = $('input#voip');
            var voipFeatBox = $('input#voipfeatures');
            var voipFeatureBoxes = $('.voipFeatureList');
            var voipFeatureDescBox = $('.voipFeatureDesc');
            var digPhonNumb = $('#fr_voipnumber');
            var voipNumbNotesBox = $('.voipNumberNotes');
            var voipCol = $('.voipCol');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if ($('.voipContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() - 96);
                }
                $('.voipContainer').removeClass('active');
                voipFeatureBoxes.hide();
                voipFeatureDescBox.hide();
                digPhonNumb.hide();
                voipNumbNotesBox.hide();
                voipCol.css({
                    height: "153px"
                });
                self.addClass('active');
                voipInputBox.val(self.attr('id'));
                voipFeatBox.val("");
            }
            hideVoipReceiptLines();
            updatePaymentView();
        });

        $('.voipContainer').click(function () {
            var self = $(this);
            var voipInputBox = $('input#voip');
            var voipFeatureBoxes = $('.voipFeatureList');
            var voipFeatureDescBox = $('.voipFeatureDesc');
            var digPhonNumb = $('#fr_voipnumber');
            var voipNumbNotesBox = $('.voipNumberNotes');
            var voipCol = $('.voipCol');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if (!$('.voipContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() + 96);
                }
                $('.voipContainer').removeClass('active');
                $('.noVoipContainer').removeClass('active');
                self.addClass('active');
                voipFeatureBoxes.fadeIn();
                voipFeatureDescBox.fadeIn();
                digPhonNumb.fadeIn();
                voipNumbNotesBox.fadeIn();
                voipCol.css({
                    height: "510px"
                });
                voipInputBox.val(self.attr('id'));
            }
            showVoipReceiptLines();
            updatePaymentView();
        });


        function hideVoipReceiptLines() {
            VoipPlanIH.hide();
            VoipPlanIA.hide();
            VoipFeaturesIH.hide();
            VoipFeaturesIA.hide();
            VoipConverterBoxIH.hide();
            VoipConverterBoxIA.hide();
            VoipSubsidyIH.hide();
            VoipSubsidyIA.hide();
            TotVoipChargesIH.hide();
            TotVoipChargesIA.hide();
        }

        function showVoipReceiptLines() {
            VoipPlanIH.fadeIn();
            VoipPlanIA.fadeIn();
            VoipFeaturesIH.fadeIn();
            VoipFeaturesIA.fadeIn();
            VoipConverterBoxIH.fadeIn();
            VoipConverterBoxIA.fadeIn();
            VoipSubsidyIH.fadeIn();
            VoipSubsidyIA.fadeIn();
            TotVoipChargesIH.fadeIn();
            TotVoipChargesIA.fadeIn();
        }

        function hideTax() {
            if (TaxIH.is(":visible")) {
                orderDetailsCol.height(orderDetailsCol.height() - 60);
                TaxIH.hide();
                TaxIA.hide();
                SurchargeIH.hide();
                SurchargeIA.hide();
                InstallationIH.hide();
                InstallationIA.hide();
            }
        }


        function showTax() {
            if (!TaxIH.is(":visible")) {
                orderDetailsCol.height(orderDetailsCol.height() + 60);
                TaxIH.fadeIn();
                TaxIA.fadeIn();
                SurchargeIH.fadeIn();
                SurchargeIA.fadeIn();
                InstallationIH.fadeIn();
                InstallationIA.fadeIn();
            }
        }

        $('#2ndLine').hover(function () {
            var voipFeatureDescBox = $('.voipFeatureDesc');
            voipFeatureDescBox.html("Add a second line to your acount");
        },
                function () {
                    var voipFeatureDescBox = $('.voipFeatureDesc');
                    voipFeatureDescBox.html("");
                });

        $('#2ndLine').click(function () {
            var self = $(this);
            var voipFeatureDescBox = $('.voipFeatureDesc');
            if (self.hasClass('active')) {
                self.removeClass('active');
                voipFeatureDescBox.html("");
            } else {
                self.addClass('active');
                voipFeatureDescBox.html("Add a second line to your acount");
            }
            updateVoipFeaturesInputBox();
            updatePaymentView();
        });

        $('#eFax').hover(function () {
            var voipFeatureDescBox = $('.voipFeatureDesc');
            voipFeatureDescBox.html("Add a digital fax line to your acount");
        },
                function () {
                    var voipFeatureDescBox = $('.voipFeatureDesc');
                    voipFeatureDescBox.html("");
                });

        $('#eFax').click(function () {
            var self = $(this);
            var voipFeatureDescBox = $('.voipFeatureDesc');
            if (self.hasClass('active')) {
                self.removeClass('active');
                voipFeatureDescBox.html("");
            } else {
                self.addClass('active');
                voipFeatureDescBox.html("Add a digital fax line to your acount");
            }
            updateVoipFeaturesInputBox();
            updatePaymentView();
        });

        $('#CallFwd').hover(function () {
            var voipFeatureDescBox = $('.voipFeatureDesc');
            voipFeatureDescBox.html("Add call forwarding features to your acount");
        },
                function () {
                    var voipFeatureDescBox = $('.voipFeatureDesc');
                    voipFeatureDescBox.html("");
                });

        $('#CallFwd').click(function () {
            var self = $(this);
            var voipFeatureDescBox = $('.voipFeatureDesc');
            if (self.hasClass('active')) {
                self.removeClass('active');
                voipFeatureDescBox.html("");
            } else {
                self.addClass('active');
                voipFeatureDescBox.html("Add call forwarding features to your acount");
            }
            updateVoipFeaturesInputBox();
            updatePaymentView();
        });

        $('#Voicemail').hover(function () {
            var voipFeatureDescBox = $('.voipFeatureDesc');
            voipFeatureDescBox.html("Add advanced voicemail with email and texting to your account");
        },
                function () {
                    var voipFeatureDescBox = $('.voipFeatureDesc');
                    voipFeatureDescBox.html("");
                });

        $('#Voicemail').click(function () {
            var self = $(this);
            var voipFeatureDescBox = $('.voipFeatureDesc');
            if (self.hasClass('active')) {
                self.removeClass('active');
                voipFeatureDescBox.html("");
            } else {
                self.addClass('active');
                voipFeatureDescBox.html("Add advanced voicemail with email and texting to your account");
            }
            updateVoipFeaturesInputBox();
            updatePaymentView();
        });

        $('#MultiRing').hover(function () {
            var voipFeatureDescBox = $('.voipFeatureDesc');
            voipFeatureDescBox.html("Allow incoming calls to ring on multiple phones");
        },
                function () {
                    var voipFeatureDescBox = $('.voipFeatureDesc');
                    voipFeatureDescBox.html("");
                });

        $('#MultiRing').click(function () {
            var self = $(this);
            var voipFeatureDescBox = $('.voipFeatureDesc');
            if (self.hasClass('active')) {
                self.removeClass('active');
                voipFeatureDescBox.html("");
            } else {
                self.addClass('active');
                voipFeatureDescBox.html("Allow incoming calls to ring on multiple phones");
            }
            updateVoipFeaturesInputBox();
            updatePaymentView();
        });

        function updatePaymentView() {

            var servicePlanInputBox = $('input#service_plan');
            var routerInputBox = $('input#wireless_router');
            var voipInputBox = $('input#voip');
            var voipNumSelectBox = $('select#voipnumber option:selected');
            var voipFeatInputBox = $('input#voipfeatures');
            var totalChargesInputBox = $('input#total_charges');
            var recurringChargeInputBox = $('input#recurring_charges');
            var delayedChargeInputBox = $('input#delayed_charges');
            var inetServiceCharge = 0;
            var annualInetServiceCharge = 0;
            var recurringInetServiceCharge = 0;
            var totCharges = 0;


            $('div.feeAmount').each(function(){
              totCharges += $(this).attr('value') * 100;
//              console.log('Adding fee to total: '+$(this).attr('value'));
            });

            //                    var totInetCharges = 0;
            var recurringVoipCharges = 0;
            var routerCharge = 0;
            var voipCharge = 0;
            var delayedCharges = 0;
            var voipAdapterCharge = 0;
            var voipCredit = 0;
            var voipFeatCharge = 0;
            var totalRecurringCharges = 0;
            var totalRecurringInputBox = $('#total_recurring_charge_box');

            var selectedPlan = '';

            if (servicePlanInputBox.length && servicePlanInputBox.val().length > 0) {
                // Get the selected Internet service
                selectedPlan = servicePlanInputBox.val();
                var selectedPlanAmount = 0;

                if (selectedPlan.search("included") > -1) {
                    selectedPlanAmount = 0;
                } else {
                    selectedPlanAmount = $('#' + selectedPlan).val();
                    //                            alert(selectedPlanAmount);
                    selectedPlanAmount = selectedPlanAmount.replace('.', '');
                }

                if (selectedPlan.search("monthly") > -1) {
                    recurringInetServiceCharge = Number(selectedPlanAmount);
                    inetServiceCharge = recurringInetServiceCharge;
                    $('#MonthlyChargesIH').html('Ongoing Monthly Charge');
                } else if (selectedPlan.search("annual") > -1) {
                    annualInetServiceCharge = Number(selectedPlanAmount);
                    inetServiceCharge = annualInetServiceCharge;
                    recurringInetServiceCharge = 0;
                    //                            $('#MonthlyChargesIH').html('Total Annual Charge');
                } else {
                    inetServiceCharge = 0;
                    recurringInetServiceCharge = 0;
                }
            }

            if (routerInputBox.length && routerInputBox.val().length > 0) {
                // Get the selected router
                if (routerInputBox.val() == "BasicWiFi") {
                    routerCharge = 4900;
                }
                else
                if (routerInputBox.val() == "FastWiFi") {
                    routerCharge = 9900;
                }
                else
                    routerCharge = 0;
            }

            if (voipInputBox.length && voipInputBox.val().length > 0) {
                // Get the selected VOIP plan
                if (voipInputBox.val() == "UnlimitedCalling") {
                    voipCharge = 1900;
                    voipAdapterCharge = 2900;
                    //                        voipCredit = 10;
                    if ($('#2ndLine').hasClass('active')) {
                        voipFeatCharge += 200;
                        //                            voipCredit += 1;
                    }
                    if ($('#eFax').hasClass('active')) {
                        voipFeatCharge += 200;
                        //                            voipCredit += 1;
                    }
                    if ($('#CallFwd').hasClass('active')) {
                        voipFeatCharge += 200;
                        //                            voipCredit += 1;
                    }
                    if ($('#Voicemail').hasClass('active')) {
                        voipFeatCharge += 200;
                        //                            voipCredit += 1;
                    }
                    if ($('#MultiRing').hasClass('active')) {
                        voipFeatCharge += 200;
                        //                            voipCredit += 1;
                    }
                }
                else {
                    voipCharge = 0;
                    voipFeatCharge = 0;
                    voipCredit = 0;
                    voipAdapterCharge = 0;
                }
            }


            //                    totInetCharges = inetServiceCharge + routerCharge;
            totCharges += routerCharge;
            recurringVoipCharges = voipCharge + voipFeatCharge - voipCredit;
            var voipFirstMonth = voipAdapterCharge + voipCharge + voipFeatCharge - voipCredit;
            delayedCharges = voipAdapterCharge + voipCharge + voipFeatCharge - voipCredit + annualInetServiceCharge + recurringInetServiceCharge;
            totalRecurringCharges = recurringInetServiceCharge + recurringVoipCharges;


            $('#serviceChargeUpgradeIA').html('$' + convertToDollarsCents(inetServiceCharge)); //+'.00');
            $('#WirelessRouterIA').html('$' + convertToDollarsCents(routerCharge)); //+'.00');
            $('#VoipPlanIA').html('$' + convertToDollarsCents(voipCharge)); //+'.00');
            $('#VoipFeaturesIA').html('$' + convertToDollarsCents(voipFeatCharge)); //+'.00');

            //alert("inetServiceCharge: "+inetServiceCharge+"\nrouterCharge: "+routerCharge+"\nvoipCharge: "+voipCharge+"\nvoipFeatCharge: "+voipFeatCharge);

            if (voipCharge > 0) {
                $('#VoipConverterBoxIA').html('$49.00');
                $('#VoipSubsidyIA').html('- $20.00');
            } else {
                $('#VoipConverterBoxIA').html('$0.00');
                $('#VoipSubsidyIA').html('$0.00');
            }

            //                    if(voipCredit > 0)
            //                        $('#VoipCreditIA').html('- $'+voipCredit+'.00');
            //                    else
            //                        $('#VoipCreditIA').html('$'+voipCredit+'.00');

            //alert('before: '+totInetCharges);
            //totInetCharges = totInetCharges.toString();
            //                    var cents = totInetCharges.slice(totInetCharges.length-2);
            //                    var dollars = totInetCharges.slice(0, totInetCharges.length - 2);
            //                    totInetCharges = dollars+'.'+cents;
            //alert('after: '+totInetCharges);


            $('#TotChargesIA').html('$' + convertToDollarsCents(totCharges));
            $('#TotVoipChargesIA').html('$' + convertToDollarsCents(voipFirstMonth));//+'.00');
            $('#MonthlyChargesIA').html('$' + convertToDollarsCents(totalRecurringCharges));//+'.00');

            totalChargesInputBox.val(convertToDollarsCents(totCharges));
            //                    recurringChargeInputBox.val(convertToDollarsCents(recurringVoipCharges));
            recurringChargeInputBox.val(convertToDollarsCents(totalRecurringCharges));
            delayedChargeInputBox.val(convertToDollarsCents(delayedCharges));
            totalRecurringInputBox.val(convertToDollarsCents(totalRecurringCharges));
            //                    alert('recurringVoipCharges = '+recurringVoipCharges+' and delayedCharges = '+delayedCharges);

            if (totCharges == 0 && delayedCharges == 0) {
                orderDetailsTitle.hide();
                orderDetailsCaption.hide();
                orderDetailsCol.hide();
                payInfoTitle.hide();
                paymentInfoCaption.hide();
                payInfoCol.hide();
                hideTax();
                TotChargesIH.hide();
                TotChargesIA.hide();
                if (totalRecurringCharges == 0) {
                    MonthlyChargesIH.hide();
                    MonthlyChargesIA.hide();
                }
            } else {
                orderDetailsTitle.fadeIn();
                orderDetailsCaption.fadeIn();
                orderDetailsCol.fadeIn();
                payInfoTitle.fadeIn();
                paymentInfoCaption.fadeIn();
                payInfoCol.fadeIn();
                showTax();
                TotChargesIH.fadeIn();
                TotChargesIA.fadeIn();
                if (totalRecurringCharges > 0) {
                    MonthlyChargesIH.fadeIn();
                    MonthlyChargesIA.fadeIn();
                } else {
                    MonthlyChargesIH.hide();
                    MonthlyChargesIA.hide();
                }
            }



            //                    if(recurringVoipCharges == 0) {
            //                       voipOrderDetailsTitle.hide();
            //                       voipOrderDetailsCol.hide();
            //                    } else {
            //                        voipOrderDetailsTitle.fadeIn();
            //                        voipOrderDetailsCol.fadeIn();
            //                    }

            //                    if(totInetCharges == 0 && recurringVoipCharges == 0) {
            //                        payInfoTitle.hide();
            //                        payInfoCol.hide();
            //                    } else {
            //                        payInfoTitle.fadeIn();
            //                        payInfoCol.fadeIn();
            //                    }

            var debugBox = $('#paymentInfo');
            debugBox.html('<br/><p style="font-weight: bold; text-align: left;">Plans and Payment Info:<br/><br/></p>');
            debugBox.append('Recurring Charges: $' + recurringChargeInputBox.val() + '<br/>');
            debugBox.append('Auth-Only Charges: $' + delayedChargeInputBox.val() + '<br/>');
            debugBox.append('Total Charges: $' + totalChargesInputBox.val() + '<br/>');
            debugBox.append('Internet Service: ' + servicePlanInputBox.val() + '<br/>');
            debugBox.append('Wireless Router: ' + routerInputBox.val() + '<br/>');
            debugBox.append('VOIP: ' + voipInputBox.val() + '<br/>');
            debugBox.append('VOIP Number: ' + voipNumSelectBox.val() + '<br/>');
            debugBox.append('VOIP Features: ' + voipFeatInputBox.val() + '<br/>');
        }

        function convertToDollarsCents(rawAmount) {
            if (rawAmount == 0) {
                return "0.00";
            }
            rawAmount = rawAmount.toString();
            var cents = rawAmount.slice(rawAmount.length - 2);
            var dollars = rawAmount.slice(0, rawAmount.length - 2);
            return dollars + '.' + cents;
        }

        function updateVoipFeaturesInputBox() {
            var voipFeatInputBox = $('input#voipfeatures');
            voipFeatInputBox.val("");
            var voipFeatVal = "";
            $('.voipFeatureBox').each(function () {
                if ($(this).hasClass('active')) {
                    if (voipFeatVal == "") {
                        voipFeatVal = $(this).attr('id');
                    }
                    else {
                        voipFeatVal += ', ' + $(this).attr('id');
                    }
                }
            });
            voipFeatInputBox.val(voipFeatVal);

        }

        $('.includedPlanContainer').click(function () {
            var self = $(this);
            var payMonthlyButton = $('#monthly-pay');
            var payAnnuallyButton = $('#annual-pay');
            var payOptMessage = $('#paymentOptionMessage');
            var servicePlanInputBox = $('input#service_plan');
            //                    var servicePlanCycleInputBox = $('servicePlan-cycle');
            //                    var servicePlanSpeedInputBox = $('servicePlan-speed');
            var inetServiceReceiptIH = $('#serviceChargeUpgradeIH');
            var inetServiceReceiptIA = $('#serviceChargeUpgradeIA');
            var servicePlanCol = $('.servicePlanCol');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if ($('.planContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() - 20);
                }
                $('.planContainer').removeClass('active');
                //                        totChargesBox.hide();
                payMonthlyButton.hide();
                payAnnuallyButton.hide();
                payOptMessage.hide();
                inetServiceReceiptIH.hide();
                inetServiceReceiptIA.hide();
                payInfoTitle.hide();
                paymentInfoCaption.hide();
                payInfoCol.hide();
                self.addClass('active');
                servicePlanInputBox.val(self.attr('id') + '-included');

//                servicePlanColHeight = servicePlanCol.height();
//                if (servicePlanColHeight > 270) {
//                    servicePlanCol.css({
//                        height: (servicePlanColHeight - 80) + "px"
//                    });
//                }


                updatePaymentView();
            }
        });

        $('.planContainer').click(function () {
            var self = $(this);
            var payMonthlyButton = $('#monthly-pay');
            var payAnnuallyButton = $('#annual-pay');
            var payOptMessage = $('#paymentOptionMessage');
            var servicePlanInputBox = $('input#service_plan');
            var servicePlanCycleInputBox = $('servicePlan-cycle');
            var servicePlanSpeedInputBox = $('servicePlan-speed');
            var inetServiceReceiptIH = $('#serviceChargeUpgradeIH');
            var inetServiceReceiptIA = $('#serviceChargeUpgradeIA');
            var servicePlanCol = $('.servicePlanCol');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                if (!$('.planContainer').hasClass('active')) {
                    orderDetailsCol.height(orderDetailsCol.height() + 20);
                }
                $('.planContainer').removeClass('active');
                $('.includedPlanContainer').removeClass('active');
                payMonthlyButton.removeClass('active');
                payAnnuallyButton.removeClass('active');
                self.addClass('active');
                //                        totChargesBox.fadeIn();
                payMonthlyButton.fadeIn();
                payAnnuallyButton.fadeIn();
                payOptMessage.fadeIn();
                inetServiceReceiptIH.fadeIn();
                inetServiceReceiptIA.fadeIn();
                payInfoTitle.fadeIn();
                paymentInfoCaption.hide();
                payInfoCol.fadeIn();
                servicePlanInputBox.val(self.attr('id'));
                servicePlanSpeedInputBox.val(self.attr('id'));
                servicePlanCycleInputBox.val('');
//                servicePlanCol.css({
//                    height: "350px"
//                });
                //                        totChargesBox.html("<center>Please select to pay monthly or prepay annually</center>");
                updatePaymentView();
            }
        });

        $('#monthly-pay').click(function () {
            var self = $(this);
            //                    var payMonthlyButton = $('#monthly-pay');
            var payAnnuallyButton = $('#annual-pay');
            var servicePlanInputBox = $('input#service_plan');
            //                    var servicePlanCycleInputBox = $('servicePlan-cycle');
            //                    var servicePlanSpeedInputBox = $('servicePlan-speed');
            //                    var totChargesBox = $('.totalCharges');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                payAnnuallyButton.removeClass('active');
                self.addClass('active');
                servicePlanInputBox.val($('.planContainer.active').attr('id') + '-monthly');
                //                        servicePlanCycleInputBox.val('monthly');
                updatePaymentView();
            }
        });

        $('#annual-pay').click(function () {
            var self = $(this);
            var payMonthlyButton = $('#monthly-pay');
            //                    var payAnnuallyButton = $('#annual-pay');
            var servicePlanInputBox = $('input#service_plan');
            //                    var servicePlanCycleInputBox = $('servicePlan-cycle');
            //                    var totChargesBox = $('.totalCharges');

            if (self.hasClass('active')) {
                self.addClass('active');
            } else {
                payMonthlyButton.removeClass('active');
                self.addClass('active');
                servicePlanInputBox.val($('.planContainer.active').attr('id') + '-annual');
                //                        servicePlanCycleInputBox.val('annual');
                updatePaymentView();
            }
        });

        //## CONFIG-OPTION - Switch the following two lines to activate splash page in
        //			  		the beginning or skip the splash page

        // For splash page
        $('#messageContainer').fadeIn();

        // To skip the splash page
        // $('#formContainer').fadeIn();

        // For unformatted Address select fields
        var locAddress = $('#street_address');
        //locAddress.hide();
        locAddress.change(function () {
            var locAddressVal = locAddress.val();
            updateUnitList(locAddressVal);
        });

    });

    //	registrationForm = $('#formContainer form');
    //	registrationForm.zinePretifyForm();
    //	registrationForm.submit(handleFormSubmit);
    //	registrationForm.find('input[type=submit]').click(handleFormSubmit);

    var submitFlag = false;

    function dump(obj) {
        var out = '';
        for (var i in obj) {
            //                out += i + ": " + obj[i] + "\n";
            out += obj[i];
        }

        alert(out);

        // or, if you wanted to avoid alerts...

        //            var pre = document.createElement('pre');
        //            pre.innerHTML = out;
        //            document.body.appendChild(pre)
    }

    function handleFormSubmit() {
        if (submitFlag) {
            return false;
        }
        overlay.show();
        submitFlag = true;

        var serializedForm = registrationForm.serialize();

        $.post('/register', serializedForm, function (msg) {
            submitFlag = false;
            overlay.hide();
            $('span.errorIcon').remove();

//            if (msg.success) {
//                //                                alert('activation code: '+msg.activationCode+"\n"+'timer: '+msg.universal+"\n");
//                var actForm = $('#thankYou form');
//                actForm.find('input[name=ActivationCode]').val(msg.activationCode);
//                actForm.find('input[name=Universal]').val(msg.universal);
//                actForm.find('input[name=PortID]').val(msg.portID);
//
//                $('#formContainer').fadeOut(function () {
//                    registrationForm.get(0).reset();
//                    $('#thankYou').fadeIn();
//                });
//            }
//            //			else if(msg.validation_success){
//            //				$('#formContainer').fadeOut(function(){
//            //					registrationForm.get(0).reset();
//            //					$('#processingContainer').fadeIn();
//            //				});
//            //			}
//            else if (msg.debug) {
//                $('body').append('<div id="overlay"></div><div id="preloader">' + msg.debug_message + '</div>');
//                $('#overlay').click(function () {
//                    $('#overlay,#preloader').remove();
//                });
//            }
//            else
            if (msg.error) {
                $.each(msg, function (k, v) {

                    var errorIcon = $('<span></span>')
                            .addClass('errorIcon');
                    //                                                        ,{className:'errorIcon'});
                    var errorTip = $('<span></span>')
                            .addClass('errorTip')
                            //                                                        .attr({ text : v })
                            .html(v)
                            .hide()
                            .appendTo(errorIcon);
                    //                                                        ,{className:'errorTip',text:v}).hide().appendTo(errorIcon);

                    errorIcon.hover(function () {
                        errorTip.stop().fadeIn(function () {
                            errorTip.css('opacity', 1);
                        });
                    }, function () {
                        errorTip.stop().fadeOut('slow', function () {
                            errorTip.hide().css('opacity', 1);
                        });
                    });

                    registrationForm.find('[name=' + k + ']').closest('.formRow').append(errorIcon);

                    if ($(window).width() - errorIcon.offset().left > 240) {
                        errorTip.css('left', 30);
                    }
                    else {
                        errorTip.css('right', 30);
                    }
                });
            }
            else {

//                dump(msg);

                $('body').html(msg);
//                alert("Error: We encountered a browser error.\n\nPlease try a different browser (i.e. Firefox).\n\nIf you continue to experience issues please contact us at 312-600-3800.");
                //                          alert ('Error: Bad response received: '+dump(msg));
                //                          alert ('Error: Bad response received');
            }

        });
        // },'json');
        return false;
    }

    function displayValidationErrors(errorInJson){

        overlay.hide();
        msg = JSON.parse(errorInJson);
        $.each(msg, function (k, v) {

            var errorIcon = $('<span></span>')
                    .addClass('errorIcon');
            //                                                        ,{className:'errorIcon'});
            var errorTip = $('<span></span>')
                    .addClass('errorTip')
                    //                                                        .attr({ text : v })
                    .html(v[0])
                    .hide()
                    .appendTo(errorIcon);
            //                                                        ,{className:'errorTip',text:v}).hide().appendTo(errorIcon);

            errorIcon.hover(function () {
                errorTip.stop().fadeIn(function () {
                    errorTip.css('opacity', 1);
                });
            }, function () {
                errorTip.stop().fadeOut('slow', function () {
                    errorTip.hide().css('opacity', 1);
                });
            });

            registrationForm.find('[name=' + k + ']').closest('.formRow').append(errorIcon);

            if ($(window).width() - errorIcon.offset().left > 240) {
                errorTip.css('left', 30);
            }
            else {
                errorTip.css('right', 30);
            }
        });
    }

    function updateUnitList(address) {
        $unitDropDown = $('#unit');
        $unitDropDown.hide();
        $.get('/getUnitNumbersAjax', {
            'address': address
        }, function (output) {
            $unitDropDown.html(output);
            $unitDropDown.fadeIn();
        });

    }

    // function updateUnitListByText(locAdd){
    // $unitDropDown = $('#fr_field5');
    // $unitDropDown.hide();
    // $.post('assets/helpers/buildings.php', { 'locAdd': locAdd }, function(output) {
    // alert(output);
    // $unitDropDown.html(output);
    // $unitDropDown.fadeIn();
    // });
    //
    // }

    function displayOverlay() {
    }

});

(function ($) {

    $.fn.zinePretifyForm = function () {
        //		return $this.each(function(){
        //                $(this).each(function(){

        var form = $(this);

        form.find('input[type=button],input[type=submit],button').each(function () {

            var originalButton = $(this),
                    button = $('<span></span>')
                    .addClass('button')
                    .html(originalButton.val() + '<span></span>')

            button.insertAfter(originalButton.hide());
            // button.insertAfter(originalButton);

            button.click(function () {
                originalButton.click();
            });

        });

        form.find('input[type=checkbox]').each(function () {

            var originalCheckBox = $(this),
                    checkBox = $('<span></span>')
                    .addClass('checkBox ' + (this.checked ? 'checked' : ''));
            checkBox.insertAfter(originalCheckBox.hide());
            checkBox.click(function () {
                checkBox.toggleClass('checked');
                checkBox.hasClass('checked') ? originalCheckBox.prop('checked', true) : originalCheckBox.removeAttr('checked');

            });

        });

        form.find('input[type=radio]').each(function () {

            var originalRadio = $(this),
                    radio = $('<span></span>')
                    .addClass('radio ' + (this.checked ? 'checked' : ''));
            radio.insertAfter(originalRadio.hide());
            radio.click(function () {
                $('input[type=radio][name=' + originalRadio.attr('name') + ']').each(function () {
                    $(this).next().removeClass('checked');
                });

                radio.addClass('checked');
                originalRadio.prop('checked', true);
            });

        });

        /*
         form.find('select').each(function(i){

         var select = $(this);

         var selectBoxContainer = $('<span>',{
         width		: select.outerWidth(),
         className	: 'selectContainer',
         html		: '<div class="selectBox"></div><span></span>',
         css			: {zIndex : 1000-i}
         });

         var dropDown = $('<ul>',{className:'dropDown'});
         var selectBox = selectBoxContainer.find('.selectBox');

         select.find('option').each(function(i){
         var option = $(this);

         if(i==select.attr('selectedIndex')){
         selectBox.html(option.text());
         }

         var li = $('<li>',{
         html:	option.html()
         });

         li.click(function(){

         selectBox.html(option.text());
         dropDown.trigger('hide');

         select.val(option.val());
         return false;
         });

         dropDown.append(li);
         });

         selectBoxContainer.append(dropDown.hide());
         select.hide().after(selectBoxContainer);

         dropDown.bind('show',function(){

         if(dropDown.is(':animated')){
         return false;
         }

         selectBox.addClass('expanded');
         dropDown.slideDown('fast');

         }).bind('hide',function(){

         if(dropDown.is(':animated')){
         return false;
         }

         selectBox.removeClass('expanded');
         dropDown.slideUp('fast');

         }).bind('toggle',function(){
         if(selectBox.hasClass('expanded')){
         dropDown.trigger('hide');
         }
         else dropDown.trigger('show');
         });

         selectBoxContainer.click(function(){
         dropDown.trigger('toggle');
         return false;
         });

         $(document).click(function(){
         dropDown.trigger('hide');
         });
         });
         */

        //		});
    };

})(jQuery);
