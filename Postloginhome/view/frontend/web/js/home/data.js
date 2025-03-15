/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
require([
    "jquery",
    "mage/url",
    "jquery/ui"
], function($, url){
    $(document).ready(function(){
        let customerId = $('.customer-id').attr('data-customer-id');
        $.ajax({
            url: url.build('postloginhome/index/mywishlist'),
            type: "POST",
            dataType: "json",
            data: {customerId: customerId},
            showLoader: true,
            success: function (wishlistJson) {
                $("#tab-favourites").html(wishlistJson.wishdata);
            }
        });

        $('body').on('click', '#tab_order_data', function () {
            $.ajax({
                url: url.build('postloginhome/index/myorders'),
                type: "POST",
                dataType: "json",
                showLoader: true,
                success: function(ordersJson){
                    $("#tab-order").html(ordersJson.output);
                }
            });
        });

        $('body').on('click', '#tab_quotes_data', function () {
            $.ajax({
                url: url.build('postloginhome/index/myrfq'),
                type: "POST",
                dataType: "json",
                showLoader: true,
                success: function(rfqJson) {
                    $("#tab-quotes").html(rfqJson.output);
                }
            });
        });
    });
});
