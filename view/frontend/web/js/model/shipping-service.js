define([
			'jquery', // For jQuery Added
			'Magento_Checkout/js/model/quote', // For Quote Added
			'ko',
			'Magento_Checkout/js/model/checkout-data-resolver'
		],
		function ($, quote, ko, checkoutDataResolver) {
			"use strict";
			var shippingRates = ko.observableArray([]);
			return {
				isLoading: ko.observable(false),
				/**
				 * Set shipping rates
				 *
				 * @param ratesData
				 */
				setShippingRates: function (ratesData) {
					var loggedinCustomer =1
					if (loggedinCustomer == 1) {
						var address = quote.shippingAddress();

						// you can get zipcode of current shipping address here
						var zipcode = (address.postcode);
						var city = (address.city);

						// STOP TO REMOVE ERROR MESSAGE FOR DHL IF SG

						shippingRates(ratesData);
						shippingRates.valueHasMutated();
						checkoutDataResolver.resolveShippingRates(ratesData);
						 console.log('Shipping City for logedin customer is:'+city)
					} else {
						var address = quote.shippingAddress();
						var zipcode = (address.postcode);
						var city = (address.city);
						console.log('Shipping City for guest customer  is:'+city);
					}
				},
				/**
				 * Get shipping rates
				 *
				 * @returns {*}
				 */
				getShippingRates: function () {
					return shippingRates;
				}
			};
		}
);
