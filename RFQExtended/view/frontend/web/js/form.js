/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

require([
    "jquery",
    "mage/url",
    'Magento_Ui/js/modal/modal',
    "select2"
],function(
    $, url, modal
) {
    let count = 1;
    $("#count").attr('value', count);

    /* Add new Product Row */
    $(document).on("click", '#addNewProduct', function() {
        updateCount("add");
        let html = "<div class=\"productRow\">\n    <div class=\"product-container\">\n   <h3 class=\"sub-heading\">Product<\/h3>\n  <div class=\"primary\">\n <button type=\"button\" id=\"removeProductRow\" class=\"action login primary removeProductRow\">  <span>- Remove</span> </button> </div>  <div class=\"field rfq required rfq-know-manufacturer\">\n            <label class=\"label \" for=\"known_manufacturer\"><span>Manufacturer not Known<\/span><\/label>\n            <div class=\"control\">\n                <input name=\"known_manufacturer["+count+"]\"\n                       class=\"known_manufacturer\" id=\"known_manufacturer-"+count+"\" type=\"checkbox\" title=\"Manufacturer Known or not\" >\n            <\/div>\n        <\/div>\n         <fieldset class=\"fieldset rfq-product\" data-hasrequired=\"* Required Fields\">\n        <div class=\"field rfq required rfq-manufacture\">\n            <label class=\"label \" for=\"manufacturer\"><span>Manufacturer<\/span><\/label>\n            <div class=\"control\">\n                <select name=\"manufacture_name["+count+"]\" id=\"manufacturer-"+count+"\" class=\"manufacturer\" required=\"required\">\n                    <option value=\"\"> Select Manufacture <\/option>\n                                            <option value=\"34\"\n                            > Manufacture -1 <\/option>\n                                            <option value=\"35\"\n                            > Manufacture -2 <\/option>\n                                            <option value=\"36\"\n                            > Manufacture -3 <\/option>\n                                            <option value=\"37\"\n                            > Manufacture -4 <\/option>\n                                            <option value=\"38\"\n                            > Manufacture -5 <\/option>\n                                    <\/select>\n\n            <\/div>\n        <\/div>\n        <div class=\"field rfq required rfq-mfg-part\">\n            <label class=\"label \" for=\"mfg_part\"><span>MFG Part No.<\/span><\/label>\n            <div class=\"control\">\n                <input name=\"mfg_part["+count+"]\"\n                       type=\"text\" class=\"input-text mfg_part\"\n                       title=\"MFG Part No\"\n                       value=\"\"\n                       required=\"required\">\n            <\/div>\n        <\/div>\n        <div class=\"field rfq rfq-product-name\">\n            <label class=\"label \" for=\"product_name\"><span>Product Name<\/span><\/label>\n            <div class=\"control\">\n                <input name=\"product_name["+count+"]\"\n                       type=\"text\" class=\"input-text product_name\"\n                       title=\"Product Name\"\n                       value=\"\">\n            <\/div>\n        <\/div>\n        <div class=\"field rfq required rfq-annual-qty\">\n            <label class=\"label \" for=\"rfq-Annual\"><span>Annual Usage<\/span><\/label>\n            <div class=\"control\">\n                <input name=\"annual_usage["+count+"]\"\n                       type=\"number\" class=\"input-text rfq-Annual\"\n                       title=\"Annual Usage\"\n                       required=\"required\">\n            <\/div>\n        <\/div>\n        <div class=\"field rfq required rfq-req-qty qty1\">\n            <label class=\"label \" ><span>Req. Qty<\/span><\/label>\n            <div class=\"control\">\n                <input name=\"requested_qty["+count+"]\"\n                       type=\"number\" class=\"input-text rfq-Qty\"\n                       title=\"Req. Qty\"\n                       required=\"required\"'>\n            <\/div>\n        <\/div>\n    <\/fieldset>\n    <fieldset class=\"fieldset descreption-container\">\n        <div class=\"field rfq rfq-description \">\n            <label class=\"label \" for=\"rfq-description\"><span>Description<\/span><\/label>\n            <div class=\"control\">\n                <textarea name=\"description["+count+"]\" class=\"input-text rfq-description\" title=\"Description\"><\/textarea>\n            <\/div>\n        <\/div>\n    <\/fieldset>\n    <div class=\"box box-upload\">\n        <fieldset class=\"fieldset\">\n            <div class=\"field upload skus\">\n                <label class=\"label\" for=\"customer_sku_csv\"><\/label>\n                 <div class=\"control\">\n                    <div class=\"upload-container\">\n                        <div class=\"file-upload\">Choose File<\/div>\n                        <div class=\"container\">\n                            <div class=\"label-for-Sku-input\">\n                                <h3 class=\"heading\">Upload image (Optional)<\/h3>\n                                <p class=\"Sub-heading\">in jpg, png, jpeg format only<\/p>\n    <span class=\"filename\"><\/span>\n                          <input type=\"file\" data-reset=\"true\" class=\"action-upload\" name=\"attachment["+count+"]\">\n<\/div>\n                        <\/div>\n                                            <\/div>\n                <\/div>\n            <\/div>\n        <\/fieldset>\n     <\/div>\n<\/div>\n<\/div>\n";
        $(".product_request_container").append(html);
        selectRefresh(count);
        labelFixClass();
    });

    function labelFixClass() {
        $('form .field').each(function() {
            $(this).find('input, textarea, select').on('paste blur change', () => {
                var input = $(this).find('input, textarea, select');
                var label = $(this).find('.label');
                if (!$(input).val()) {
                    label.removeClass('fix');
                } else {
                    label.addClass('fix');
                }
                var label1 = $('hidden-class')
                if (!$('#pass').val()) {
                    label1.hide()
                } else {
                    label1.show()
                }
            });
            var input = $(this).find('input, textarea, select');
            var label = $(this).find('.label');
            if(input.val()!=''){
                label.addClass('fix');
            } else {
                label.removeClass('fix');
            }
        });
    }

    function selectRefresh(count) {
        $('#manufacturer-'+count).select2({
            tags: true,
            placeholder: "Select Manufacturer",
            language: {
                noResults: function () {
                    return "<?= __('No results found') ?>";
                }
            }
        });
    }

    $(document).on('click', '.known_manufacturer', function () {
        var is_checked = $(this).is(':checked');
        $("#rfqExtendedCustomForm").data('validator').resetForm();
        if (is_checked) {
            $(this).closest('.productRow').find('.rfq-name').attr('required','required');
            $(this).closest('.productRow').find('.product_name').attr('required','required');
            $(this).closest('.productRow').find('.manufacturer').removeAttr('required');
            $(this).closest('.productRow').find('.mfg_part').removeAttr('required');
            $(this).closest('.productRow').find('.rfq-manufacture').removeClass('required');
            $(this).closest('.productRow').find('.rfq-mfg-part').removeClass('required');
            $(this).closest('.productRow').find('.rfq-product-name').addClass('required');
            $(this).closest('.productRow').find('.rfq-description').addClass('required');
        } else {
            $(this).closest('.productRow').find('.rfq-name').removeAttr('required');
            $(this).closest('.productRow').find('.product_name').removeAttr('required');
            $(this).closest('.productRow').find('.manufacturer').attr('required','required');
            $(this).closest('.productRow').find('.mfg_part').attr('required','required');
            $(this).closest('.productRow').find('.rfq-manufacture').addClass('required');
            $(this).closest('.productRow').find('.rfq-mfg-part').addClass('required');
            $(this).closest('.productRow').find('.rfq-product-name').removeClass('required');
            $(this).closest('.productRow').find('.rfq-description').removeClass('required');
        }
    })


    /* Remove Product Row */
    $(document).on('click', '.removeProductRow', function () {
        $(this).closest('.productRow').remove();
        updateCount("sub");
        $('#rfq-name').change();
    });

    $(document).on('change', '#rfqExtendedCustomForm', function () {
        let cache_id = 'rfqQuoteForm';
        let test = $(this).serializeArray();
        localStorage.setItem(cache_id, JSON.stringify(test));
    });

    $(document).ready(function() {
        selectRefresh(count);
        let cache_id = 'rfqQuoteForm';
        let data = JSON.parse(localStorage.getItem(cache_id));
        let final_data = null;
        if (data != null && data.length > 0) {
            final_data =  objectifyForm(data);
        }

        if (final_data != null) {
            if (final_data.count > 1)
            {
                for (var i = 1; i < final_data.count; i++)
                {
                    $("#addNewProduct").click();
                }
            }

            $('#rfqExtendedCustomForm .input-text').each(function () {
                if (final_data['' + this.name + '']) {
                    this.value = final_data['' + this.name + ''];
                    $(this).parents('.field').find('.label').addClass('fix');
                }
            });

            $('#rfqExtendedCustomForm .known_manufacturer').each(function () {
                if (final_data['' + this.name + '']) {
                    $('#'+this.id).prop('checked',true)
                }
            });

            $('#rfqExtendedCustomForm .manufacturer').each(function () {
                if (final_data['' + this.name + '']) {
                    $('#'+this.id).val(final_data['' + this.name + '']).trigger('change');
                }
            });
        }
    });

    function objectifyForm(formArray) {
        var returnArray = {};
        for (var i = 0; i < formArray.length; i++){
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }


    /* Update the Count Hidden Input */
    function updateCount(condition) {
        if(condition === "add") {
            count++;
        } else if (condition === "sub") {
            count--;
        }
        $("#count").attr('value', count);
    }

    /* Submit Button Click */
    $(document).on('click', '#submitRFQCustomForm', function () {
        let productNameDescArray = document.getElementsByName('mfg_part['+count+']');
        let qtyArray = document.getElementsByName('requested_qty['+count+']');

        if(!(productNameDescArray.length && qtyArray.length)) {
            $("#addNewProduct").click();
        }

        let customer_id = $("#customer_id").val();
        if (!customer_id) {
            var self = this;

            var modaloption = {
                type: 'popup',
                modalClass: 'modal-popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                title: 'Create an Account',
                buttons: []
            };
            var callforoption = modal(modaloption, $('.guest-customer-confirm-popup'));
            $('.guest-customer-confirm-popup').modal('openModal');

            $('body').on('click', '#guest_place_order', function () {
                var ajaxUrl = url.build('quick-quote/ajax/guestToCustomer');
                var customerEmail = $('#rfq-customer-email').val();
                var customerFirstName = $('#rfq-customer-firstname').val();
                var customerLastName = $('#rfq-customer-lastname').val();
                var passWord = $('#guest_pass').val();
                var confirmPassWord = $('#confirm_pass').val();

                $('#wrong_pass_alert').html('');
                if (customerEmail == '' || customerFirstName == '' || customerLastName == '') {
                    $('#wrong_pass_alert').html('Please fill name and email');
                    return false;
                }

                if (passWord!='' && confirmPassWord!='' && passWord==confirmPassWord) {
                    if (passWord.length>7) {
                        $.ajax({
                            showLoader: true,
                            url: ajaxUrl,
                            data: {password:passWord,customer_email:customerEmail,customer_first_name:customerFirstName,customer_last_name:customerLastName},
                            type: "POST",
                        }).done(function (data) {
                            if (!data.is_exist) {
                                if (data.is_new_created) {
                                    location.reload();
                                }
                            } else {
                                $('#wrong_pass_alert').html('Customer with this email already exist. Please login to submit this RFQ Form');
                                return false;
                            }
                        });
                    } else {
                        $('#wrong_pass_alert').html('Password should be minimum 8 characters');
                        return false;
                    }
                } else {
                    $('#wrong_pass_alert').html('Password not matched');
                    return false;
                }
            });
        } else {
            $("#rfqExtendedCustomForm").submit();
            if ($('#rfqExtendedCustomForm').valid()) {
                localStorage.removeItem('rfqQuoteForm');
            }
        }
    });
});
