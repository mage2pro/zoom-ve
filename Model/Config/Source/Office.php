<?php
namespace Dfe\ZoomVe\Model\Config\Source;
class Office extends \Dfe\ZoomVe\Model\Config\Source\Generic
{
	/**
	 * Carrier code
	 *
	 * @var string
	 */
	protected $_code = 'office';

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	function toOptionArray()
	{
		$orCityArr = $this->carrierConfig->getCode($this->_code);
		$returnArr = [];
		foreach ($orCityArr as $key => $val) {
			$returnArr[] = ['value' => $key, 'label' => $key];
		}
		return $returnArr;
	}
}
