/**
 * @package     Codilar Technologies
 * @author      Prajwal Joshi
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
require([
    'jquery',
], function($){
    var collectSpan = $('#collect_span');
    $(document).on('click', '#import_category', function() {
        $('.sign').hide();
        if ($("#category_general_category_file").val().split('.').pop().toLowerCase() === 'csv') {
            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var results = [];
                    var rows = e.target.result.split("\n");

                    // To check whether csv contains the proper data in required format
                    var content = rows[0];
                    var values = content.split(',');

                    if (!(values.length === 3 && values[0].trim() === 'category_id' && values[1].trim() === 'image_url' && values[2].trim() === 'store_id')) {
                        collectSpan.find('.fail').show();
                        $('#collect_message_span').text('Csv File is not in proper format, Download Sample for proper format');
                        return '';
                    }

                    for (var i = 1; i < rows.length; i++) {
                        var cells = rows[i].split(",");
                        if (cells.length > 1) {
                            var data = {};
                            data.category_id = cells[0];
                            data.image_url = cells[1];
                            data.store_id = cells[2];
                            results.push(data);
                        }
                    }
                    console.log(JSON.stringify(results));
                    AjaxCall(JSON.stringify(results));
                };
                reader.readAsText($("#category_general_category_file")[0].files[0]);
            } else {
                collectSpan.find('.fail').show();
                $('#collect_message_span').text('Upload Csv File');
            }
        } else {
            collectSpan.find('.fail').show();
            $('#collect_message_span').text('Upload Csv File');
        }
    });


    function AjaxCall(data)
    {
        let url = $('.ajax-url').val();
        new Ajax.Request(url, {
            method: 'post',
            parameters: {data:data},
            loaderArea:     false,
            asynchronous:   true,
            onCreate: function() {
                collectSpan.find('.sign').hide();
                collectSpan.find('.processing').show();
                $('#collect_message_span').text('');
            },
            onSuccess: function(response) {
                collectSpan.find('.sign').hide();

                var resultText = '';
                if (response.status > 200) {
                    resultText = response.statusText;
                } else {
                    resultText = 'Imported Successfully';
                    collectSpan.find('.success').show();
                }
                $('#collect_message_span').text(resultText);

                var json = response.responseJSON;
                if (typeof json.time != 'undefined') {
                    $('#row_codilar_extrafeature_general_collect_time').find('.value .time').text(json.time);
                }
            }
        });
    }
});
