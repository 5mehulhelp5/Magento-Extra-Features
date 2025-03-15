/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'ko',
        'Magento_Checkout/js/model/totals',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote'
    ],
    function () {
        'use strict';
        console.log('hi');
        var mixin = {
            displayCustomname: function() {
                return 'Prajewal Joshi'
            },
        };
        return function (target) {
            return target.extend(mixin);
        };
    }
);
