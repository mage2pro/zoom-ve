<?php
namespace Dfe\ZoomVe\Plugin\Checkout\Model;
class ShippingInformationManagement
{
	protected $quoteRepository;

	function __construct(
		\Magento\Quote\Model\QuoteRepository $quoteRepository
	) {
		$this->quoteRepository = $quoteRepository;
	}

	/**
	 * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
	 * @param $cartId
	 * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
	 */
	function beforeSaveAddressInformation(
		\Magento\Checkout\Model\ShippingInformationManagement $subject,
		$cartId,
		\Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
	) {
		$extAttributes = $addressInformation->getExtensionAttributes();
		$pickupOffice = $extAttributes->getPickupOffice();
		$quote = $this->quoteRepository->getActive($cartId);
		$quote->setPickupOffice($pickupOffice);
	}
}
