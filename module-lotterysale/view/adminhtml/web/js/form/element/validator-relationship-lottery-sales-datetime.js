define([
    'jquery',
    'moment',
    'mageUtils'
], function ($, moment, utils) {
    'use strict';
    function validatorRelationship(dateTimeTo, dateTimeFrom) {
        let endDate = dateTimeTo ? Date.parse(dateTimeTo): '',
            startDate = dateTimeFrom ? Date.parse(dateTimeFrom) : '';

        return (startDate < endDate) || !endDate;
    }

    return function (validator) {
        validator.addRule(
            'validator-relationship-application-from-to',
            function (value) {
                let from = $("input[name='product[casio_lottery_sales][application_date_from]']").val(),
                    to = $("input[name='product[casio_lottery_sales][application_date_to]']").val();
                if(from || to) {
                    return validatorRelationship(to, from);
                }
                return true;
            },
            $.mage.__("'Application Date To' must be greater than 'Application Date From'")
        );
        validator.addRule(
            'validator-relationship-application-to-purchasedeadline',
            function (value) {
                let from = $("input[name='product[casio_lottery_sales][application_date_to]']").val(),
                    to = $("input[name='product[casio_lottery_sales][purchase_deadline]']").val();
                if(from || to) {
                    return validatorRelationship(to, from);
                }
                return true;
            },
            $.mage.__("'Purchase Deadline' must be greater than 'Application Date To'")
        );
        return validator;
    }
});