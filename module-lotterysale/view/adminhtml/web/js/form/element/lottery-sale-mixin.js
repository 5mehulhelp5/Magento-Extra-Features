define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/validation/validator'
], function ($, _, validator) {
    'use strict';

    var setEnabledSetting = {
        defaults: {
            enabledValues: [],
            dependenciesValues: [],
            requiredValues: []
        },

        /**
         * set field to be required
         */
        setRequired: function (value) {
            var self = this, required = false;
            _.each(this.requiredValues, function (callbacks) {
                var element = $("input[name='"+ callbacks +"']");
                if (element.val() === undefined) {
                    required = self.required();
                    return;
                }
                if (element.val() !== '') {
                    required = true
                    return;
                }
            })
            if (required) {
                this.required(required);
                this.validation['required-entry'] = required;
            } else {
                if (typeof this.validation['required-entry'] !== 'undefined'){
                    delete this.validation['required-entry'];
                }
                this.required(required);
            }
            this.error(false);
        },

        /**
         * set required from field application-date-from
         */
        setApplicationFromRequired: function () {
            this.setRequired();
        },

        /**
         * set required from field application-date-to
         */
        setApplicationToRequired: function () {
            this.setRequired();
        },

        /**
         * Set the field as enable
         */
        setCasioDisabled: function () {
            var self = this, disable = false;
            _.each(this.enabledValues, function (callbacks) {
                var element = $("input[name='"+ callbacks +"']");
                if (element.val() === undefined) {
                    disable = self.disabled();
                    return;
                }
                if (element.val() !== '') {
                    disable = true
                    return;
                }
            })
            if (disable) {
                this.serviceDisabled(true)
                this.disable();
            } else {
                this.serviceDisabled(false)
                if (this.isUseDefault() && this.hasService()) {
                    this.disable();
                } else {
                    this.enable()
                }
            }
        },

        /**
         * set pre-order enable or disable
         */
        setPreOrderFromDisable: function () {
            this.setCasioDisabled();
        },

        /**
         * set pre-order enable or disable
         */
        setPreOrderEndDisable: function () {
            this.setCasioDisabled();
        },

        /**
         * set lottery-sale enable or disable
         */
        setApplicationFromDisable: function () {
            this.setCasioDisabled();
        },

        /**
         * set lottery-sale enable or disable
         */
        setApplicationToDisable: function () {
            this.setCasioDisabled();
        },

        /**
         * set lottery-sale enable or disable
         */
        setLotteryDateDisable: function () {
            this.setCasioDisabled();
        },

        /**
         * set lottery-sale enable or disable
         */
        setPurchaseDeadlineDisable: function () {
            this.setCasioDisabled();
        }

    };

    return function (target) {
        return target.extend(setEnabledSetting);
    };
});
