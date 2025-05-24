<?php
namespace Dfe\ZoomVe\Plugin\Sales\Model\AdminOrder;
class Create
{
	/**
	 * @var \Magento\Quote\Api\CartRepositoryInterface
	 */
	protected $quoteRepository;

	/**
	 * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	 */
	function __construct(
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	) {
		$this->quoteRepository = $quoteRepository;
	}


	/**
	 * Quote saving before plugin
	 * @return $this
	 */
	function beforeSaveQuote(
		\Magento\Sales\Model\AdminOrder\Create $subject
	)
	{
		$cartId = $subject->getQuote()->getId();
		$addressInformation = $subject->getQuote()->getShippingAddress();
		$extAttributes = $addressInformation->getExtensionAttributes();
		$pickupOffice = $extAttributes->getPickupOffice();
		//$quote = $this->quoteRepository->getActive($cartId);
		if (!is_null($pickupOffice)) {
			$subject->getQuote()->setPickupOffice($pickupOffice);
		}
	}

}
