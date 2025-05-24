<?php
namespace Dfe\ZoomVe\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements InstallSchemaInterface
{

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		$installer->getConnection()->addColumn(
			$installer->getTable('quote'),
			'mailbox_account',
			[
				'type' => 'text',
				'nullable' => true,
				'comment' => 'Mailbox Account',
			]
		);

		$installer->getConnection()->addColumn(
			$installer->getTable('quote'),
			'pickup_office',
			[
				'type' => 'text',
				'nullable' => true,
				'comment' => 'Pickup Office',
			]
		);

		$installer->getConnection()->addColumn(
			$installer->getTable('sales_order'),
			'mailbox_account',
			[
				'type' => 'text',
				'nullable' => true,
				'comment' => 'Mailbox Account',
			]
		);

		$installer->getConnection()->addColumn(
			$installer->getTable('sales_order'),
			'pickup_office',
			[
				'type' => 'text',
				'nullable' => true,
				'comment' => 'Pickup Office',
			]
		);

		$setup->endSetup();
	}
}
