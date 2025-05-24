<?php
namespace Dfe\ZoomVe\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements UpgradeSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();
		if (version_compare($context->getVersion(), '1.1.2') < 0) {
			//add tax_class_id for sales_order_table table
			$setup->getConnection()->addColumn(
				$setup->getTable('quote'),
				'mailbox_account',
				[
					'type' => 'text',
					'nullable' => true,
					'comment' => 'Mailbox Account',
				]
			);

			$setup->getConnection()->addColumn(
				$setup->getTable('quote'),
				'pickup_office',
				[
					'type' => 'text',
					'nullable' => true,
					'comment' => 'Pickup Office',
				]
			);

			$setup->getConnection()->addColumn(
				$setup->getTable('sales_order'),
				'mailbox_account',
				[
					'type' => 'text',
					'nullable' => true,
					'comment' => 'Mailbox Account',
				]
			);

			$setup->getConnection()->addColumn(
				$setup->getTable('sales_order'),
				'pickup_office',
				[
					'type' => 'text',
					'nullable' => true,
					'comment' => 'Pickup Office',
				]
			);

		}
		$setup->endSetup();
	}

}
