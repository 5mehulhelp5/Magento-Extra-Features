define(['jquery', 'Magento_Customer/js/customer-data'], function($, customerData) {
    'use strict';

    return function (config, element) {
        var productId = $(element).data('id');
        var wishlist = customerData.get('wishlist')();
        var isInWishlist = false;

        wishlist.items.every(function(item) {
            if (item.product_id == productId) {
                isInWishlist = true;
                return false; // Exit loop early if found
            }
            return true;
        });

        if (isInWishlist) {
            $(element).addClass('added-to-wishlist');
        }
    };
});
