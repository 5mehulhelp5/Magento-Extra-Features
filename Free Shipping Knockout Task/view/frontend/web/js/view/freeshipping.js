define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function ($, ko, Component, quote, customerData) {
    "use strict";

    var freeshippingminimumamount = window.freeshippingamount;
    var currency = window.currency;
    var cartData = customerData.get('cart');

    return Component.extend({
        defaults: {
            template: 'Codilar_Sales/freeshipping'
        },
        freeshipping: ko.observable(),
        progressBarWidth: ko.observable('100%'),
        progressBarColor: ko.observable('green'),

        initialize: function () {
            this._super();

            cartData.subscribe(function () {
                this.getFreeShippingConfig();
            }, this);

            this.getFreeShippingConfig();
        },
        getFreeShippingConfig: function () {
            var cart = customerData.get('cart');
            var subtotalAmount = cart().subtotalAmount;

            if (subtotalAmount <= 0) {
                window.location.reload();
            } else if (freeshippingminimumamount && (freeshippingminimumamount - subtotalAmount) > 0) {
                let width = (subtotalAmount / freeshippingminimumamount) * 100;
                this.progressBarWidth(width + '%');
                this.progressBarColor('#ff5501');
                this.freeshipping('Shop ' + currency + (freeshippingminimumamount - subtotalAmount) + ' More to Avail Free Shipping');
            } else {
                this.progressBarWidth('100%');
                this.progressBarColor('green');
                this.freeshipping('Your Order is eligible for Free Shipping');
            }
        },
        getfreeshippingAmount: function () {
            return freeshippingminimumamount;
        },
        getFreeshipping: function () {
            return this.freeshipping;
        }
    });
});
