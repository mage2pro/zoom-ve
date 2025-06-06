<?php
namespace Dfe\ZoomVe\Block\Adminhtml\Order\Create\Shipping\Method;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Dfe\ZoomVe\Helper\Config;
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
	/**
	 * Shipping rates
	 *
	 * @var array
	 */
	protected $_rates;

	/**
	 * Tax data
	 *
	 * @var \Magento\Tax\Helper\Data
	 */
	protected $_taxData = null;

	/**
	 * @var PriceCurrencyInterface
	 */
	protected $priceCurrency;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Model\Session\Quote $sessionQuote
	 * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param \Magento\Tax\Helper\Data $taxData
	 * @param array $data
	 */
	function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Model\Session\Quote $sessionQuote,
		\Magento\Sales\Model\AdminOrder\Create $orderCreate,
		PriceCurrencyInterface $priceCurrency,
		\Magento\Tax\Helper\Data $taxData,
		Config $configHelper,
		array $data = []
	) {
		$this->_taxData = $taxData;
		$data['taxHelper'] = $this->_taxData;
		$this->configHelper = $configHelper;
		parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setId('sales_order_create_shipping_method_form');
	}

	/**
	 * Retrieve quote shipping address model
	 *
	 * @return \Magento\Quote\Model\Quote\Address
	 */
	function getAddress()
	{
		return $this->getQuote()->getShippingAddress();
	}

	/**
	 * Retrieve array of shipping rates groups
	 *
	 * @return array
	 */
	function getShippingRates()
	{
		if (empty($this->_rates)) {
			$this->_rates = $this->getAddress()->getGroupedAllShippingRates();
		}
		return $this->_rates;
	}

	/**
	 * Retrieve carrier name from store configuration
	 *
	 * @param string $carrierCode
	 * @return string
	 */
	function getCarrierName($carrierCode)
	{
		if ($name = $this->_scopeConfig->getValue(
			'carriers/' . $carrierCode . '/title',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->getStore()->getId()
		)
		) {
			return $name;
		}
		return $carrierCode;
	}

	/**
	 * Retrieve current selected shipping method
	 *
	 * @return string
	 */
	function getShippingMethod()
	{
		return $this->getAddress()->getShippingMethod();
	}

	/**
	 * Check activity of method by code
	 *
	 * @param string $code
	 * @return bool
	 */
	function isMethodActive($code)
	{
		return $code === $this->getShippingMethod();
	}

	/**
	 * Retrieve rate of active shipping method
	 *
	 * @return \Magento\Quote\Model\Quote\Address\Rate|false
	 */
	function getActiveMethodRate()
	{
		$rates = $this->getShippingRates();
		if (is_array($rates)) {
			foreach ($rates as $group) {
				foreach ($group as $rate) {
					if ($rate->getCode() == $this->getShippingMethod()) {
						return $rate;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Get rate request
	 *
	 * @return mixed
	 */
	function getIsRateRequest()
	{
		return $this->getRequest()->getParam('collect_shipping_rates');
	}

	/**
	 * Get shipping price
	 *
	 * @param float $price
	 * @param bool $flag
	 * @return float
	 */
	function getShippingPrice($price, $flag)
	{
		return $this->priceCurrency->convertAndFormat(
			$this->_taxData->getShippingPrice(
				$price,
				$flag,
				$this->getAddress(),
				null,
				$this->getAddress()->getQuote()->getStore()
			),
			true,
			PriceCurrencyInterface::DEFAULT_PRECISION,
			$this->getQuote()->getStore()
		);
	}
	/**
	 * Get Offices
	 *
	 * @return mixed
	 */
	function getOffices()
	{
		$offices = $this->configHelper->getCode('office');
		$options = array();
		$i = 0;
		foreach ( $offices as $office_name => $office_code) {
			$options[$i]["value"] = $office_code;
			$options[$i]["label"] = $office_name;
			$i++;
		}
		return $options;
	}
}
