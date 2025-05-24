<?php
namespace Dfe\ZoomVe\Observer;
use Magento\Framework\Event\ObserverInterface;
class DataAssignObserver implements ObserverInterface
{
	function execute(\Magento\Framework\Event\Observer $observer)
	{
		$quote = $observer->getQuote();
		$order = $observer->getOrder();

		if ($quote->getPickupOffice()) {
			$order->setPickupOffice($quote->getPickupOffice());
		}
		return $this;
	}
}
