<?php
namespace Dfe\ZoomVe\Block\Adminhtml\Order\Create\Totals;
use Magento\Framework\Pricing\PriceCurrencyInterface;
class Shipping extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals
{
	/**
	 * Template
	 *
	 * @var string
	 */
	protected $_template = 'Magento_Sales::order/create/totals/shipping.phtml';

	/**
	 * Tax config
	 *
	 * @var \Magento\Tax\Model\Config
	 */
	protected $_taxConfig;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Model\Session\Quote $sessionQuote
	 * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
	 * @param \Magento\Sales\Helper\Data $salesData
	 * @param \Magento\Sales\Model\Config $salesConfig
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param \Magento\Tax\Model\Config $taxConfig
	 * @param array $data
	 */
	function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Model\Session\Quote $sessionQuote,
		\Magento\Sales\Model\AdminOrder\Create $orderCreate,
		PriceCurrencyInterface $priceCurrency,
		\Magento\Sales\Helper\Data $salesData,
		\Magento\Sales\Model\Config $salesConfig,
		\Magento\Tax\Model\Config $taxConfig,
		array $data = []
	) {
		$this->_taxConfig = $taxConfig;
		parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig, $data);
	}

	/**
	 * Check if we need display shipping include and exclude tax
	 *
	 * @return bool
	 */
	function displayBoth()
	{
		return $this->_taxConfig->displayCartShippingBoth();
	}

	/**
	 * Check if we need display shipping include tax
	 *
	 * @return bool
	 */
	function displayIncludeTax()
	{
		return $this->_taxConfig->displayCartShippingInclTax();
	}

	/**
	 * Get shipping amount include tax
	 *
	 * @return float
	 */
	function getShippingIncludeTax()
	{
		return $this->getTotal()->getShippingInclTax();
	}

	/**
	 * Get shipping amount exclude tax
	 *
	 * @return float
	 */
	function getShippingExcludeTax()
	{
		return $this->getTotal()->getValue();
	}

	/**
	 * Get label for shipping include tax
	 *
	 * @return \Magento\Framework\Phrase
	 */
	function getIncludeTaxLabel()
	{
		return __(
			'Shipping Incl. Tax (%1)',
			$this->escapeHtml($this->getQuote()->getShippingAddress()->getShippingDescription(), ['span'])
		);
	}

	/**
	 * Get label for shipping exclude tax
	 *
	 * @return \Magento\Framework\Phrase
	 */
	function getExcludeTaxLabel()
	{
		return __(
			'Shipping Excl. Tax (%1)',
			$this->escapeHtml($this->getQuote()->getShippingAddress()->getShippingDescription(), ['span'])
		);
	}
}
