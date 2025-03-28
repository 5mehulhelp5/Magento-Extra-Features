/**
 * Candere Software
 *
 * @category PG
 * @package  Scheme
 * @author Candere
 * @copyright Candere Pvt. Ltd. (https://www.candere.com/)
 */
define(['jquery', 'uiComponent','Magento_Ui/js/modal/alert', 'ko','mage/url','Magento_Catalog/js/price-utils','Magento_Ui/js/model/messageList','jquery/validate','mage/validation','slick'],
    function ($, Component,alert, ko,url,priceUtils,messageList) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'KalyanUs_Scheme/scheme-enrollment-component',
                schemeSuccessTemplate : 'KalyanUs_Scheme/enrollment/scheme-enrollment-success',
                schemeFailureTemplate : 'KalyanUs_Scheme/enrollment/scheme-enrollment-failure',
                schemePlanStepTemplate : 'KalyanUs_Scheme/enrollment/scheme-enrollment-planstep',
                schemePersonalStepTemplate : 'KalyanUs_Scheme/enrollment/scheme-enrollment-personalstep',
                razorpayDataFrameLoaded: false,
                selectedEmiValue:ko.observable(0),
                selectedDurationValue:ko.observable(0),
                auto_monthly_payment:ko.observable(false),
                benefitPercentageValue:ko.observable(0),
                isSuccessEnrollment:ko.observable(false),
                isFailureEnrollment:ko.observable(false),
                isverifyMobileNumber:ko.observable(false),
                isSendOtpVisible:ko.observable(true),
                verifyError:ko.observable(false),
                isAllowNominee:ko.observable(false),
                nomineText:ko.observable('Add Nominee'),
                isPlanStep:ko.observable(true),
                isPersonalStep:ko.observable(false),
                isStepVisible:ko.observable(true),
                currentStep:ko.observable('plan'),
                isOtpSendFormobile:ko.observable(false),
                schemeEmail:ko.observable(''),
                schemeCustomerName:ko.observable(''),
                schemeMobileNumber:ko.observable(''),
                schemeAddress:ko.observable(''),
                schemePincode:ko.observable(''),
                schemeState:ko.observable(''),
                schemeCity:ko.observable(''),
                nomineeName:ko.observable(''),
                nomineeMobileNumber:ko.observable(''),
                nomineeRelationship:ko.observable(''),
                nomineeNationality:ko.observable(''),
                quote:[],
                schemeData:[],
                successResponseArr:ko.observableArray([])
            },
            initialize: function () {
                this._super();
                var self = this;
                self.nextStep(self.currentStep());

                ko.bindingHandlers.slider = {
                    init: function (element, valueAccessor, allBindingsAccessor) {
                        var options = allBindingsAccessor().sliderOptions || {};
                        //var options = {min: 1000, max: 15000, range: 'min', step: 100,value:7000};
                        $(element).slider(options);
                        ko.utils.registerEventHandler(element, "slidechange", function (event, ui) {
                            //var observable = valueAccessor();
                            //observable(ui.value);
                            self.selectedEmiValue(ui.value);
                        });
                        ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                            $(element).slider("destroy");
                        });
                        ko.utils.registerEventHandler(element, "slide", function (event, ui) {
                            //var observable = valueAccessor();
                            //observable(ui.value);
                            self.selectedEmiValue(ui.value);
                        });
                    },
                    update: function (element, valueAccessor) {
                        var value = ko.utils.unwrapObservable(valueAccessor());
                        if (isNaN(value)) value = 0;
                        self.selectedEmiValue(value);
                        $(element).slider("value", value);
                    }
                };
                $(document).ready(function() {
                    self.selectedDurationValue();
                    self.durationSelected(self.selectedDurationValue());
                });
                if(self.selectedEmiValue()==0){
                    self.selectedEmiValue('');
                }
            },
            initObservable: function() {
                var self = this._super()
                .observe(['quote']);
                self.setQuoteFields(this.quote());

                if(!self.razorpayDataFrameLoaded) {
                    $.getScript("https://checkout.razorpay.com/v1/checkout.js", function() {
                        self.razorpayDataFrameLoaded = true;
                    });
                }
                return self;
            },
            setQuoteFields: function(quote){
                var self = this;
                if(quote){
                    var q=quote || {};
                    if(q.duration!='' && typeof q.duration!=='undefined'){
                        self.selectedDurationValue(String(Math.round(q.duration)));
                        self.setBenefitPercentage(self.selectedDurationValue());
                    }else{
                        if(this.schemeData.defaultDuration!='' && typeof this.schemeData.defaultDuration!=='undefined'){
                            self.selectedDurationValue(String(Math.round(this.schemeData.defaultDuration)));
                            self.setBenefitPercentage(self.selectedDurationValue());
                        }
                    }
                    if(q.auto_monthly_payment!='' && typeof q.auto_monthly_payment!=='undefined'){
                        if(q.auto_monthly_payment=='1'){
                            self.auto_monthly_payment(true);
                        }
                    }
                    if(q.emi_amount!='' && typeof q.emi_amount!=='undefined'){
                        self.selectUsuallyEmailAmount(Math.round(q.emi_amount));
                        self.nextStep('personal');
                    }
                    if(q.email_id!='' && typeof q.email_id!=='undefined'){
                        self.schemeEmail(q.email_id);
                    }
                    if(q.customer_name!='' && typeof q.customer_name!=='undefined'){
                        self.schemeCustomerName(q.customer_name);
                    }
                    if(q.scheme_mobile_number!='' && typeof q.scheme_mobile_number!=='undefined'){
                        self.schemeMobileNumber(q.scheme_mobile_number);
                    }
                    if(q.address!='' && typeof q.address!=='undefined'){
                        self.schemeAddress(q.address);
                    }
                    if(q.pincode!='' && typeof q.pincode!=='undefined'){
                        self.schemePincode(q.pincode);
                    }
                    if(q.state!='' && typeof q.state!=='undefined'){
                        self.schemeState(q.state);
                    }
                    if(q.city!='' && typeof q.city!=='undefined'){
                        self.schemeCity(q.city);
                    }
                    var nomineeData=q.nominee_info || {};
                    if(nomineeData!=''){
                        var expandNomineeTab=false;
                        if(nomineeData.name!='' && typeof nomineeData.name!=='undefined'){
                            self.nomineeName(nomineeData.name);
                            expandNomineeTab=true;
                        }
                        if(nomineeData.mobilenumber!='' && typeof nomineeData.mobilenumber!=='undefined'){
                            self.nomineeMobileNumber(nomineeData.mobilenumber);
                            expandNomineeTab=true;
                        }
                        if(nomineeData.relationship!='' && typeof nomineeData.relationship!=='undefined'){
                            self.nomineeRelationship(nomineeData.relationship);
                            expandNomineeTab=true;
                        }
                        if(nomineeData.nationality!='' && typeof nomineeData.nationality!=='undefined'){
                            self.nomineeNationality(nomineeData.nationality);
                            expandNomineeTab=true;
                        }
                        if(expandNomineeTab==true){
                            self.addnominee();
                        }
                    }
                }else{
                    if(this.schemeData.defaultDuration!='' && typeof this.schemeData.defaultDuration!=='undefined'){
                        self.selectedDurationValue(String(Math.round(this.schemeData.defaultDuration)));
                        self.setBenefitPercentage(self.selectedDurationValue());
                    }
                }
            },
            getTotalEmiAmount :function(emi_amount){
                var self=this;
                return emi_amount*self.selectedDurationValue();
            },
            getTotalEmiAmountWithFormat: function(emi_amount){
                return priceUtils.formatPrice(Math.round(this.getTotalEmiAmount(emi_amount)), window.schemeConfig.priceFormat);
            },
            getBenefit :function(emi_amount){
                var self=this;
                return ((emi_amount/100)*self.benefitPercentageValue());
            },
            getBenefitWithFormat:function(emi_amount){
                return priceUtils.formatPrice(Math.round(this.getBenefit(emi_amount)), window.schemeConfig.priceFormat);
            },
            getRedeemableAmount: function(emi_amount){
                var totalAmount=this.getTotalEmiAmount(emi_amount);
                var benefit=this.getBenefit(emi_amount);
                return (totalAmount + benefit);
            },
            getRedeemableAmountWithFormat : function(emi_amount){
                return priceUtils.formatPrice(Math.round(this.getRedeemableAmount(emi_amount)), window.schemeConfig.priceFormat);
            },
            getFormattedPrice : function(price){
                return priceUtils.formatPrice(Math.round(price), window.schemeConfig.priceFormat);
            },
            selectedEmiVal:function(v){
                var self=this;
                if(v==self.selectedEmiValue()){
                    return true;
                }
                return false;
            },
            selectUsuallyEmailAmount :function(value){
                var self = this;
                self.selectedEmiValue(value);
                // $("#monthly_amount").slider("value", value);
                $('.amountBox .input_group_ .label_').addClass('pushedUp');
            },
            getPinCodeDetail : function (value, elem) {
                var self = this;
                var pincode = elem.target.value;
                var dataSchemeForm = $('#scheme-enrollment-form');
                dataSchemeForm.validation();
                var dataSchemePincodeForm = jQuery('#sch-pincode');
                dataSchemePincodeForm.validation();
                if(dataSchemePincodeForm.validation('isValid')){
                    jQuery.ajax({
                        url: url.build('scheme/enrollment/pincodeapi'),
                        type: "POST",
                        showLoader: true,
                        data: {pincode:pincode},
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                if(response.data!='' && typeof response.data !== 'undefined'){
                                    self.schemeState(response.data.region_id);
                                    self.schemeCity(response.data.city);
                                }
                            }
                        }
                    });
                }
            },
            nextStep: function(no){
                var self=this;
                $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list').removeClass('active');
                if(no=='payment_success'){
                    //success step
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.payment').addClass('complete');
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.personal').addClass('complete');
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.plan').addClass('complete');
                    self.isSuccessEnrollment(true);
                    self.isFailureEnrollment(false);
                    self.isPersonalStep(false);
                    self.isPlanStep(false);
                    self.isStepVisible(false);
                    self.currentStep(no);
                }else if(no=='payment_failure'){
                    //failure step
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.payment').addClass('active');
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.personal').addClass('complete');
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.plan').addClass('complete');
                    self.isSuccessEnrollment(false);
                    self.isFailureEnrollment(true);
                    self.isPersonalStep(false);
                    self.isPlanStep(false);
                    self.isStepVisible(false);
                    self.currentStep(no);
                }else if(no=='personal'){
                    //personal step
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.personal').addClass('active');
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.plan').addClass('complete');
                    self.isSuccessEnrollment(false);
                    self.isFailureEnrollment(false);
                    self.isPersonalStep(true);
                    self.isPlanStep(false);
                    self.isStepVisible(true);
                    self.currentStep(no);
                }else{
                    // plan step
                    $('.scheme_steps  .sectionHeader .schemeSteps .schemeSteps__list.plan').addClass('active');
                    self.isSuccessEnrollment(false);
                    self.isFailureEnrollment(false);
                    self.isPersonalStep(false);
                    self.isPlanStep(true);
                    self.isStepVisible(true);
                    self.currentStep(no);
                }
            },
            continueButton:function(){
                var self=this;
                var current_step=self.currentStep();
                if(current_step=='personal'){
                    self.submitform();
                }else if(current_step=='plan'){

                    var emi=self.selectedEmiValue();
                    if(emi!='' && typeof emi!=='undefined'){
                        self.selectedEmiValue(parseInt(emi));
                    }
                    self.quoteSave();
                }
            },
            durationChanged :function(value,ele){
                var self=this;
                self.selectedDurationValue(String(ele.target.value));
                self.setBenefitPercentage(ele.target.value);
                $('#duration-'+value).attr('checked',true);
            },
            durationSelected :function(value){
                var self=this;
                self.selectedDurationValue(String(value));
                self.setBenefitPercentage(value);
                $('#duration-'+value).attr('checked',true);
            },
            selectDurationScheme:function(duration){
                var self=this;
                if(duration==self.selectedDurationValue()){
                    return true;
                }
                return false;
            },
            setBenefitPercentage: function(duration){
                var self=this;
                if(duration!='' && typeof duration!='undefined'){
                    jQuery.each(this.schemeData.schemes, function(index, item) {
                        if(item.duration==duration){
                            if(item.benefit_percentage!='' && typeof item.benefit_percentage!='undefined'){
                                self.benefitPercentageValue(item.benefit_percentage);
                            }
                        }
                    });
                }
            },
            addnominee: function(){
                var self=this;
                if(self.isAllowNominee()){
                    self.isAllowNominee(false);
                    self.nomineText('Add Nominee');
                    self.makeEmptyNomineeField();
                }else{
                    self.isAllowNominee(true);
                    self.nomineText('remove Nominee');
                }
            },
            makeEmptyNomineeField: function(){
                var self=this;
                self.nomineeName('');
                self.nomineeMobileNumber('');
                self.nomineeRelationship('');
                self.nomineeNationality('');
            },
            changePlan :function(){
                var self=this;
                var current_step=self.currentStep();
                if(current_step=='personal'){
                    self.nextStep('plan');
                }
            },
            sendotpMobile: function(){
                var self = this;
                var dataSchemeForm = $('#scheme-enrollment-form');
                dataSchemeForm.validation();
                var dataSchemeMobileForm = jQuery('#sch-mobile-number');
                dataSchemeMobileForm.validation();
                if(dataSchemeMobileForm.validation('isValid')){
                    jQuery.ajax({
                        url: url.build('scheme/enrollment/sendotp'),
                        type: "POST",
                        showLoader: true,
                        data: {mobile:$('#sch-mobile-number').val()},
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                self.isOtpSendFormobile(true);
                                self.allowToEditMobileNumber();
                            }else{
                                if(response.message!='' && typeof response.message !== 'undefined'){
                                    messageList.addErrorMessage({ message: response.message });
                                }
                                if(response.redirect)
                                {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                }
            },
            verifyOtp: function(){
                var self = this;
                var dataSchemeMobileForm = $('#sch-mobile-number-otp');
                dataSchemeMobileForm.validation();
                var status= dataSchemeMobileForm.validation('isValid');
                if(status){
                    jQuery.ajax({
                        url: url.build('scheme/enrollment/verifyOtp'),
                        type: "POST",
                        showLoader: true,
                        data: {mobile:$('#sch-mobile-number').val(),otp:$('#sch-mobile-number-otp').val()},
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                self.isverifyMobileNumber(true);
                                self.isSendOtpVisible(false);
                                self.isOtpSendFormobile(false);
                                self.allowToEditMobileNumber();
                            }else{
                                if(response.message!='' && typeof response.message !== 'undefined'){
                                    // messageList.addErrorMessage({ message: response.message });
                                    self.verifyError(true);

                                    setTimeout(function() {
                                        self.verifyError(false);
                                    }, 5000);
                                }
                                if(response.redirect)
                                {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                }
            },
            quoteSave :function(){
                var self = this;

                var dataSchemeForm = $('#scheme-enrollment-form');
                dataSchemeForm.validation();
                var dataSchemeEmiForm = jQuery('#emi_amount');
                dataSchemeEmiForm.validation();
                if(dataSchemeEmiForm.validation('isValid')){
                    let planstepDetail = { 'step_enrollment':'plan_step','plan_type': $("input[name='scheme[duration]']:checked").val(), 'monthly_pay_amount': $('#emi_amount').val(), 'benefit_amt': self.getBenefit($('#emi_amount').val()),'redeemable_amt': self.getRedeemableAmount($('#emi_amount').val())};
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push({'event': 'scheme_planpage','data' : planstepDetail});
                    jQuery.ajax({
                        url: url.build('scheme/enrollment/quote'),
                        type: "POST",
                        showLoader: true,
                        data: dataSchemeForm.serializeArray(),
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                self.nextStep('personal');
                                self.setQuoteFields(response.data.quote);
                            }else{
                                if(response.message!='' && typeof response.message !== 'undefined'){
                                    messageList.addErrorMessage({ message: response.message });
                                }
                                if(response.redirect)
                                {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                }
            },
            submitform :function(){
                var self = this;
                // console.log(self.selectedEmiValue());
                // console.log('next step');
                // alert({
                //     title: $.mage.__('Error Message'),
                //     content: $.mage.__('Nominee fields are required.'),
                //     actions: {
                //         always: function(){}
                //     }
                // });
                // return;
                var dataSchemeForm = $('#scheme-enrollment-form');
                dataSchemeForm.validation();
                //dataSchemeForm.mage('validation', {});
                var status= dataSchemeForm.validation('isValid');

                if(status){
                    if(self.isverifyMobileNumber()){
                        var sch_state=$("#sch-state").val();
                        var sch_state_name=$('#sch-state > option[value='+sch_state+']').html();
                        let personalstepDetail = {
                            'step_enrollment':'personal_step',
                            'customer_name': $("input[name='scheme[customer_name]']").val(),
                            'email': $("#sch-email").val(),
                            'phone': $("#sch-mobile-number").val(),
                            'address': $("#sch-address").val(),
                            'pincode': $("#sch-pincode").val(),
                            'state': sch_state_name,
                            'city': $("#sch-city").val(),
                            'nominee_name': $("#sch-nominee-full-name").val(),
                            'nominee_relationship': $("#sch-nominee-relationship").val(),
                            'nominee_phone': $("#sch-nominee-mobile-number").val(),
                            'nominee_nationality': $("#sch-nominee-nationality").val(),
                            'monthly_pay_amount': parseInt($('#emi_amount').val()),
                            'benefit_amt': self.getBenefit($('#emi_amount').val()),
                            'redeemable_amt': self.getRedeemableAmount($('#emi_amount').val())
                        };
                        window.dataLayer = window.dataLayer || [];
                        dataLayer.push({'event': 'scheme_personalpage','data' : personalstepDetail});
                        var datapost = dataSchemeForm.serializeArray();
                        if(self.isverifyMobileNumber()){
                            datapost.push({name: "scheme[is_mobile_verified]", value: 1});
                        }
                        jQuery.ajax({
                            url: url.build('scheme/enrollment/buy'),
                            type: "POST",
                            showLoader: true,
                            data: datapost,
                            cache: false,
                            success: function(response){
                                if(response.status==true){
                                    self.renderIframe(response.data);
                                }else{
                                    if(response.message!='' && typeof response.message !== 'undefined'){
                                        messageList.addErrorMessage({ message: response.message });
                                    }
                                }
                            }
                        });
                    }else{
                        messageList.addErrorMessage({ message: 'Please verify mobile Number.' });
                    }
                }
            },
            renderIframe: function(data) {
                var self = this;
                var transactionsreceipt = data.transactionsreceipt;
                var order_id = data.order_id;
                var primaryLockerId = data.primaryLockerId;
                var razorpay_payment_id = "";
                var razorpay_signature = "";
                var generated_signature = "";
                var hash ="";
                var razorpay_order_id="";

                var options={
                    "key": data.razorpayapikey,
                    "currency": data.razorpay.currency,
                    "name": data.razorpay.name,
                    "description": data.razorpay.description,
                    "image": data.razorpay.image,
                    "order_id": order_id,
                    "retry": 0,
                    "handler": function (data) {
                        self.validatePayment(data,order_id,transactionsreceipt);
                    },
                    modal: {
                        ondismiss: function(data) {
                            self.failureResponse(data,order_id,transactionsreceipt);
                        }
                    },
                    "prefill": data.razorpay.prefill,
                    "notes": data.razorpay.notes,
                    "theme": data.razorpay.theme
                };
                this.rzp = new Razorpay(options);
                this.rzp.open();
            },
            failureResponse : function(data,order_id,transactionsreceipt){
                var self=this;
                if(typeof data!=='undefined'){
                    data.order_id = order_id;
                    data.status = 'failure';
                    data.transactionsreceipt = transactionsreceipt;
                    jQuery.ajax({
                        url: url.build('scheme/enrollment/payment'),
                        type: "POST",
                        showLoader: true,
                        data: data,
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                if(response.payment_failure==true){
                                    self.nextStep('payment_failure');
                                    if(response.datalayer!='' && typeof response.datalayer!=='undefined'){
                                        window.dataLayer = window.dataLayer || [];
                                        dataLayer.push({'event': 'scheme_payment_failure','data' : response.datalayer});
                                    }
                                }
                            }
                        }
                    });
                }
            },
            validatePayment :function(data,order_id,transactionsreceipt){
                var self = this;
                data.order_id = order_id;
                data.status = 'success';
                data.transactionsreceipt = transactionsreceipt;
                jQuery.ajax({
                    url: url.build('scheme/enrollment/payment'),
                    type: "POST",
                    showLoader: true,
                    data: data,
                    cache: false,
                    success: function(response){
                        if(response.status==true){
                            if(response.payment_done==true){
                                self.successResponseArr.push(response.data);
                                self.nextStep('payment_success');
                                if(response.datalayer!='' && typeof response.datalayer!=='undefined'){
                                    window.dataLayer = window.dataLayer || [];
                                    dataLayer.push({'event': 'scheme_enrolment','data' : response.datalayer});
                                }
                            }
                        }
                    }
                });
            },
            retryPaymentUrl: function(){
                if(this.schemeData.urls.retryUrl!='' && typeof this.schemeData.urls.retryUrl!=='undefined'){
                    return this.schemeData.urls.retryUrl;
                }
                return '';
            },
            getPlanbookScheme: function(){
               if(this.schemeData.urls.planbookUrl!='' && typeof this.schemeData.urls.planbookUrl!=='undefined'){
                    return this.schemeData.urls.planbookUrl;
                }
                return '';
            },
            getTermAndCondition: function(){
                if(this.schemeData.urls.termAndConditionUrl!='' && typeof this.schemeData.urls.termAndConditionUrl!=='undefined'){
                    return this.schemeData.urls.termAndConditionUrl;
                }
                return '';
            },
            allowToEditMobileNumber: function(){
                var self=this;
                if(self.isOtpSendFormobile()==true || self.isverifyMobileNumber()==true){
                    return true;
                }else{
                    return false;
                }
            },
            closePopUp: function(){
                // this.isOpen(false);
                this.isOtpSendFormobile(false)
            }
        });
    }
);
