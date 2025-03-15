/**
 * @package     EGC Supply
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */
define([
    "uiComponent",
    "dataServicesBase",
    "jquery",
    "Magento_Catalog/js/price-utils",
], function (Component, ds, $, priceUnits) {
    "use strict"
    return Component.extend({
        defaults: {
            template:
                "Magento_ProductRecommendationsLayout/recommendations.html",
            recs: [],
        },
        initialize: function (config) {
            this._super(config)
            this.pagePlacement = config.pagePlacement
            this.placeholderUrl = config.placeholderUrl
            this.priceFormat = config.priceFormat
            this.priceUnits = priceUnits
            this.currencyConfiguration = config.currencyConfiguration
            this.alternateEnvironmentId = config.alternateEnvironmentId
            return this
        },
        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this._super().observe(["recs"])
        },

        //Helper function to add addToCart button & convert currency
        /**
         *
         * @param {@} response is type Array.
         * @returns type Array.
         */
        processResponse(response) {
            const units = []
            if (!response.length || response[0].unitId === undefined) {
                return units
            }
            for (let i = 0; i < response.length; i++) {
                response[i].products = response[i].products.slice(
                    0,
                    response[i].displayNumber,
                )
                $(".products-grid").html('');
                var sliderProductIds = [];
                for (let j = 0; j < response[i].products.length; j++) {
                    if (response[i].products[j].productId) {
                        sliderProductIds.push(response[i].products[j].productId);
                        const form_key = $.cookie("form_key")
                        const url = this.createAddToCartUrl(
                            response[i].products[j].productId,
                        )
                        const postUenc = this.encodeUenc(url)
                        const addToCart = {form_key, url, postUenc}
                        response[i].products[j].addToCart = addToCart
                    }

                    if (
                        this.currencyConfiguration &&
                        response[i].products[j].currency !==
                        this.currencyConfiguration.currency
                    ) {
                        if (response[i].products[j].prices === null) {
                            response[i].products[j].prices = {
                                minimum: {final: null},
                            }
                        } else {
                            response[i].products[j].prices.minimum.final =
                                response[i].products[j].prices &&
                                response[i].products[j].prices.minimum &&
                                response[i].products[j].prices.minimum.final
                                    ? this.convertPrice(
                                    response[i].products[j].prices.minimum
                                        .final,
                                    )
                                    : null
                        }
                        response[i].products[j].currency =
                            this.currencyConfiguration.currency
                    }
                }
                units.push(response[i])
                
                console.log(sliderProductIds);
                $.ajax({
                    url: BASE_URL+'recommendation/product/slider',
                    type: "POST",
                    dataType: "json",
                    data: {productIds: sliderProductIds,sliderLabel:response[i].unitName},
                    showLoader: true,
                    success: function(resultJson) {
                        $(".products-recommendation-grid").append(resultJson.output);
                        renderKoTemplate(resultJson.behaviour);
                        function renderKoTemplate(data) {
                            $('.recommendation-slider').replaceWith(data);
                            $('.page-wrapper').trigger('contentUpdated');
                        }
                    }
                });
            }
            units.sort((a, b) => a.displayOrder - b.displayOrder)
            return units
        },


        loadJsAfterKoRender: function (self, unit) {
            const renderEvent = new CustomEvent("render", {detail: unit})
            document.dispatchEvent(renderEvent)
        },

        convertPrice: function (price) {
            return parseFloat(price * this.currencyConfiguration.rate)
        },

        createAddToCartUrl(productId) {
            const currentLocationUENC = encodeURIComponent(
                this.encodeUenc(BASE_URL),
            )
            const postUrl =
                BASE_URL +
                "checkout/cart/add/uenc/" +
                currentLocationUENC +
                "/product/" +
                productId
            return postUrl
        },

        encodeUenc: function (value) {
            const regex = /=/gi
            return btoa(value).replace(regex, ",")
        },

        productRecommendationSlickInit: function () {
            $(document).ready(function () {
                $('.product-slider').each(function () {
                    $('.product-slider').not('.slick-initialized').slick({
                        dots: false,
                        arrow: true,
                        infinite: false,
                        speed: 300,
                        slidesToShow: 4.3,
                        slidesToScroll: 1,
                        responsive: [
                            {
                                breakpoint: 1199,
                                settings: {
                                    slidesToShow: 3.3,
                                    slidesToScroll: 2
                                }
                            },
                            {
                                breakpoint: 991,
                                settings: {
                                    slidesToShow: 2.3,
                                    slidesToScroll: 2
                                }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 2.3,
                                    slidesToScroll: 2
                                }
                            },
                            {
                                breakpoint: 600,
                                settings: {
                                    slidesToShow: 1.2,
                                    slidesToScroll: 1
                                }
                            }
                        ]

                    });
                });
            });
            $('#loader-example').trigger('processStart');
        }
    })
})
