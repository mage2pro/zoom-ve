define([
	'uiComponent',
	'ko',
	'jquery',
	'mage/translate',
	'Magento_Ui/js/modal/modal',
	'Magento_Checkout/js/model/quote',
	'underscore',
	'mage/url'
], function (Component, ko, $, $t, modal, quote, _, urlBuilder) {
	'use strict';
	return Component.extend({
		defaults: {
			template: 'Dfe_ZoomVe/checkout/shipping/select-office',
			actionUrl: urlBuilder.build('/zoomenvios/index/office')
		},
		isOfficeDisplayable: ko.observable(false),
		errorValidationMessage: ko.observable(false),
		selectedOffice: ko.observable(),
		initialize: function () {
			var self = this;

			quote.shippingMethod.subscribe(function () {
				let method = quote.shippingMethod();
				let method_code = (method === null) ? "" : method.method_code,
					carrier_code = (method === null) ? "" : method.carrier_code;

				if (carrier_code === 'zoomenvios'  && method_code.includes("-1")) {
					self.isOfficeDisplayable(true);
					var address = quote.shippingAddress();
					var city = (address.city);
					self.availableOffice(city);
				} else {
					self.isOfficeDisplayable(false);
					$('#officeDropdown').val("");
					self.selectedOffice("");
				}
			});


			self.selectedOffice.subscribe(() => {
				self._selectOffice();
			});

			return this._super();
		},

		_selectOffice: function () {
			let dropdownVal = $("#officeDropdown :selected").val();
			$('#pickup-office').val( dropdownVal );
			$('#office-error-msg').hide();
			if (dropdownVal != "") {
				$('#selected-office-msg')
					.show()
					.find('span')
					.text( $("#officeDropdown :selected").text() );
			} else {
				$('#selected-office-msg').hide();
			}
		},
		prepareOffieList: function(data){
		   var offices = _.map(data,
				function (value, key) {
					return {
						'value': value.office_code,
						'label': value.office_name
					};
				});
		   return offices;
		},
		availableOffice: function (city = false) {
			var self = this;
			if (city) {
				jQuery.ajax({
					url: this.actionUrl,
					type: 'POST',
					dataType: 'json',
					data: {
						city: city
					},
					success: function (data) {
						$('#officeDropdown').find('option:not(:first)').remove();
						var currPickupOffice = '';
						var pickupOffice = $('#pickup-office').val();
						$('#pickup-office').val('');
						for (const val of data) {
							if(val.office_code == pickupOffice){
							   currPickupOffice = val.office_code;
							}
							$('#officeDropdown').append($(document.createElement('option')).prop({
								value: val.office_code,
								text: val.office_name
							}));
						}
						$("#officeDropdown").val(currPickupOffice).change();
						$('#pickup-office').val(currPickupOffice);
					}
				});
			} else {
				return self.prepareOffieList($.parseJSON(window.checkoutConfig.shipping.select_office.offices));
			}

		}
	});
});