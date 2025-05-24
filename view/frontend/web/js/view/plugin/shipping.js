define([
	'Magento_Checkout/js/model/quote',
	'jquery'
], function (quote, $) {
	'use strict';

	return function (Component) {
		return Component.extend({
			validateShippingInformation: function () {
				let method = quote.shippingMethod();
				let method_code = (method === null) ? "" : method.method_code,
					carrier_code = (method === null) ? "" : method.carrier_code;

				if( carrier_code == 'zoomenvios' && $('#pickup-office').val() == ""  && method_code.includes("-1")) {
					$('#office-error-msg')
						.show()
						.find('span')
						.text('Please select an Office to pick your product.');
					return false;
				}
				return this._super();
			}
		});
	}
});
