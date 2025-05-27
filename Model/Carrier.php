<?php
namespace Dfe\ZoomVe\Model;
use Dfe\ZoomVe\Block\System\Config\Form\Field\Locations;
use Dfe\ZoomVe\Helper\Config;
use Dfe\ZoomVe\OriginCityLocator as OCL;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;
use Magento\Shipping\Model\Shipment\Request as Shipment;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory as TrackStatusFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Zend_Http_Client;
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{

	const CODE = 'zoomenvios';
	const DEFAULT_ORIGIN_CITY = 'SAN CARLOS';
	const DELIVERY_CONFIRMATION_SHIPMENT = 1;
	const DELIVERY_CONFIRMATION_PACKAGE = 2;

	protected $_code = self::CODE;

		/**
	 * Rate request data
	 *
	 * @var RateRequest
	 */
	protected $_request;

	/**
	 * Rate result data
	 *
	 * @var Result
	 */
	protected $_result;

	/** @var PriceCurrencyInterface $priceCurrency */
	protected $_priceCurrency;

	/**
	 * Base currency rate
	 *
	 * @var float
	 */
	protected $_baseCurrencyRate;

	/**
	 * Xml access request
	 *
	 * @var string
	 */
	protected $_xmlAccessRequest;

	/**
	 * Default cgi gateway url
	 *
	 * @var string
	 */
	protected $_defaultGatewayUrl = 'https://sandbox.zoom.red/baaszoom/public/canguroazul/CalcularTarifa';

	/**
	 * Test urls for shipment
	 *
	 * @var array
	 */
	protected $_defaultUrls = [
		'ShipConfirm' => 'https://sandbox.zoom.red/baaszoom/public/canguroazul/ShipConfirm',
		'ShipAccept' => 'https://sandbox.zoom.red/baaszoom/public/canguroazul/ShipAccept',
	];

	/**
	 * Live urls for shipment
	 *
	 * @var array
	 */
	protected $_liveUrls = [
		'ShipConfirm' => 'https://sandbox.zoom.red/baaszoom/public/guiaelectronica/createShipment',
		'ShipAccept' => 'https://sandbox.zoom.red/baaszoom/public/guiaelectronica/createShipment',
	];

	/**
	 * @var FormatInterface
	 */
	protected $_localeFormat;

	/**
	 * @var LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var Config
	 */
	protected $configHelper;


	/**
	 * @var AsyncClientInterface
	 */
	private $asyncHttpClient;

	/**
	 * @var ProxyDeferredFactory
	 */
	private $deferredProxyFactory;

	/**
	 * @param ScopeConfigInterface $scopeConfig
	 * @param RateErrorFactory $rateErrorFactory
	 * @param LoggerInterface $logger
	 * @param Security $xmlSecurity
	 * @param ElementFactory $xmlElFactory
	 * @param RateFactory $rateFactory
	 * @param RateMethodFactory $rateMethodFactory
	 * @param TrackFactory $trackFactory
	 * @param TrackErrorFactory $trackErrorFactory
	 * @param TrackStatusFactory $trackStatusFactory
	 * @param RegionFactory $regionFactory
	 * @param CountryFactory $countryFactory
	 * @param CurrencyFactory $currencyFactory
	 * @param PriceCurrencyInterface $priceCurrency
	 * @param Data $directoryData
	 * @param StockRegistryInterface $stockRegistry
	 * @param FormatInterface $localeFormat
	 * @param Config $configHelper
	 * @param ClientFactory $httpClientFactory
	 * @param StoreManagerInterface $storeManager
	 * @param array $data
	 * @param AsyncClientInterface|null $asyncHttpClient
	 * @param ProxyDeferredFactory|null $proxyDeferredFactory
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	function __construct(
		ScopeConfigInterface $scopeConfig,
		RateErrorFactory $rateErrorFactory,
		LoggerInterface $logger,
		Security $xmlSecurity,
		ElementFactory $xmlElFactory,
		RateFactory $rateFactory,
		RateMethodFactory $rateMethodFactory,
		TrackFactory $trackFactory,
		TrackErrorFactory $trackErrorFactory,
		TrackStatusFactory $trackStatusFactory,
		RegionFactory $regionFactory,
		CountryFactory $countryFactory,
		CurrencyFactory $currencyFactory,
		PriceCurrencyInterface $priceCurrency,
		Data $directoryData,
		StockRegistryInterface $stockRegistry,
		FormatInterface $localeFormat,
		Config $configHelper,
		ClientFactory $httpClientFactory,
		StoreManagerInterface $storeManager,
		array $data = [],
		?AsyncClientInterface $asyncHttpClient = null,
		?ProxyDeferredFactory $proxyDeferredFactory = null
	) {
		parent::__construct(
			$scopeConfig,
			$rateErrorFactory,
			$logger,
			$xmlSecurity,
			$xmlElFactory,
			$rateFactory,
			$rateMethodFactory,
			$trackFactory,
			$trackErrorFactory,
			$trackStatusFactory,
			$regionFactory,
			$countryFactory,
			$currencyFactory,
			$directoryData,
			$stockRegistry,
			$data
		);
		$this->_priceCurrency = $priceCurrency;
		$this->_localeFormat = $localeFormat;
		$this->configHelper = $configHelper;
		$this->asyncHttpClient = $asyncHttpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
		$this->deferredProxyFactory = $proxyDeferredFactory
			?? ObjectManager::getInstance()->get(ProxyDeferredFactory::class);
		$this->storeManager = $storeManager;
	}

	/**
	 * @return bool
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	protected function isAdmin()
	{
		if ($this->appState->getAreaCode() === FrontNameResolver::AREA_CODE) {
			return true;
		}
		return false;
	}

	/**
	 * Collect and get rates/errors
	 *
	 * @param RateRequest $request
	 * @return Result|Error|bool
	 */
	function collectRates(RateRequest $request) {
		# 2025-05-24 Dmitrii Fediuk https://upwork.com/fl/mage2pro
		# 1) "`zoom.red` requires a weight for `CalcularTarifa`":
		# https://github.com/ferreteo-com/site/issues/4
		# 2) `$request->getPackageWeight()` returns «0.0000» for zero weight.
		if (!(float)$request->getPackageWeight()) {
			$request->setPackageWeight((float)$this->getConfigData('weight__default'));
		}
		$this->setRequest($request);

		if (!$this->canCollectRates()) {
			return $this->getErrorMessage();
		}

		$this->setRequest($request);

		//To use the correct result in the callback.
		$this->_result = $result = $this->_getQuotes();

		return $this->deferredProxyFactory->create(
			[
				'deferred' => new CallbackDeferred(
					function () use ($request, $result) {
						$this->_result = $result;
						$this->_updateFreeMethodQuote($request);
						return $this->getResult();
					}
				)
			]
		);
	}

	/**
	 * @used-by self::collectRates()
	 * @param RateRequest $request
	 * @return $this
	 */
	function setRequest(RateRequest $request) {
		$this->_request = $request;

		$rowRequest = new DataObject();

		//$modeType = $this->getConfigData('mode_type');

		//$rowRequest->setModeType($this->configHelper->getCode('mode_type', $modeType));

		if ($request->getOrigCountry()) {
			$origCountry = $request->getOrigCountry();
		} else {
			$origCountry = $this->_scopeConfig->getValue(
				OrderShipment::XML_PATH_STORE_COUNTRY_ID,
				ScopeInterface::SCOPE_STORE,
				$request->getStoreId()
			);
		}

		$rowRequest->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

		if ($request->getOrigRegionCode()) {
			$origRegionCode = $request->getOrigRegionCode();
		} else {
			$origRegionCode = $this->_scopeConfig->getValue(
				OrderShipment::XML_PATH_STORE_REGION_ID,
				ScopeInterface::SCOPE_STORE,
				$request->getStoreId()
			);
		}
		if (is_numeric($origRegionCode)) {
			$origRegionCode = $this->_regionFactory->create()->load($origRegionCode)->getCode();
		}
		$rowRequest->setOrigRegionCode($origRegionCode);

		if ($request->getOrigPostcode()) {
			$rowRequest->setOrigPostal($request->getOrigPostcode());
		} else {
			$rowRequest->setOrigPostal(
				$this->_scopeConfig->getValue(
					OrderShipment::XML_PATH_STORE_ZIP,
					ScopeInterface::SCOPE_STORE,
					$request->getStoreId()
				)
			);
		}

		if ($request->getOrigCity()) {
			$rowRequest->setOrigCity($request->getOrigCity());
		}
		else {
			# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
			# "`strtoupper` does not work correctly with the Spanish language (e.g.: «SAN CRISTóBAL»)":
			# https://github.com/mage2pro/zoom-ve/issues/6
			$originCity = mb_strtoupper( $this->_scopeConfig->getValue(
				OrderShipment::XML_PATH_STORE_CITY,
				ScopeInterface::SCOPE_STORE,
				$request->getStoreId()
			) );
			if (empty($originCity)) {
				$originCity = self::DEFAULT_ORIGIN_CITY;
			}
			# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
			# "«San Cristóbal» is absent in the `origin_city` list
			# in `Dfe\ZoomVe\Helper\Config::getConfigData()`": https://github.com/mage2pro/zoom-ve/issues/7
			$rowRequest->setOrigCity(OCL::p($originCity));
		}

		if ($request->getDestCountryId()) {
			$destCountry = $request->getDestCountryId();
		} else {
			$destCountry = self::USA_COUNTRY_ID;
		}

		$country = $this->_countryFactory->create()->load($destCountry);
		$rowRequest->setDestCountry($country->getData('iso2_code') ?: $destCountry);

		$rowRequest->setDestRegionCode($request->getDestRegionCode());

		if ($request->getDestPostcode()) {
			$rowRequest->setDestPostal($request->getDestPostcode());
		}
		# 2025-05-28 Dmitrii Fediuk https://upwork.com/fl/mage2pro
		# «mb_strtoupper(): Passing null to parameter #1 ($string) of type string is deprecated
		# in vendor/mage2pro/zoom-ve/Model/Carrier.php on line 359»:
		# https://github.com/mage2pro/zoom-ve/issues/12
		if ($request->getDestCity()) {
			# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
			# 1) "`strtoupper` does not work correctly with the Spanish language (e.g.: «SAN CRISTóBAL»)":
			# https://github.com/mage2pro/zoom-ve/issues/6
			# 2) "«San Cristóbal» is absent in the `origin_city` list
			# in `Dfe\ZoomVe\Helper\Config::getConfigData()`": https://github.com/mage2pro/zoom-ve/issues/7
			$rowRequest->setDestCity(OCL::p(mb_strtoupper($request->getDestCity())));
		}

		$weight = $this->getTotalNumOfBoxes($request->getPackageWeight());

		$weight = $this->_getCorrectWeight($weight);

		$rowRequest->setWeight($weight);

		if ($request->getPackageQty()) {
			$rowRequest->setQuantityPieces($request->getPackageQty());
		}

		if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
			$rowRequest->setFreeMethodWeight($request->getFreeMethodWeight());
		}

		$rowRequest->setValue($request->getPackageValue());
		$rowRequest->setValueWithDiscount($request->getPackageValueWithDiscount());

		$unit = $this->getConfigData('unit_of_measure');

		$rowRequest->setUnitMeasure($unit);
		$rowRequest->setIsReturn($request->getIsReturn());
		$rowRequest->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

		$this->_rawRequest = $rowRequest;

		return $this;
	}

	/**
	 * Get correct weight
	 *
	 * Namely:
	 * Checks the current weight to comply with the minimum weight standards set by the carrier.
	 * Then strictly rounds the weight up until the first significant digit after the decimal point.
	 *
	 * @param float|int $weight
	 * @return float
	 */
	protected function _getCorrectWeight($weight)
	{
		$minWeight = $this->getConfigData('min_package_weight');

		if ($weight < $minWeight) {
			$weight = $minWeight;
		}

		//rounds a number to one significant figure
		$weight = ceil($weight * 10) / 10;

		return $weight;
	}

	/**
	 * Get result of request
	 *
	 * @return Result
	 */
	function getResult()
	{
		return $this->_result;
	}

	/**
	 * Set free method request
	 *
	 * @param string $freeMethod
	 * @return void
	 */
	protected function _setFreeMethodRequest($freeMethod)
	{
		$r = $this->_rawRequest;

		$weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
		$weight = $this->_getCorrectWeight($weight);
		$r->setWeight($weight);
		$r->setAction($this->configHelper->getCode('action', 'single'));
		$r->setProduct($freeMethod);
	}

	/**
	 * Do remote request for  and handle errors
	 *
	 * @return Result|null
	 */
	protected function _getQuotes()
	{
		$rowRequest = $this->_rawRequest;
		if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
			$destPostal = substr((string)$rowRequest->getDestPostal(), 0, 5);
		} else {
			$destPostal = $rowRequest->getDestPostal();
		}

		$result = $this->_rateFactory->create();
		# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
		# "Replace the `VEF` currency with `VES`": https://github.com/mage2pro/zoom-ve/issues/10
		$vesRate = $this->_getBaseCurrencyRate('VES');
		//$total = (double) $rowRequest->getBaseSubtotalInclTax() * $vesRate;
		$total = (double) $rowRequest->getValue() * $vesRate;
		$params = [
			'ciudad_remitente' => $rowRequest->getOrigCity(), //Sender city
			'valor_mercancia' => 0, //$total,
			'ciudad_destinatario' => $rowRequest->getDestCity(), //Recipient city
			'peso' => $rowRequest->getWeight(),
			'cantidad_piezas' => (int) $rowRequest->getQuantityPieces(),
			//'modalidad_tarifa' => $rowRequest->getModeType(),
		];
		# 2025-05-24 Dmitrii Fediuk https://upwork.com/fl/mage2pro
		# 1) "`zoom.red` / `CalcularTarifa`:
		# «El valor mínimo que puede declarar al asegurar su envío es de Bs.  943.24.
		# Debe ingresar una cantidad igual o mayor a esta y a la vez menor o igual a Bs. 471620.5»":
		# https://github.com/ferreteo-com/site/issues/6
		# 2) The minimum value of `valor_declarado` seems to be 10 USD.
		# I use 15 to ensure that the API call does not fail.
		# 2) The maximum value of `valor_declarado` seems to be 5000 USD.
		# I use 4500 to ensure that the API call does not fail.
		$params['valor_declarado'] = min(max($total, 15 * $vesRate), 4500 * $vesRate);
		$allowedMethods = $this->getAllowedMethods();
		$modeTypes = $this->configHelper->getCode('mode_type');
		$responseBodies = array();

		foreach ($allowedMethods as $rMethodMode => $allowedMethod) {
			//$params['tipo_tarifa'] = $methodIndex; //rate type

			//foreach ($modeTypes as $modeIndex => $modeType) {
			$allowed = $this->_parseMethodMode($rMethodMode);
			//$methodMode = explode('-', $rmethodMode);
			//$method = is_array($methodMode) & isset($methodMode[0]) ? $methodMode[0] : '';
			//$mode = is_array($methodMode) & isset($methodMode[1]) ? $methodMode[1] : '';

			$params['tipo_tarifa'] = $allowed["method"]; //rate type
			$params['modalidad_tarifa'] = $allowed["mode"]; //type of delivery, 1 = office, 2 = door
			/*
			if($params['tipo_tarifa'] == 2){
				$params['valor_mercancia'] = 0;
			}
			*/

			unset($params['oficina_retirar']);
			if ($allowed["mode"] == "1") { //COD
				$params['oficina_retirar'] =
					$rowRequest->getOffice() ? $rowRequest->getOffice() : 50; // 50 = ZOOM SAN MARTIN; 889=ZOOM VALLE DE LA PASCUA
			}

			$responseBody = $this->_getCachedQuotes($params);



			if ($responseBody === null) {
				$debugData = ['request' => $params];
				try {
					$url = $this->getConfigData('gateway_url');
					if (!$url) {
						$url = $this->_defaultGatewayUrl;
					}
					$client = new Zend_Http_Client();
					$client->setUri($url);
					$client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
					$client->setParameterGet($params);
					$response = $client->request();
					$responseBody = $response->getBody();

					$debugData['result'] = $responseBody;
					$this->_setCachedQuotes($params, $responseBody);
				} catch (Throwable $e) {
					$debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
					$responseBody = '';
				}
				$this->_debug($debugData);
			}
			$responseBodies[$rMethodMode] = $responseBody;
			//}

		}

		return $this->_parseRestResponse($responseBodies);
	}

	/**
	 * @param $rmethodMode
	 * @return array
	 */
	protected function _parseMethodMode($rMethodMode) {
		$methodMode = explode('-', $rMethodMode);
		$method = is_array($methodMode) & isset($methodMode[0]) ? $methodMode[0] : '';
		$mode = is_array($methodMode) & isset($methodMode[1]) ? $methodMode[1] : '';
		return array("method" => $method, "mode" => $mode);
	}

	/**
	 * Get shipment by code
	 *
	 * @param string $code
	 * @param string $origin
	 * @return array|bool
	 */
	function getShipmentByCode($code, $origin = null)
	{
		if ($origin === null) {
			$origin = $this->getConfigData('origin_shipment');
		}
		$arr = $this->configHelper->getCode('originShipment', $origin);
		if (isset($arr[$code])) {
			return $arr[$code];
		} else {
			return false;
		}
	}

	/**
	 * @param $amount
	 * @param $currency
	 * @return string|string[]
	 */
	protected function _getRawAmount($amount, $currency) {
		$currencyFormat = [
			# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
			# "Replace the `VEF` currency with `VES`": https://github.com/mage2pro/zoom-ve/issues/10
			'VES' => [
				"accuracy"  => ".",
				"precision" => ","
			]
		];
		$amountParts = explode($currencyFormat[$currency]["precision"], $amount);
		$rawAmount = str_replace($currencyFormat[$currency]["accuracy"], "",$amountParts[0]);
		if (isset($amountParts[1])) {
			$rawAmount .= "." . $amountParts[1];
		}
		return $rawAmount;
	}

	/**
	 * Prepare shipping rate result based on response
	 *
	 * @param string $response
	 * @return Result
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	protected function _parseRestResponse($responses)
	{
		$costArr = [];
		$priceArr = [];
		if (is_array($responses) && count($responses) > 0) {
			//$rRows = $responses;
			foreach ($responses as $method => $rRow) {
				$row = explode('%', $rRow);
				$total = isset(json_decode($row[0],true)["entidadRespuesta"]["total"])
				? json_decode($row[0],true)["entidadRespuesta"]["total"] : null;
				if ($total) {
					$responsePrice = $this->_localeFormat->getNumber($total);
					$costArr[$method] = $responsePrice;
					$priceArr[$method] = $this->getMethodPrice($responsePrice, $row[0]);
				}

			}
			asort($priceArr);
		}

		$result = $this->_rateFactory->create();
		$vesRate = $this->_getBaseCurrencyRate("VEF");

		/*$priceArr = $costArr = array(
			'1-1' => 0.0,
			'1-2' => 0.0,
			'2-1' => 23.0,
			'2-2' => 26.0
		);*/

		if (empty($priceArr)) {
			$error = $this->_rateErrorFactory->create();
			$error->setCarrier('zoomenvios');
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($this->getConfigData('specificerrmsg'));
			$result->append($error);
		} else {
			foreach ($priceArr as $method => $price) {

				$rate = $this->_rateMethodFactory->create();
				$rate->setCarrier('zoomenvios');
				$rate->setCarrierTitle($this->getConfigData('title'));
				$rate->setMethod($method);
				$methodMode = $this->_parseMethodMode($method);

				//$modeTitle = $this->configHelper->getCode('mode_type_description', $mode);
				$price = (double)$price / $vesRate;
				$methodTitle = str_replace("-", "- <span>",
					$this->configHelper->getCode('method', $method)) . "</span>";

				if ($methodMode["method"] == 1) {
					$methodTitle .= " - " . $this->_priceCurrency->convertAndFormat($price);
					$price = 0;
				}
				$cost = (isset($costArr[$method])) ?
					(double)$costArr[$method] / $vesRate : null;

				$rate->setMethodTitle($methodTitle);
				$rate->setCost($cost);
				$rate->setPrice($price);
				$result->append($rate);
			}
		}

		return $result;
	}

	/**
	 * Get base currency rate
	 *
	 * @param string $code
	 * @return float
	 */
	protected function _getBaseCurrencyRate($code)
	{
		if (!$this->_baseCurrencyRate) {
			$this->_baseCurrencyRate = $this->_currencyFactory->create()->load(
				$this->_request->getBaseCurrency()->getCode()
			)->getAnyRate(
				$code
			);
		}

		return $this->_baseCurrencyRate;
	}

	/**
	 * Get base currency rate
	 *
	 * @param string $code
	 * @return float
	 */
	protected function _currencyConvert($code, $price, $toCurrency = null)
	{
		$convertedPrice = $this->_currencyFactory->create()->load(
			$code
		)->convert(
			$price, $toCurrency
		);

		return $convertedPrice;
	}

	/**
	 * Convert base price value to store price value
	 *
	 * @param string $amountValue //The amountValue
	 * @param string $currencyTo  //The currencyTo
	 *
	 * @return float
	 */
	function convertPrice($amountValue, $currencyTo)
	{
		$currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
		if ($currentCurrency != $currencyTo) {
			$baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();
			$rateTo = (float)$this->currencyFactory->create()->load($baseCurrency)->getRate($currentCurrency);
			$rateFrom = (float)$this->currencyFactory->create()->load($baseCurrency)->getRate($currencyTo);
			if ($rateFrom > 0) {
				$amountValue = (($amountValue * $rateTo)/$rateFrom);
			}
		}
		return $amountValue;
	}


	/**
	 * Convert base price value to store price value
	 *
	 * @param string $amountValue //The amountValue
	 * @param string $currencyTo  //The currencyTo
	 *
	 * @return float
	 */
	function convertVesPrice($amountValue, $currencyFrom, $currencyTo)
	{
		$currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
		if ($currentCurrency != $currencyTo) {
			$baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();
			$rateTo = (float)$this->currencyFactory->create()->load($baseCurrency)->getRate($currentCurrency);
			$rateFrom = (float)$this->currencyFactory->create()->load($baseCurrency)->getRate($currencyTo);
			if ($rateFrom > 0) {
				$amountValue = (($amountValue * $rateTo)/$rateFrom);
			}
		}
		return $amountValue;
	}

	/**
	 * Map currency alias to currency code
	 *
	 * @param string $code
	 * @return string
	 */
	private function mapCurrencyCode($code)
	{
		$currencyMapping = [
			'RMB' => 'CNY',
			'CNH' => 'CNY'
		];

		return $currencyMapping[$code] ?? $code;
	}

	/**
	 * Processing rate for ship element
	 *
	 * @param \Magento\Framework\Simplexml\Element $shipElement
	 * @param array $allowedMethods
	 * @param array $allowedCurrencies
	 * @param array $costArr
	 * @param array $priceArr
	 * @param bool $negotiatedActive
	 * @param \Magento\Framework\Simplexml\Config $xml
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	private function processShippingRateForItem(
		\Magento\Framework\Simplexml\Element $shipElement,
		array $allowedMethods,
		array $allowedCurrencies,
		array &$costArr,
		array &$priceArr,
		bool $negotiatedActive,
		\Magento\Framework\Simplexml\Config $xml
	): void {
		$code = (string)$shipElement->Service->Code;
		if (in_array($code, $allowedMethods)) {
			//The location of tax information is in a different place
			// depending on whether we are using negotiated rates or not
			if ($negotiatedActive) {
				$includeTaxesArr = $xml->getXpath(
					"//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates"
					. "/NetSummaryCharges/TotalChargesWithTaxes"
				);
				$includeTaxesActive = $this->getConfigFlag('include_taxes') && !empty($includeTaxesArr);
				if ($includeTaxesActive) {
					$cost = $shipElement->NegotiatedRates
						->NetSummaryCharges
						->TotalChargesWithTaxes
						->MonetaryValue;

					$responseCurrencyCode = $this->mapCurrencyCode(
						(string)$shipElement->NegotiatedRates
							->NetSummaryCharges
							->TotalChargesWithTaxes
							->CurrencyCode
					);
				} else {
					$cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
					$responseCurrencyCode = $this->mapCurrencyCode(
						(string)$shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode
					);
				}
			} else {
				$includeTaxesArr = $xml->getXpath(
					"//RatingServiceSelectionResponse/RatedShipment/TotalChargesWithTaxes"
				);
				$includeTaxesActive = $this->getConfigFlag('include_taxes') && !empty($includeTaxesArr);
				if ($includeTaxesActive) {
					$cost = $shipElement->TotalChargesWithTaxes->MonetaryValue;
					$responseCurrencyCode = $this->mapCurrencyCode(
						(string)$shipElement->TotalChargesWithTaxes->CurrencyCode
					);
				} else {
					$cost = $shipElement->TotalCharges->MonetaryValue;
					$responseCurrencyCode = $this->mapCurrencyCode(
						(string)$shipElement->TotalCharges->CurrencyCode
					);
				}
			}

			//convert price with Origin country currency code to base currency code
			$successConversion = true;
			if ($responseCurrencyCode) {
				if (in_array($responseCurrencyCode, $allowedCurrencies)) {
					$cost = (double)$cost * $this->_getBaseCurrencyRate($responseCurrencyCode);
				} else {
					$errorTitle = __(
						'We can\'t convert a rate from "%1-%2".',
						$responseCurrencyCode,
						$this->_request->getPackageCurrency()->getCode()
					);
					$error = $this->_rateErrorFactory->create();
					$error->setCarrier('zoomenvios');
					$error->setCarrierTitle($this->getConfigData('title'));
					$error->setErrorMessage($errorTitle);
					$successConversion = false;
				}
			}

			if ($successConversion) {
				$costArr[$code] = $cost;
				$priceArr[$code] = $this->getMethodPrice((float)$cost, $code);
			}
		}
	}

	/**
	 * Get tracking
	 *
	 * @param string|string[] $trackings
	 * @return Result
	 */
	function getTracking($trackings)
	{
		if (!is_array($trackings)) {
			$trackings = [$trackings];
		}

		//zoomenvios no longer support tracking for data streaming version
		//so we can only reply the popup window to zoomenvios.
		$result = $this->_trackFactory->create();
		foreach ($trackings as $tracking) {
			$status = $this->_trackStatusFactory->create();
			$status->setCarrier('zoomenvios');
			$status->setCarrierTitle($this->getConfigData('title'));
			$status->setTracking($tracking);
			$status->setPopup(1);
			$status->setUrl(
				"https://sandbox.zoom.red/baaszoom/public/canguroazul/getInfoTracking?" .
				" tipo_busqueda=1&codigo=71090585&codigo_cliente=={$tracking}"
			);
			$result->append($status);
		}

		$this->_result = $result;

		return $this->_result;
	}

	/**
	 * Get tracking response
	 *
	 * @return string
	 */
	function getResponse()
	{
		$statuses = '';
		if ($this->_result instanceof \Magento\Shipping\Model\Tracking\Result) {
			$trackings = $this->_result->getAllTrackings();
			if ($trackings) {
				foreach ($trackings as $tracking) {
					$data = $tracking->getAllData();
					if ($data) {
						if (isset($data['status'])) {
							$statuses .= __($data['status']);
						} else {
							$statuses .= __($data['error_message']);
						}
					}
				}
			}
		}
		if (empty($statuses)) {
			$statuses = __('Empty response');
		}

		return $statuses;
	}

	/**
	 * Get allowed shipping methods.
	 *
	 * @return array
	 */
	function getAllowedMethods()
	{
		$allowedMethods = explode(',', (string)$this->getConfigData('allowed_methods'));
		$isZoomXml = $this->getConfigData('type') === 'ZoomEnvios_XML';
		$origin = $this->getConfigData('origin_shipment');

		$availableByTypeMethods = $isZoomXml
			? $this->configHelper->getCode('originShipment', $origin)
			: $this->configHelper->getCode('method');

		$methods = [];
		foreach ($availableByTypeMethods as $methodCode => $methodData) {
			if (in_array($methodCode, $allowedMethods)) {
				$methods[$methodCode] = $methodData->getText();
			}
		}

		return $methods;
	}

	/**
	 * Form XML for shipment request
	 *
	 * @param DataObject $request
	 * @return string
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function _formShipmentRequest(DataObject $request)
	{
		$packages = $request->getPackages();
		$shipmentItems = [];
		foreach ($packages as $package) {
			$shipmentItems[] = $package['items'];
		}
		$shipmentItems = array_merge(...$shipmentItems);

		$xmlRequest = $this->_xmlElFactory->create(
			['data' => '<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>']
		);
		$requestPart = $xmlRequest->addChild('Request');
		$requestPart->addChild('RequestAction', 'ShipConfirm');
		$requestPart->addChild('RequestOption', 'nonvalidate');

		$shipmentPart = $xmlRequest->addChild('Shipment');
		if ($request->getIsReturn()) {
			$returnPart = $shipmentPart->addChild('ReturnService');
			// ZoomEnvios Print Return Label
			$returnPart->addChild('Code', '9');
		}
		$shipmentPart->addChild('Description', $this->generateShipmentDescription($shipmentItems));
		//empirical

		$shipperPart = $shipmentPart->addChild('Shipper');
		if ($request->getIsReturn()) {
			$shipperPart->addChild('Name', $request->getRecipientContactCompanyName());
			$shipperPart->addChild('AttentionName', $request->getRecipientContactPersonName());
			$shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
			$shipperPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

			$addressPart = $shipperPart->addChild('Address');
			$addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet());
			$addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
			$addressPart->addChild('City', $request->getRecipientAddressCity());
			$addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
			$addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
			if ($request->getRecipientAddressStateOrProvinceCode()) {
				$addressPart->addChild('StateProvinceCode', $request->getRecipientAddressStateOrProvinceCode());
			}
		} else {
			$shipperPart->addChild('Name', $request->getShipperContactCompanyName());
			$shipperPart->addChild('AttentionName', $request->getShipperContactPersonName());
			$shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
			$shipperPart->addChild('PhoneNumber', $request->getShipperContactPhoneNumber());

			$addressPart = $shipperPart->addChild('Address');
			$addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
			$addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
			$addressPart->addChild('City', $request->getShipperAddressCity());
			$addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
			$addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
			if ($request->getShipperAddressStateOrProvinceCode()) {
				$addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
			}
		}

		$shipToPart = $shipmentPart->addChild('ShipTo');
		$shipToPart->addChild('AttentionName', $request->getRecipientContactPersonName());
		$shipToPart->addChild(
			'CompanyName',
			$request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : 'N/A'
		);
		$shipToPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

		$addressPart = $shipToPart->addChild('Address');
		$addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet1());
		$addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
		$addressPart->addChild('City', $request->getRecipientAddressCity());
		$addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
		$addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
		if ($request->getRecipientAddressStateOrProvinceCode()) {
			$addressPart->addChild('StateProvinceCode', $request->getRecipientAddressRegionCode());
		}
		if ($this->getConfigData('mode_type') == 'DOR') {
			$addressPart->addChild('ResidentialAddress');
		}

		if ($request->getIsReturn()) {
			$shipFromPart = $shipmentPart->addChild('ShipFrom');
			$shipFromPart->addChild('AttentionName', $request->getShipperContactPersonName());
			$shipFromPart->addChild(
				'CompanyName',
				$request->getShipperContactCompanyName() ? $request
					->getShipperContactCompanyName() : $request
					->getShipperContactPersonName()
			);
			$shipFromAddress = $shipFromPart->addChild('Address');
			$shipFromAddress->addChild('AddressLine1', $request->getShipperAddressStreet1());
			$shipFromAddress->addChild('AddressLine2', $request->getShipperAddressStreet2());
			$shipFromAddress->addChild('City', $request->getShipperAddressCity());
			$shipFromAddress->addChild('CountryCode', $request->getShipperAddressCountryCode());
			$shipFromAddress->addChild('PostalCode', $request->getShipperAddressPostalCode());
			if ($request->getShipperAddressStateOrProvinceCode()) {
				$shipFromAddress->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
			}

			$addressPart = $shipToPart->addChild('Address');
			$addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
			$addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
			$addressPart->addChild('City', $request->getShipperAddressCity());
			$addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
			$addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
			if ($request->getShipperAddressStateOrProvinceCode()) {
				$addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
			}
			if ($this->getConfigData('mode_type') == 'DOR') {
				$addressPart->addChild('ResidentialAddress');
			}
		}

		$servicePart = $shipmentPart->addChild('Service');
		$servicePart->addChild('Code', $request->getShippingMethod());

		$packagePart = [];
		$customsTotal = 0;
		$packagingTypes = [];
		$deliveryConfirmationLevel = $this->_getDeliveryConfirmationLevel(
			$request->getRecipientAddressCountryCode()
		);
		foreach ($packages as $packageId => $package) {
			$packageItems = $package['items'];
			$packageParams = new DataObject($package['params']);
			$packagingType = $package['params']['container'];
			$packagingTypes[] = $packagingType;
			$height = $packageParams->getHeight();
			$width = $packageParams->getWidth();
			$length = $packageParams->getLength();
			$weight = $packageParams->getWeight();
			$weightUnits = $packageParams->getWeightUnits() == \Zend_Measure_Weight::POUND ? 'LBS' : 'KGS';
			$dimensionsUnits = $packageParams->getDimensionUnits() == \Zend_Measure_Length::INCH ? 'IN' : 'CM';
			$deliveryConfirmation = $packageParams->getDeliveryConfirmation();
			$customsTotal += $packageParams->getCustomsValue();

			$packagePart[$packageId] = $shipmentPart->addChild('Package');
			$packagePart[$packageId]->addChild('Description', $this->generateShipmentDescription($packageItems));
			//empirical
			$packagePart[$packageId]->addChild('PackagingType')->addChild('Code', $packagingType);
			$packageWeight = $packagePart[$packageId]->addChild('PackageWeight');
			$packageWeight->addChild('Weight', $weight);
			$packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $weightUnits);

			// set dimensions
			if ($length || $width || $height) {
				$packageDimensions = $packagePart[$packageId]->addChild('Dimensions');
				$packageDimensions->addChild('UnitOfMeasurement')->addChild('Code', $dimensionsUnits);
				$packageDimensions->addChild('Length', $length);
				$packageDimensions->addChild('Width', $width);
				$packageDimensions->addChild('Height', $height);
			}

			// zoomenvios support reference number only for domestic service
			if ($this->_isUSCountry($request->getRecipientAddressCountryCode())
				&& $this->_isUSCountry($request->getShipperAddressCountryCode())
			) {
				if ($request->getReferenceData()) {
					$referenceData = $request->getReferenceData() . $packageId;
				} else {
					$referenceData = 'Order #' .
						$request->getOrderShipment()->getOrder()->getIncrementId() .
						' P' .
						$packageId;
				}
				$referencePart = $packagePart[$packageId]->addChild('ReferenceNumber');
				$referencePart->addChild('Code', '02');
				$referencePart->addChild('Value', $referenceData);
			}

			if ($deliveryConfirmation && $deliveryConfirmationLevel === self::DELIVERY_CONFIRMATION_PACKAGE) {
					$serviceOptionsNode = $packagePart[$packageId]->addChild('PackageServiceOptions');
					$serviceOptionsNode->addChild(
						'DeliveryConfirmation'
					)->addChild(
						'DCISType',
						$deliveryConfirmation
					);
			}
		}

		if (!empty($deliveryConfirmation) && $deliveryConfirmationLevel === self::DELIVERY_CONFIRMATION_SHIPMENT) {
			$serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
			$serviceOptionsNode->addChild(
				'DeliveryConfirmation'
			)->addChild(
				'DCISType',
				$deliveryConfirmation
			);
		}

		$shipmentPart->addChild('PaymentInformation')
			->addChild('Prepaid')
			->addChild('BillShipper')
			->addChild('AccountNumber', $this->getConfigData('shipper_number'));


		$labelPart = $xmlRequest->addChild('LabelSpecification');
		$labelPart->addChild('LabelPrintMethod')->addChild('Code', 'GIF');
		$labelPart->addChild('LabelImageFormat')->addChild('Code', 'GIF');

		return $xmlRequest->asXml();
	}

	/**
	 * Generates shipment description.
	 *
	 * @param array $items
	 * @return string
	 */
	private function generateShipmentDescription(array $items): string
	{
		$itemsDesc = [];
		$itemsShipment = $items;
		foreach ($itemsShipment as $itemShipment) {
			$item = new \Magento\Framework\DataObject();
			$item->setData($itemShipment);
			$itemsDesc[] = $item->getName();
		}

		return substr(implode(' ', $itemsDesc), 0, 35);
	}

	/**
	 * Send and process shipment accept request
	 *
	 * @param Element $shipmentConfirmResponse
	 * @return DataObject
	 * @deprecated 100.3.3 New asynchronous methods introduced.
	 * @see requestToShipment
	 */
	protected function _sendShipmentAcceptRequest(Element $shipmentConfirmResponse)
	{
		$xmlRequest = $this->_xmlElFactory->create(
			['data' => '<?xml version = "1.0" ?><ShipmentAcceptRequest/>']
		);
		$request = $xmlRequest->addChild('Request');
		$request->addChild('RequestAction', 'ShipAccept');
		$xmlRequest->addChild('ShipmentDigest', $shipmentConfirmResponse->ShipmentDigest);
		$debugData = ['request' => $this->filterDebugData($this->_xmlAccessRequest) . $xmlRequest->asXML()];

		try {
			$deferredResponse = $this->asyncHttpClient->request(
				new Request(
					$this->getShipAcceptUrl(),
					Request::METHOD_POST,
					['Content-Type' => 'application/xml'],
					$this->_xmlAccessRequest . $xmlRequest->asXML()
				)
			);
			$xmlResponse = $deferredResponse->get()->getBody();
			$debugData['result'] = $xmlResponse;
			$this->_setCachedQuotes($xmlRequest, $xmlResponse);
		} catch (Throwable $e) {
			$debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
			$xmlResponse = '';
		}

		$response = '';
		try {
			$response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
		} catch (Throwable $e) {
			$debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
		}

		$result = new DataObject();
		if (isset($response->Error)) {
			$result->setErrors((string)$response->Error->ErrorDescription);
		} else {
			$shippingLabelContent = (string)$response->ShipmentResults->PackageResults->LabelImage->GraphicImage;
			$trackingNumber = (string)$response->ShipmentResults->PackageResults->TrackingNumber;

			// phpcs:ignore Magento2.Functions.DiscouragedFunction
			$result->setShippingLabelContent(base64_decode($shippingLabelContent));
			$result->setTrackingNumber($trackingNumber);
		}

		$this->_debug($debugData);

		return $result;
	}

	/**
	 * Get ship accept url
	 *
	 * @return string
	 */
	function getShipAcceptUrl()
	{
		if ($this->getConfigData('is_account_live')) {
			$url = $this->_liveUrls['ShipAccept'];
		} else {
			$url = $this->_defaultUrls['ShipAccept'];
		}

		return $url;
	}

	/**
	 * Request quotes for given packages.
	 *
	 * @param DataObject $request
	 * @return string[] Quote IDs.
	 * @throws LocalizedException
	 * @throws RuntimeException
	 */
	private function requestQuotes(DataObject $request): array
	{
		/** @var HttpResponseDeferredInterface[] $quotesRequests */
		//Getting quotes
		$this->_prepareShipmentRequest($request);
		$rawXmlRequest = $this->_formShipmentRequest($request);
		$this->setXMLAccessRequest();
		$xmlRequest = $this->_xmlAccessRequest . $rawXmlRequest;
		$this->_debug(['request_quote' => $this->filterDebugData($this->_xmlAccessRequest) . $rawXmlRequest]);
		$quotesRequests[] = $this->asyncHttpClient->request(
			new Request(
				$this->getShipConfirmUrl(),
				Request::METHOD_POST,
				['Content-Type' => 'application/xml'],
				$xmlRequest
			)
		);

		$ids = [];
		//Processing quote responses
		foreach ($quotesRequests as $quotesRequest) {
			$httpResponse = $quotesRequest->get();
			if ($httpResponse->getStatusCode() >= 400) {
				throw new LocalizedException(__('Failed to get the quote'));
			}
			try {
				/** @var Element $response */
				$response = $this->_xmlElFactory->create(['data' => $httpResponse->getBody()]);
				$this->_debug(['response_quote' => $response]);
			} catch (Throwable $e) {
				throw new RuntimeException($e->getMessage());
			}
			if (isset($response->Response->Error)
				&& in_array($response->Response->Error->ErrorSeverity, ['Hard', 'Transient'])
			) {
				throw new RuntimeException((string)$response->Response->Error->ErrorDescription);
			}

			$ids[] = $response->ShipmentDigest;
		}

		return $ids;
	}

	/**
	 * Request ZoomEnvios to ship items based on quotes.
	 *
	 * @param string[] $quoteIds
	 * @return DataObject[]
	 * @throws LocalizedException
	 * @throws RuntimeException
	 */
	private function requestShipments(array $quoteIds): array
	{
		/** @var HttpResponseDeferredInterface[] $shippingRequests */
		$shippingRequests = [];
		foreach ($quoteIds as $quoteId) {
			/** @var Element $xmlRequest */
			$xmlRequest = $this->_xmlElFactory->create(
				['data' => '<?xml version = "1.0" ?><ShipmentAcceptRequest/>']
			);
			$request = $xmlRequest->addChild('Request');
			$request->addChild('RequestAction', 'ShipAccept');
			$xmlRequest->addChild('ShipmentDigest', $quoteId);

			$debugRequest = $this->filterDebugData($this->_xmlAccessRequest) . $xmlRequest->asXml();
			$this->_debug(
				[
					'request_shipment' => $debugRequest
				]
			);
			$shippingRequests[] = $this->asyncHttpClient->request(
				new Request(
					$this->getShipAcceptUrl(),
					Request::METHOD_POST,
					['Content-Type' => 'application/xml'],
					$this->_xmlAccessRequest . $xmlRequest->asXml()
				)
			);
		}
		//Processing shipment requests
		/** @var DataObject[] $results */
		$results = [];
		foreach ($shippingRequests as $shippingRequest) {
			$httpResponse = $shippingRequest->get();
			if ($httpResponse->getStatusCode() >= 400) {
				throw new LocalizedException(__('Failed to send the package'));
			}
			try {
				/** @var Element $response */
				$response = $this->_xmlElFactory->create(['data' => $httpResponse->getBody()]);
				$this->_debug(['response_shipment' => $response]);
			} catch (Throwable $e) {
				throw new RuntimeException($e->getMessage());
			}
			if (isset($response->Error)) {
				throw new RuntimeException((string)$response->Error->ErrorDescription);
			}

			foreach ($response->ShipmentResults->PackageResults as $packageResult) {
				$result = new DataObject();
				$shippingLabelContent = (string)$packageResult->LabelImage->GraphicImage;
				$trackingNumber = (string)$packageResult->TrackingNumber;
				// phpcs:ignore Magento2.Functions.DiscouragedFunction
				$result->setLabelContent(base64_decode($shippingLabelContent));
				$result->setTrackingNumber($trackingNumber);
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
	 *
	 * @param DataObject $request
	 * @return DataObject
	 * @deprecated 100.3.3 New asynchronous methods introduced.
	 * @see requestToShipment
	 */
	protected function _doShipmentRequest(DataObject $request)
	{
		$this->_prepareShipmentRequest($request);
		$result = new DataObject();
		$rawXmlRequest = $this->_formShipmentRequest($request);
		$this->setXMLAccessRequest();
		$xmlRequest = $this->_xmlAccessRequest . $rawXmlRequest;
		$xmlResponse = $this->_getCachedQuotes($xmlRequest);
		$debugData = [];

		if ($xmlResponse === null) {
			$debugData['request'] = $this->filterDebugData($this->_xmlAccessRequest) . $rawXmlRequest;
			$url = $this->getShipConfirmUrl();
			try {
				$deferredResponse = $this->asyncHttpClient->request(
					new Request(
						$url,
						Request::METHOD_POST,
						['Content-Type' => 'application/xml'],
						$xmlRequest
					)
				);
				$xmlResponse = $deferredResponse->get()->getBody();
				$debugData['result'] = $xmlResponse;
				$this->_setCachedQuotes($xmlRequest, $xmlResponse);
			} catch (Throwable $e) {
				$debugData['result'] = ['code' => $e->getCode(), 'error' => $e->getMessage()];
			}
		}

		try {
			$response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
		} catch (Throwable $e) {
			$debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
			$result->setErrors($e->getMessage());
		}

		if (isset($response->Response->Error)
			&& in_array($response->Response->Error->ErrorSeverity, ['Hard', 'Transient'])
		) {
			$result->setErrors((string)$response->Response->Error->ErrorDescription);
		}

		$this->_debug($debugData);

		if ($result->hasErrors() || empty($response)) {
			return $result;
		} else {
			return $this->_sendShipmentAcceptRequest($response);
		}
	}

	/**
	 * Get ship confirm url
	 *
	 * @return string
	 */
	function getShipConfirmUrl()
	{
		$url = $this->getConfigData('url');
		if (!$url) {
			if ($this->getConfigData('is_account_live')) {
				$url = $this->_liveUrls['ShipConfirm'];

				return $url;
			} else {
				$url = $this->_defaultUrls['ShipConfirm'];

				return $url;
			}
		}

		return $url;
	}

	/**
	 * @inheritDoc
	 */
	function requestToShipment($request)
	{
		$packages = $request->getPackages();
		if (!is_array($packages) || !$packages) {
			throw new LocalizedException(__('No packages for request'));
		}
		if ($request->getStoreId() != null) {
			$this->setStore($request->getStoreId());
		}

		// phpcs:disable
		try {
			$quoteIds = $this->requestQuotes($request);
			$labels = $this->requestShipments($quoteIds);
		} catch (LocalizedException $exception) {
			$this->_logger->critical($exception);
			return new DataObject(['errors' => [$exception->getMessage()]]);
		} catch (RuntimeException $exception) {
			$this->_logger->critical($exception);
			return new DataObject(['errors' => __('Failed to send items')]);
		}
		// phpcs:enable

		return new DataObject(['info' => $labels]);
	}

	/**
	 * @inheritDoc
	 */
	function returnOfShipment($request)
	{
		$request->setIsReturn(true);

		return $this->requestToShipment($request);
	}

	/**
	 * Return delivery confirmation types of carrier
	 *
	 * @param DataObject|null $params
	 * @return array|bool
	 */
	function getDeliveryConfirmationTypes(DataObject $params = null)
	{
		$countryRecipient = $params != null ? $params->getCountryRecipient() : null;
		$deliveryConfirmationTypes = [];
		switch ($this->_getDeliveryConfirmationLevel($countryRecipient)) {
			case self::DELIVERY_CONFIRMATION_PACKAGE:
				$deliveryConfirmationTypes = [
					1 => __('Delivery Confirmation'),
					2 => __('Signature Required'),
					3 => __('Adult Signature Required'),
				];
				break;
			case self::DELIVERY_CONFIRMATION_SHIPMENT:
				$deliveryConfirmationTypes = [1 => __('Signature Required'), 2 => __('Adult Signature Required')];
				break;
			default:
				break;
		}
		array_unshift($deliveryConfirmationTypes, __('Not Required'));

		return $deliveryConfirmationTypes;
	}


	/**
	 * Get delivery confirmation level based on origin/destination
	 *
	 * Return null if delivery confirmation is not acceptable
	 *
	 * @param string|null $countyDestination
	 * @return int|null
	 */
	protected function _getDeliveryConfirmationLevel($countyDestination = null)
	{
		if ($countyDestination === null) {
			return null;
		}

		if ($countyDestination == self::USA_COUNTRY_ID) {
			return self::DELIVERY_CONFIRMATION_PACKAGE;
		}

		return self::DELIVERY_CONFIRMATION_SHIPMENT;
	}
}

