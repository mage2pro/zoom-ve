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
	function getConfig() {return ['shipping' => ['select_office' => [
		'offices' => $this->getOffices($this->_cart->getQuote()->getShippingAddress()->getCity())
	]]];}

	/**
	 * 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
	 * `getOficinas`:
	 * https://documenter.getpostman.com/view/6789630/S1Zz6V2v#18546b41-9e98-4c4a-b782-c9b10c9d33a6
	 * @used-by self::getConfig()
	 * @used-by \Dfe\ZoomVe\Controller\Index\Office::execute()
	 */
	function getOffices(?string $city = '') {
		$officeListStr = $this->configHelper->getCode('origin_city', [
			# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
			# 1) «strtoupper(): Passing null to parameter #1 ($string) of type string is deprecated
			# in vendor/mage2pro/zoom-ve/Model/Checkout/DataProvider.php on line 68»:
			# https://github.com/mage2pro/zoom-ve/issues/4
			# 2) "`strtoupper` does not work correctly with the Spanish language (e.g.: «SAN CRISTóBAL»)":
			# https://github.com/mage2pro/zoom-ve/issues/6
			mb_strtoupper((string)$city),'office_code'
		]);
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
