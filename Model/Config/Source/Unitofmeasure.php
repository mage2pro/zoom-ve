<?php
namespace Dfe\ZoomVe\Model\Config\Source;
class Unitofmeasure extends \Dfe\ZoomVe\Model\Config\Source\Generic
{
	/**
	 * Carrier code
	 *
	 * @var string
	 */
	protected $_code = 'unit_of_measure';

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	function toOptionArray()
	{
		$unitArr = dfe_zv_cfg()->getCode($this->_code);
		$returnArr = [];
		foreach ($unitArr as $key => $val) {
			$returnArr[] = ['value' => $key, 'label' => $key];
		}
		return $returnArr;
	}
}
