<?php
namespace Dfe\ZoomVe\Model\Config\Source;
class OriginShipment extends \Dfe\ZoomVe\Model\Config\Source\Generic
{
	/**
	 * Carrier code
	 *
	 * @var string
	 */
	protected $_code = 'originShipment';

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	function toOptionArray()
	{
		$orShipArr = $this->carrierConfig->getCode($this->_code);
		$returnArr = [];
		foreach ($orShipArr as $key => $val) {
			$returnArr[] = ['value' => $key, 'label' => $key];
		}
		return $returnArr;
	}
}
