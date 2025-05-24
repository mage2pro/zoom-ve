define([], function () {
	'use strict';

	return function (payload) {
		payload.addressInformation['extension_attributes'] =
			payload.addressInformation.extension_attributes;

		return payload;
	};
});
