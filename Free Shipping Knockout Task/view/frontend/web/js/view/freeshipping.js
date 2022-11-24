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
                    return 'Shop '+ currency +(freeshippingminimumamount-subtotalAmount)+' More to Avail Free Shipping';
                }
                else {
                    return 'Your Order is eligible for Free Shippping';
                }
            },
            getfreeshippingAmount: function (){
                return freeshippingminimumamount;
            }
        });
    }
);
