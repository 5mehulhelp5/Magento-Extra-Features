var config = {
    map: {
        '*': {
            'Magento_Checkout/template/summary/cart-items.html':
                'Codilar_Vendor/template/summary/cart-items.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/cart-items' : {'Codilar_Vendor/js/view/summary/cart-items-mixin':true}
        }
    }
};
