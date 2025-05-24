<?php
namespace Dfe\ZoomVe\Model\Config\Source;
class Freemethod extends \Dfe\ZoomVe\Model\Config\Source\Method
{
	/**
	 * {@inheritdoc}
	 */
	function toOptionArray()
	{
		$arr = parent::toOptionArray();
		array_unshift($arr, ['value' => '', 'label' => __('None')]);
		return $arr;
	}
}
