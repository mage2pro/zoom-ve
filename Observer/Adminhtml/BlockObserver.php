<?php
namespace Dfe\ZoomVe\Observer\Adminhtml;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
class BlockObserver implements ObserverInterface
{
	/**
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $_coreTemplate;

	/**
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 */
	function __construct(
		\Magento\Framework\View\Element\Template $coreTemplate
	)
	{
		$this->_coreTemplate = $coreTemplate;
	}

	function execute(EventObserver $observer)
	{
		if($observer->getElementName() == 'order_shipping_view') {
			$shippingInfoBlock = $observer->getLayout()->getBlock($observer->getElementName());
			$order = $shippingInfoBlock->getOrder();

			if (strpos($order->getShippingMethod(), "zoomenvios") === false) {
				return $this;
			}

			$officeInfo = $this->_coreTemplate
				->setPickupOffice($order->getPickupOffice())
				->setTemplate('Dfe_ZoomVe::order/view/office-info.phtml')
				->toHtml();
			$html = $observer->getTransport()->getOutput() . $officeInfo;
			$observer->getTransport()->setOutput($html);
		}
	}
}
