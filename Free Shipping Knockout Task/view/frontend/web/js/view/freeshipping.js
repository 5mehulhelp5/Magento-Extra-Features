define(
    [
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, Component, quote) {
        "use strict";
        var freeshippingminimumamount = window.freeshippingamount;
        var currency = window.currency;
        return Component.extend({
            defaults: {
                template: 'Codilar_Sales/freeshipping'
            },
            getFreeShippingConfig: function() {
                var totals = quote.totals();
                var subtotalAmount = (totals ? totals : quote)['subtotal'];
                if(freeshippingminimumamount && freeshippingminimumamount - subtotalAmount >0){
                    let width = (subtotalAmount/freeshippingminimumamount)*100;
                    document.getElementById('freeshiping-progress-bar-item').style.width = width + '%';
                    return 'Shop '+ currency +(freeshippingminimumamount-subtotalAmount)+' More to Avail Free Shipping';
                }
                else {
                    document.getElementById('freeshiping-progress-bar-item').style.width = '100%';
                    document.getElementById('freeshiping-progress-bar-item').style.backgroundColor = 'green';
                    return 'Your Order is eligible for Free Shippping';
                }
            },
            getfreeshippingAmount: function (){
                return freeshippingminimumamount;
            }
        });
    }
);
