define([
    'jquery'
],
function ($) {
    'use strict';
    $.widget("mage.lotterySales", {

        loadMore: function () {
            var item = $('.lottery-history-wrapper > .lottery-data-item.active + .lottery-data-item:not(.active)').attr('data-id'),
                more = $('.dataItem-more.is-acc-btn');
            if (item) {
                for (var i = 0; i < 5; i++) {
                    var element = parseFloat(item) + i;
                    $('.lottery-history-wrapper > .block-' + element).addClass('active');
                }
                if (element >= more.data('count')) {
                    more.addClass('is-on');
                }
            } else {
                this.hiddenHistory();
            }
        },

        /**
         * Hidden all order
         */
        hiddenHistory() {
            $('.lottery-history-wrapper > .lottery-data-item').removeClass('active');
            $('.dataItem-more.is-acc-btn').removeClass('is-on')
            for (var i = 1; i < 6; i++) {
                $('.lottery-history-wrapper > .block-' + i).addClass('active');
            }
        },
    });

    return $.mage.lotterySales;
});
