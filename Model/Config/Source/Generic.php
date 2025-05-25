<?php
namespace Dfe\ZoomVe\Model\Config\Source;
use Magento\Shipping\Model\Carrier\Source\GenericInterface;
class Generic implements GenericInterface {
	/**
	 * @return array
	 */
	function toOptionArray() {
		$configData = dfe_zv_cfg()->getCode($this->_code);
		$arr = [];
		foreach ($configData as $code => $title) {
			$arr[] = ['value' => $code, 'label' => __($title)];
		}
		return $arr;
	}

	/**
	 * @var string
	 */
	protected $_code = '';
}