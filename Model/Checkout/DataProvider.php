<?php
namespace Dfe\ZoomVe\Model\Checkout;
use Dfe\ZoomVe\Helper\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Dfe\ZoomVe\Model\ResourceModel\Store\CollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart;
class DataProvider implements ConfigProviderInterface
{

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var \Dfe\ZoomVe\Model\ResourceModel\Store\CollectionFactory
	 */
	protected $storeCollectionFactory;

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * @var Config
	 */
	protected $configHelper;

	/**
	 * @var Magento\Checkout\Model\Session;
	 */
	protected $_checkoutSession;

	/**
	 *
	 * @var Magento\Checkout\Model\Cart
	 */
	protected $_cart;

	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param Config $configHelper
	 */
	function __construct(
		Config $configHelper,
		ScopeConfigInterface $scopeConfig,
		CheckoutSession $checkoutSession,
		Cart $cart
	) {
		$this->configHelper = $configHelper;
		$this->scopeConfig = $scopeConfig;
		$this->_checkoutSession = $checkoutSession;
		$this->_cart = $cart;
	}

	/**
	 * {@inheritdoc}
	 */
	function getConfig()
	{
		//$city = $this->_checkoutSession->getQuote()-getShippingAddress()->getCity();
		$city = $this->_cart->getQuote()->getShippingAddress()->getCity();
		$config = [
			'shipping' => [
				'select_office' => [
					'offices' => $this->getOffices($city)
				]
			]
		];

		return $config;
	}

	function getOffices($city = null)
	{
		$officeListStr = $this->configHelper->getCode('origin_city', [strtoupper($city),'office_code']);
		$officeListArr = explode(',', $officeListStr);
		$offices = $this->configHelper->getCode('office');
		$offices_arr = array();
		$i = 0;
		foreach ( $offices as $office_name => $office_code) {
			if(in_array($office_code, $officeListArr)){
			$offices_arr[$i]["office_code"] = $office_code;
			$offices_arr[$i]["office_name"] = $office_name;
			$i++;
			}
		}
		return \Zend_Json::encode($offices_arr);
	}
}
