define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/validation'
], function ($, modal, url, messageList) {
    "use strict";

    $(document).ready(function () {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Sync Scheme',
            buttons: []
        };

        var popupModal = $('#sync-scheme-popup').modal(options);
        var form = $('#sync-scheme-form');

        $('.sync-scheme').click(function () {
            popupModal.modal('openModal');
        });

        // Reset the form when the modal is closed (manual or programmatic)
        // $('#sync-scheme-popup').on('modalclosed', function () {
        //     form[0].reset();
        //     // form.validate().destroy();
        // });

        $('#sync-scheme-form').submit(function (e) {
            e.preventDefault();

            // Validate form before submission
            if (!form.valid()) {
                return;
            }

            var enrollmentNo = $('#enrollment_no').val();

            $.ajax({
                url: url.build('scheme/sync/enrollment'),
                type: 'POST',
                data: { enrollment_no: enrollmentNo },
                showLoader: true,
                success: function (response) {
                    if (response.status) {
                        messageList.addSuccessMessage({ message: response.message });
                    } else {
                        messageList.addErrorMessage({ message: response.message });
                    }
                    popupModal.modal('closeModal');
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                },
                error: function () {
                    messageList.addErrorMessage({ message: 'Something went wrong. Please try again.' });
                    popupModal.modal('closeModal');
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }
            });
        });
    });
});
