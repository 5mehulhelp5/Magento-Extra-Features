require([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "slick",
    "mage/url",
    "jquery/ui"
], function($, modal, slick, url){
    var options = {
        type: 'popup',
        responsive: true,
        modalClass: 'create-new-wishlist-modal',
        innerScroll: true,
        buttons: []
    };

    var popup = modal(options, $('#create-wishlist-block-add-all'));
    $(".create-new-wishlist").on('click', function () {
        $("#create-wishlist-block-add-all").modal("openModal");
    });
    $("#action_close_all").on('click', function (){
        $("#create-wishlist-block-add-all").modal("closeModal");
    });

    $(document).ready(function(){
        var defaultwishlistId = $('#wishlists-select li button:first').attr('id');
        var popup = modal(options, $('#create-wishlist-block-add-all'));
        $(".create-new-wishlist").on('click', function () {
            $("#create-wishlist-block-add-all").modal("openModal");
        });
        $("#action_close_all").on('click', function (){
            $("#create-wishlist-block-add-all").modal("closeModal");
        });
        let currentUrl = window.location.href;
        if(!currentUrl.includes('wishlist/index/index'))
        {
            $('.return_url').val(currentUrl);
        }

        $('body').on('click', '#wishlists-select li button', function () {
            $('#wishlists-select li button').removeClass("active");
            $(this).addClass("active");
            var wishlistId = this.id;
            var wishlistName = this.title;
            if(wishlistId !== defaultwishlistId){
                $('.wishlist-delete-form').removeClass('default-wishlist-delete-form');
            }
            else {
                $('.wishlist-delete-form').addClass('default-wishlist-delete-form');
            }
            $.ajax({
                url: url.build('postloginhome/index/mainwishlist'),
                type: "POST",
                dataType: "json",
                data: { wishlistId: wishlistId },
                showLoader: true,
                success: function(wishlistJson){
                    $("#customer_wishlist_container").html(wishlistJson.wishdata);
                    $('.show-more-links').map((key, element) => {
                        let url = $(element).attr('href') +'wishlist_id/'+ wishlistId;
                        $(element).attr("href", url)
                    });
                    $('#wishlist-delete-form').attr('action', url.build('wishlist/index/deletewishlist/wishlist_id/') + wishlistId);
                    var action = JSON.parse($('.edit').attr('data-wishlist-edit'))
                    action.name = wishlistName;
                    action.url = url.build('wishlist/index/editwishlist/wishlist_id/') + wishlistId;
                    $('.edit').attr('data-wishlist-edit',JSON.stringify(action));
                }
            });
        });

        $('body').on('click', '#customer_wishlist_container .remove_btn', function () {
            var removeBtnIdArr = this.id.split("remove_btn_");
            var removeBtnId = removeBtnIdArr[1];
            $.ajax({
                url: url.build('postloginhome/index/removewishlistitem'),
                type: "POST",
                dataType: "json",
                data: { item: removeBtnId },
                showLoader: true,
                success: function(wishlistJson){
                    if(wishlistJson.deleted==1){
                        $("#wishlist_item_"+removeBtnId).remove();
                        var activeBtnHtml = $('#wishlists-select li button.active').html();
                        var matchesInt = activeBtnHtml.match(/(\d+)/);
                        var activeBtnHtmlNew = activeBtnHtml.replace(/\d/g, matchesInt[0]-1);
                        if(matchesInt) {
                            $('#wishlists-select li button.active').html(activeBtnHtmlNew);
                        }
                    }
                },
                error: function()
                {
                    console.log('wishlist item not deleted');
                }
            });

        });

        $('body').on('click', '#dropdown-toggle', function () {
            let toogleCont = $(this).parents('.customer-wishlist-main-data').find(".toggle-content");
            $(".toggle-content").not(toogleCont).hide();
            toogleCont.toggle();
        });

        var popupDel = modal(options, $('#modal-content-remove-wishlist'));
        $('#btn-wishlist-delete-form').on('click', function () {
            popupDel.setTitle('Remove Wishlist');
            popupDel.openModal();
        });
        $('body').on('click', '#ok_remove', function(e) {
            e.preventDefault();
            $('#wishlist-delete-form').submit();
        });
        $("#cancel").on('click', function (e) {
            e.preventDefault();
            popupDel.closeModal();
        });

        $("#searchInput").keyup(function(){
            let searchQuery = $(this).val().toLowerCase();

            $(".customer-wishlist-main-data").each((index, el) => {
                let flag =  false;
                $(el).find('.name-search').each((index, el) => {
                    let toValidate = $(el).text().toLowerCase();
                    if (toValidate.includes(searchQuery)) {
                        flag = true;
                    }
                });
                $(el).css("display", flag ? "" : "none");
            });
            $(".search-message").hide();
            if (!$(".customer-wishlist-main-data:not(:hidden)").length) {
                $(".search-message").show();
            }
        })

        var childrenWidth = 0;
        $('.wishlist-select-items .item').each(function () {
            childrenWidth += $(this).width();
        });
        // outerContainerWidth = 700;
        $('.wishlist-select-items').not(".slick-initialized").slick({
            dots: false,
            arrows: true,
            infinite: false,
            slidesToShow:4,
            slidesToScroll: 1,
            autoplay: false,
            variableWidth:true,
            responsive: [
                {
                    breakpoint: 641,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 481,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 361,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });

        $(window).load(function(){
            $('.cms-home .postloginhome .customer-wishlist-main-data:lt(6)').show();
        });
    });
});
