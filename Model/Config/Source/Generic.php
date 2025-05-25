<?php
namespace Dfe\ZoomVe\Model\Config\Source;
use Dfe\ZoomVe\Helper\Config as C;
use Magento\Shipping\Model\Carrier\Source\GenericInterface;
class Generic implements GenericInterface
{
	/**
	 * @var \Dfe\ZoomVe\Helper\Config
	 */
	protected $carrierConfig;

	/**
	 * Carrier code
	 *
	 * @var string
	 */
	protected $_code = '';

	/**
	 * 2025-05-25
	 */
	function __construct(C $c)
	{
		$this->carrierConfig = $c;
	}

	/**
	 * Returns array to be used in multiselect on back-end
	 *
	 * @return array
	 */
	function toOptionArray()
	{
		$configData = $this->carrierConfig->getCode($this->_code);
		$arr = [];
		foreach ($configData as $code => $title) {
			$arr[] = ['value' => $code, 'label' => __($title)];
		}
		return $arr;
	}
}
