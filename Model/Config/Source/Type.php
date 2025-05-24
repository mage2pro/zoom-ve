<?php
namespace Dfe\ZoomVe\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
class Type implements OptionSourceInterface
{
	/**
	 * {@inheritdoc}
	 */
	function toOptionArray()
	{
		return [
			['value' => 'UPS', 'label' => __('United Parcel Service')],
			['value' => 'UPS_XML', 'label' => __('United Parcel Service XML')]
		];
	}
}
