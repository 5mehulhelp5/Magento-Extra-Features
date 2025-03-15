/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
require([
    "jquery",
    "mage/url",
], function($, url) {
    $(document).ready(function () {
        $('body').on('click', '#quote-state li button', function () {
            $('#quote-state li button').removeClass("active");
            $(this).addClass("active");
            var state = this.id;
            console.log(state);
            $.ajax({
                url: url.build('quick-quote/quote/view'),
                type: "POST",
                dataType: "json",
                data: {state: state},
                showLoader: true,
                success: function (myquoteJson) {
                    $("#my-quotes-content").html(myquoteJson.quotedata);
                }
            });
        });
    });
});
