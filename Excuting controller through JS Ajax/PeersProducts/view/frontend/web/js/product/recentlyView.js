/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
require(
    [
        "jquery",
        "mage/url",
        "jquery/ui"
    ], function($, url){
        $(document).ready(function() {
            console.log('recently');
            let produtId = $("#product_addtocart_form input[name='product']").val();
            console.log(produtId);
            $.ajax({
                url: url.build('recently/product/update'),
                type: "POST",
                dataType: "json",
                data: {productId: produtId}
            });
        });
    });
