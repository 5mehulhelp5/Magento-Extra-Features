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
                template: 'KalyanUs_Scheme/scheme-installment-component',
                razorpayDataFrameLoaded: false,
                enrollmentDetail:ko.observableArray([]),
                successResponseArr:ko.observableArray([])
            },
            initialize: function () {
                this._super();
            },
            initObservable: function() {
                var self = this._super()
                .observe(['enrollmentDetail']);

                if(!self.razorpayDataFrameLoaded) {
                    $.getScript("https://checkout.razorpay.com/v1/checkout.js", function() {
                        self.razorpayDataFrameLoaded = true;
                    });
                }
                return self;
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
                if(data!='' && typeof data !== 'undefined'){
                    data.order_id = order_id;
                    data.status = 'failure';
                    data.transactionsreceipt = transactionsreceipt;
                    jQuery.ajax({
                        url: url.build('scheme/installment/payment'),
                        type: "POST",
                        showLoader: true,
                        data: data,
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                if(response.payment_failure==true){
                                    if(response.message!='' && typeof response.message!=='undefined'){
                                        messageList.addErrorMessage({ message: response.message });
                                    }
                                }
                            }
                        }
                    });
                }
            },
            validatePayment :function(data,order_id,transactionsreceipt){
                var self = this;
                if(data!='' && typeof data !== 'undefined'){
                    data.order_id = order_id;
                    data.status = 'success';
                    data.transactionsreceipt = transactionsreceipt;
                    jQuery.ajax({
                        url: url.build('scheme/installment/payment'),
                        type: "POST",
                        showLoader: true,
                        data: data,
                        cache: false,
                        success: function(response){
                            if(response.status==true){
                                if(response.payment_done==true){
                                    if(response.data.list!='' && typeof response.data.list!=='undefined'){
                                        self.enrollmentDetail(response.data.list);
                                    }
                                }
                            }
                        }
                    });
                }
            },
            getFormattedPrice : function(price){
                return priceUtils.formatPrice(Math.round(price), window.schemeConfig.priceFormat);
            },
            payInstallment :function(enrollmentId){
                var self = this;
                if(enrollmentId!='' && typeof enrollmentId!=='undefined'){
                    jQuery.ajax({
                        url: url.build('scheme/installment/pay'),
                        type: "POST",
                        showLoader: true,
                        data: {'enrollment_id':enrollmentId},
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
                    messageList.addErrorMessage({ message: 'Enrorllment Information is not found.' });
                }
            }
        });
    }
);
