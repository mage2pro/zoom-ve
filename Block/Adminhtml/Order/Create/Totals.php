<?php
namespace Dfe\ZoomVe\Block\Adminhtml\Order\Create;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
	/**
	 * Total renderers
	 *
	 * @var array
	 */
	protected $_totalRenderers;

	/**
	 * Default renderer
	 *
	 * @var string
	 */
	protected $_defaultRenderer = \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals::class;

	/**
	 * Sales data
	 *
	 * @var \Magento\Sales\Helper\Data
	 */
	protected $_salesData = null;

	/**
	 * Sales config
	 *
	 * @var \Magento\Sales\Model\Config
	 */
	protected $_salesConfig;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Model\Session\Quote $sessionQuote
	 * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param \Magento\Sales\Helper\Data $salesData
	 * @param \Magento\Sales\Model\Config $salesConfig
	 * @param array $data
	 */
	function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Model\Session\Quote $sessionQuote,
		\Magento\Sales\Model\AdminOrder\Create $orderCreate,
		PriceCurrencyInterface $priceCurrency,
		\Magento\Sales\Helper\Data $salesData,
		\Magento\Sales\Model\Config $salesConfig,
		StoreManagerInterface $storeManager,
		array $data = []
	) {
		$this->_salesData = $salesData;
		$this->_salesConfig = $salesConfig;
		$this->_storeManager = $storeManager;
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
		$this->setId('sales_order_create_totals');
	}

	/**
	 * Get totals
	 *
	 * @return array
	 */
	function getTotals()
	{
		$this->getQuote()->collectTotals();
		if ($this->getQuote()->isVirtual()) {
			$totals = $this->getQuote()->getBillingAddress()->getTotals();
		} else {
			$totals = $this->getQuote()->getShippingAddress()->getTotals();
		}
		return $totals;
	}

	/**
	 * Get header text
	 *
	 * @return \Magento\Framework\Phrase
	 */
	function getHeaderText()
	{
		return __('Order Totals');
	}

	/**
	 * Get header css class
	 *
	 * @return string
	 */
	function getHeaderCssClass()
	{
		return 'head-money';
	}

	/**
	 * Get total renderer
	 *
	 * @param string $code
	 * @return bool|\Magento\Framework\View\Element\BlockInterface
	 */
	protected function _getTotalRenderer($code)
	{
		$blockName = $code . '_total_renderer';
		$block = $this->getLayout()->getBlock($blockName);
		if (!$block) {
			$configRenderer = $this->_salesConfig->getTotalsRenderer('quote', 'totals', $code);
			if (empty($configRenderer)) {
				$block = $this->_defaultRenderer;
			} else {
				$block = $configRenderer;
			}

			$block = $this->getLayout()->createBlock($block, $blockName);
		}
		/**
		 * Transfer totals to renderer
		 */
		$block->setTotals($this->getTotals());
		return $block;
	}

	/**
	 * Render total
	 *
	 * @param \Magento\Framework\DataObject $total
	 * @param string|null $area
	 * @param int $colspan
	 * @return mixed
	 */
	function renderTotal($total, $area = null, $colspan = 1)
	{
		return $this->_getTotalRenderer(
			$total->getCode()
		)->setTotal(
			$total
		)->setColspan(
			$colspan
		)->setRenderingArea(
			$area === null ? -1 : $area
		)->toHtml();
	}

	/**
	 * Render totals
	 *
	 * @param null $area
	 * @param int $colspan
	 * @return string
	 */
	function renderTotals($area = null, $colspan = 1)
	{
		$html = '';
		$storeId = $this->_storeManager->getStore()->getId();
		$shippingMethodTitle = '';

		foreach ($this->getTotals() as $total) {
			if ($total->getArea() != $area && $area != -1) {
				continue;
			}

			if($total->getCode() == 'shipping'){
				$zoomTitle = $this->_scopeConfig->getValue(
					'carriers/zoomenvios/name',
					ScopeInterface::SCOPE_STORE,
					$storeId
				);
				$shippingHtml = $this->renderTotal($total, $area, $colspan);
				if(array_key_exists(0, $total->getTitle()->getArguments())){
					$shippingMethodTitle = $total->getTitle()->getArguments()[0];
				}
				if(strpos($shippingMethodTitle, $zoomTitle) !== false){
					//$total->getTitle()->getText();
					$arrFrom = array("&lt;","&gt;","&quot;");
					$arrTo = array("<",">",'"');
					$html .= str_replace($arrFrom, $arrTo, $shippingHtml);
				}else{
					$html .= $shippingHtml;
				}


			}else {
				$html .= $this->renderTotal($total, $area, $colspan);
			}

		}
		return $html;
	}

	/**
	 * Check allow to send new order confirmation email
	 *
	 * @return bool
	 */
	function canSendNewOrderConfirmationEmail()
	{
		return $this->_salesData->canSendNewOrderConfirmationEmail($this->getQuote()->getStoreId());
	}

	/**
	 * Get note notification
	 *
	 * @return bool
	 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
	 */
	function getNoteNotify()
	{
		$notify = $this->getQuote()->getCustomerNoteNotify();
		if ($notify === null || $notify) {
			return true;
		}
		return false;
	}
}
