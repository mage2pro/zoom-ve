<?php
namespace Dfe\ZoomVe\Block\Cart;
use Magento\Framework\UrlInterface;
class Shipping extends \Magento\Checkout\Block\Cart\LayoutProcessor
{

	/**
	 * Route for customer account login page
	 */
	const ZOOM_CITY_URL = 'zoomenvios/index/city';

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfig;

	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Block\Checkout\AttributeMerger $merger,
		\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
		\Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
	) {
		$this->_scopeConfig = $scopeConfig;
		parent::__construct($merger, $countryCollection, $regionCollection);
	}

	/**
	 * Show City in Shipping Estimation
	 *
	 * @return bool
	 * @codeCoverageIgnore
	 */
	protected function isCityActive()
	{
		return true;
	}

}