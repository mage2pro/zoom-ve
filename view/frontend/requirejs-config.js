var config = {
	map: {
	   '*': {
		   'Magento_Checkout/js/model/cart/totals-processor/default': 'Dfe_ZoomVe/js/model/cart/totals-processor/default',
		   'Magento_Checkout/js/model/shipping-save-processor/default': 'Dfe_ZoomVe/js/model/shipping-save-processor/default',
		   'Magento_Checkout/js/model/shipping-save-processor/payload-extender': 'Dfe_ZoomVe/js/model/shipping-save-processor/payload-extender',
		   'Magento_Checkout/js/action/set-shipping-information': 'Dfe_ZoomVe/js/action/set-shipping-information'
	   }
	}
	,
	config: {
		mixins: {
			'Magento_Checkout/js/view/shipping': {
				'Dfe_ZoomVe/js/view/plugin/shipping': true
			}
		}
	}
};
