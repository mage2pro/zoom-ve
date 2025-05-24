<?php
declare(strict_types=1);
namespace Dfe\ZoomVe\Model\Config\Backend;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;
class ZoomUrl extends Value
{
	/**
	 * @inheritdoc
	 */
	function beforeSave()
	{
		// phpcs:ignore Magento2.Functions.DiscouragedFunction
		$host = parse_url((string)$this->getValue(), \PHP_URL_HOST);

		if (!empty($host) && !preg_match('/(?:.+\.|^)zoomenvios\.com$/i', $host)) {
			throw new ValidatorException(__('ZoomEnvios API endpoint URL\'s must use zoomenvios.com'));
		}

		return parent::beforeSave();
	}
}
