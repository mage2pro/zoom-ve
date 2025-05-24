<?php
namespace Dfe\ZoomVe\Helper;
use Dfe\ZoomVe\Helper\Config;
use \Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	/**
	 * @var Config
	 */
	protected $configHelper;


	/**
	 * @param Config $configHelper
	 * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
	 */
	function __construct(
		Context $context,
		Config $configHelper,
		\Magento\Sales\Api\Data\OrderInterface $orderInterface
	) {
		$this->configHelper = $configHelper;
		$this->orderInterface  = $orderInterface;
		parent::__construct($context);
	}


	/**
	 * Return office name by code
	 *
	 * @param $officeCode
	 * @return string|null
	 */
	function getOfficeNameByCode($officeCode): ?string
	{
		$offices = array_flip($this->configHelper->getCode('office'));
		return $offices[$officeCode];
	}

	/**
	 * @return \Magento\Sales\Api\Data\OrderInterface
	 */
	function getOrder()
	{
		return $this->orderInterface;
	}

	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getCreatInvoice($storeId)
	{
		return $this->scopeConfig->isSetFlag(
			'carriers/zoomenvios/createinvoice',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getCreatShipment($storeId)
	{
		return $this->scopeConfig->isSetFlag(
			'carriers/zoomenvios/createshipment',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getName($storeId)
	{
		return $this->scopeConfig->getValue(
			'carriers/zoomenvios/name',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getTitle($storeId)
	{
		return $this->scopeConfig->getValue(
			'carriers/zoomenvios/title',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getError($storeId)
	{
		return $this->scopeConfig->getValue(
			'carriers/zoomenvios/specificerrmsg',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}
	/**
	 * @param $storeId
	 * @return mixed
	 */
	function getPreSelect($storeId)
	{
		return $this->scopeConfig->isSetFlag(
			'carriers/adminshippingmethod/pre_select',
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}
}
