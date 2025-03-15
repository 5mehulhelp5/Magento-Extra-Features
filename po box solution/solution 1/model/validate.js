define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'mage/validation'
    ],
    function ($, modal, url, quote) {
        'use strict';
        return {
            validate: function () {
                var orderFlag = true;
                var streetAddress = quote.shippingAddress().street;
                console.log('hi');
                var pattern = /^P(ost)?\.?\s*O(ffice)?\.?\s*B(ox)?/i;
                var street = streetAddress.toString();
                console.log(streetAddress);
                if (street.match(pattern)) {
                    orderFlag = false;
                    alert('PO Box addresses are not accepted.');
                }
                return orderFlag;
            }
        };
    }
);
