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
            console.log('recommendations');
            var produtIds = [465,468,469,466];
            $.ajax({
                url: url.build('recommend/index/recommendations'),
                type: "POST",
                dataType: "json",
                data: {productIds: produtIds}
            });
        });
    });
