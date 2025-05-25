<?php
namespace Dfe\ZoomVe\Helper;
# 2025-05-25
/** @used-by dfe_zv_cfg() */
class Config
{
	/**
	 * Get configuration data of carrier
	 *
	 * @param string $type
	 * @param string $code
	 * @return array|string|false
	 */
	function getCode($type, $code = '')
	{
		$codes = $this->getConfigData();
		if (!isset($codes[$type])) {
			return false;
		} elseif ('' === $code) {
			return $codes[$type];
		}

		if (is_array($code)) {
			$parm1 = $code[0];
			$parm2 = $code[1];
			if (!isset($codes[$type][$parm1][$parm2])) {
				return false;
			} else {
				return $codes[$type][$parm1][$parm2];
			}
		} else {
			if (!isset($codes[$type][$code])) {
				return false;
			} else {
				return $codes[$type][$code];
			}
		}
	}

	/**
	 * Get configuration data of carrier
	 *
	 * @param string $type
	 * @param string $code
	 * @return array|string|false
	 */
	function getCodes($type, $code = '')
	{
		$codes = $this->getConfigData();
		if (!isset($codes[$type])) {
			return false;
		} elseif ('' === $code) {
			return $codes[$type];
		}

		if (!isset($codes[$type][$code])) {
			return false;
		} else {
			return $codes[$type][$code];
		}
	}

	/**
	 * Get configuration data of carrier
	 *
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function getConfigData()
	{
		return [
			'method' => [
				'1-1' => __('Collect at Office - Paid On Destiny (COD)'),
				'1-2' => __('Delivered to My Door - Paid On Destiny (COD)'),
				'2-1' => __('Collect at Office - National'),
				'2-2' => __('Delivered to My Door - National'),
				'3' => __('International'),
			],//tipo_tarifa-modalidad_tarifa
			'origin_city' => [
				"ACARIGUA" => ["code" => 25, "office_code" => "2,1215"],
				"ANACO" => ["code" => 24, "office_code" => "3,2251"],
				"BARCELONA" => ["code" => 68, "office_code" => ""],
				"BARINAS" => ["code" => 33, "office_code" => "6,1543,1753,3465,1253,1904"],
				"BARQUISIMETO" => ["code" => 44, "office_code" => "15,612,1025,306,1069,956,1179,1726"],
				"CABIMAS" => ["code" => 29, "office_code" => "3478"],
				"CALABOZO" => ["code" => 2, "office_code" => "872"],
				"CARACAS" => ["code" => 19, "office_code" => "46,47,3476,825,1257,1372,1214,3113,50,3045,1382,2704,1127,1190,2768,1193,728,2706,651,3062,3143,730,2709,2402,1194,1885,3039,1220,3068,2435,1625,1679"],
				"CARUPANO" => ["code" => 20, "office_code" => "744"],
				"CATIA LA MAR" => ["code" => 425, "office_code" => "74"],
				"CHARALLAVE" => ["code" => 26, "office_code" => "54"],
				"CIUDAD BOLIVAR" => ["code" => 11, "office_code" => "56,1810,1756,1178"],
				"CIUDAD OJEDA" => ["code" => 28, "office_code" => "57,706"],
				"CORO" => ["code" => 12, "office_code" => "59,2181"],
				"CUMANA" => ["code" => 10, "office_code" => "60,2177,1115"],
				"EL TIGRE" => ["code" => 30, "office_code" => "62"],
				"EL VIGIA" => ["code" => 32, "office_code" => "745,1464"],
				"GUANARE" => ["code" => 43, "office_code" => "875"],
				"GUARENAS" => ["code" => 8, "office_code" => "67,163,2945,3459"],
				"GUATIRE" => ["code" => 196, "office_code" => "1452"],
				"LOS TEQUES" => ["code" => 21, "office_code" => "73"],
				"MARACAIBO" => ["code" => 6, "office_code" => "82,168,3063,2253,2980,2718,3314,3269,2711,2068"],
				"MARACAY" => ["code" => 3, "office_code" => "87,1252,805,1140,2588,2396,1703,3150,3546,2466,1195,811,2592"],
				"MATURIN" => ["code" => 22, "office_code" => "89,1830,2944,1580,2343,2712"],
				"MERIDA" => ["code" => 18, "office_code" => "95,3151,2066,1797,3148,382,1475"],
				"PORLAMAR" => ["code" => 15, "office_code" => "99,235,1811,1554"],
				"PUERTO AYACUCHO" => ["code" => 35, "office_code" => "100"],
				"PUERTO CABELLO" => ["code" => 31, "office_code" => "101,2465,2769"],
				"PUERTO ORDAZ" => ["code" => 14, "office_code" => "109,1206,3171,1809,775"],
				"PUNTO FIJO" => ["code" => 13, "office_code" => "110,1799,1688"],
				"SAN ANTONIO DEL TACHIRA" => ["code" => 16, "office_code" => "112,1689"],
				"SAN CARLOS" => ["code" => 1, "office_code" => "939"],
				"SAN FELIPE" => ["code" => 37, "office_code" => "874"],
				"SAN FERNANDO" => ["code" => 36, "office_code" => "871,2568"],
				"SAN JUAN DE LOS MORROS" => ["code" => 39, "office_code" => "945,2590"],
				"SANTA BARBARA DEL ZULIA" => ["code" => 366, "office_code" => "3549"],
				"VALENCIA" => ["code" => 4, "office_code" => "136,137,791,1050,2342,1832,2176,2569,816,1764,2216,3462,1905,1132,1704,361,2352,1319"],
				"VALERA" => ["code" => 34, "office_code" => "139,2979,1755"],
				"VALLE DE LA PASCUA" => ["code" => 40, "office_code" => "889,1833"]
			],
			"office" => [
				"ZOOM ACARIGUA" => 2,
				"ZOOM ANACO" => 3,
				"ZOOM BARCELONA" => 106,
				"ZOOM C.C. GALERIAS EL PARAISO" => 539,
				"ZOOM CDO CATIA" => 48,
				"ZOOM CHACAITO I" => 825,
				"ZOOM EL JUNQUITO (INVERSIONES MAR2405)" => 1137,
				"ZOOM PALO VERDE (NO UTILIZAR)" => 160,
				"ZOOM PLAZA VENEZUELA" => 49,
				"ZOOM SAN MARTIN" => 50,
				"ZOOM TELARES LOS ANDES" => 885,
				"ZOOM SAN CARLOS" => 939,
				"ALIADO ZOOM AG SERVICIOS" => 1215,
				"ALIADO ZOOM FV COMUNICACIONES" => 2251,
				"ALIADO ZOOM AUDIOBOX PRODUCCIONES" => 1589,
				"ALIADO ZOOM ENVIOS DISALBRICA 1" => 3309,
				"ALIADO ZOOM SEACAR ORIENTE 1702" => 3050,
				"ZOOM BARINAS" => 6,
				"ZOOM BARINAS CENTRO" => 1543,
				"ZOOM BARQUISIMETO" => 15,
				"ZOOM AEROPUERTO JACINTO LARA" => 612,
				"ZOOM CDO BRM" => 1025,
				"ALIADO ZOOM CONSTRUCTORA CUATRO ENE" => 306,
				"ALIADO ZOOM ETESCA" => 1069,
				"ALIADO ZOOM FREYCA 2009" => 956,
				"ALIADO ZOOM KS SERVICIOS EXPRESOS BARQUISIMETO" => 1179,
				"ALIADO ZOOM MERCAREPUESTOS AP" => 1726,
				"ALIADO ZOOM BIG BEN SERVICIOS EMPRESARIALES" => 1753,
				"ALIADO ZOOM FARMACIA BETANIA C.A (QIARIS FARMACIA)" => 3465,
				"ALIADO ZOOM INVERSIONES SERVICIOS Y ENVIOS BARINAS" => 1253,
				"ALIADO ZOOM INVERSIONES Y SERVICIOS CONSTRUC EXPRESS" => 1904,
				"ALIADO ZOOM ROMARCA INTERNACIONAL VENEZUELA C.A" => 3478,
				"ZOOM CALABOZO" => 872,
				"ZOOM LA URBINA"=>46,
				"ZOOM BELLO CAMPO"=>47,
				"ZOOM CATIA"=>3476,
				"ZOOM CHACAITO I"=>825,
				"ZOOM LA CALIFORNIA"=>1257,
				"ZOOM MAKRO LA YAGUARA"=>1372,
				"ZOOM PARQUE HUMBOLDT"=>1214,
				"ZOOM SABANA GRANDE"=>3113,
				"ZOOM SAN MARTIN"=>50,
				"ALIADO ZOOM CORPORACION ZANESIL, C.A"=>3045,
				"ALIADO ZOOM DIGITAL ANDREW,C.A"=>1382,
				"ALIADO ZOOM DIGITAL PARAISO"=>2704,
				"ALIADO ZOOM ENCOMIENDAS GALLERY 55, C.A."=>1127,
				"ALIADO ZOOM ENYS STYLE & FASHION, C.A. (LA YAGUARA)"=>1190,
				"ALIADO ZOOM GRUPO FIGUE, C.A. AV LIBERTADOR."=>2768,
				"ALIADO ZOOM IMPRESION,IMAGEN Y POP 2205 C.A"=>1193,
				"ALIADO ZOOM INSTALL COMPUTER, C.A"=>728,
				"ALIADO ZOOM INVERSIONES LA GRAN FLORIDA 2304, C.A."=>2706,
				"ALIADO ZOOM INVERSIONES MB33, C.A."=>651,
				"ALIADO ZOOM INVERSIONES RRSS 1388, C.A (EL PARAISO)"=>3062,
				"ALIADO ZOOM MBE CHUAO"=>3143,
				"ALIADO ZOOM MBE MULTICENTRO EMPRESARIAL DEL ESTE"=>730,
				"ALIADO ZOOM MORGAF INVERSIONES 777,C.A. ( LA CANDELARIA )"=>2709,
				"ALIADO ZOOM MULTISERVICIOS BFP, C.A."=>2402,
				"ALIADO ZOOM MULTISERVICIOS TEREMAR M.T. 2.012, C.A. ( COCHE )"=>1194,
				"ALIADO ZOOM NETCOM CONSULTORES, C.A. (MONTALBAN CC USLAR)"=>1885,
				"ALIADO ZOOM ORGANIZACION MADERLING C.A"=>3039,
				"ALIADO ZOOM PROPUESTAS URBANAS, C.A."=>1220,
				"ALIADO ZOOM RAPID ENVIOS DKSS, C.A"=>3068,
				"ALIADO ZOOM REPRESENTACIONES SIGMA 256, C.A."=>2435,
				"ALIADO ZOOM REPRESENTACIONES SRV 14, C.A."=>1625,
				"ALIADO ZOOM SUPLIDORA GLOBAL 3000, C.A. (PARQUE CENTRAL)"=>1679,
				"ZOOM SAN CARLOS" => 939,
				"ZOOM CARUPANO"=>744,
				"ZOOM CATIA LA MAR"=>74,
				"ZOOM CHARALLAVE CIUDAD CONCORDIA"=>54,
				"ZOOM CIUDAD BOLIVAR"=>56,
				"ALIADO ZOOM 1 DIGITAL, C.A."=>1810,
				"ALIADO ZOOM INVERSIONES EPSILON, C.A."=>1756,
				"ALIADO ZOOM MN LLAMADAS, C.A. (PASEO MENESES)"=>1178,
				"ZOOM CIUDAD OJEDA"=>57,
				"ALIADO ZOOM MBE CIUDAD OJEDA"=>706,
				"ZOOM CORO"=>59,
				"ALIADO ZOOM CONSTRUCCIONES CIVILES GENERALES CGC, C.A."=>2181,
				"ZOOM CUMANA"=>60,
				"ALIADO ZOOM FLEVIMAR, C.A."=>2177,
				"ALIADO ZOOM MBE CUMANA"=>1115,
				"ZOOM EL TIGRE"=>62,
				"ZOOM EL VIGIA"=>745,
				"ALIADO ZOOM INVERSIONES & SERVICIOS SANTIAGO LEAL, C.A. (TUCANIZON)"=>1464,
				"ZOOM GUANARE"=>875,
				"ZOOM GUARENAS"=>67,
				"ZOOM C.C MIRANDA"=>163,
				"ALIADO ZOOM ESTACIONAMIENTO Y MULTISERVICIOS SOUTO C.A"=>2945,
				"ALIADO ZOOM NSC CARGO & LOGISTICS, C.A"=>3459,
				"ZOOM C.C. BUENAVENTURA VISTA PLACE"=>1452,
				"ZOOM LOS TEQUES"=>73,
				"ZOOM MARACAIBO"=>82,
				"ZOOM LA CHINITA"=>168,
				"ALIADO ZOOM ARA EXPRESS, C.A."=>3063,
				"ALIADO ZOOM GRUPO MAUCOVENCA, C.A."=>2253,
				"ALIADO ZOOM INVERSIONES MARAEXPRESS, C.A"=>2980,
				"ALIADO ZOOM INVERSIONES ZULIMAR 05, C.A."=>2718,
				"ALIADO ZOOM SERVICIOS DE ENTREGAS Y ENCOMIENDAS BRAVO C.A"=>3314,
				"ALIADO ZOOM SERVICIOS V V M, C.A"=>3269,
				"ALIADO ZOOM SULIM, C.A."=>2711,
				"ALIADO ZOOM WENDY INVERSIONES TECNO VENEZOLANAS, C.A"=>2068,
				"ZOOM MARACAY"=>87,
				"ZOOM CDO MYC"=>1252,
				"ZOOM MARACAY AV. MIRANDA"=>805,
				"ZOOM PACIFICO"=>1140,
				"ALIADO ZOOM CANGURO EXPRESS 911, C.A."=>2588,
				"ALIADO ZOOM CENTRO DE COPIADO Y REPRODUCCION D.M.M., C.A."=>2396,
				"ALIADO ZOOM CORPORACION CALITRI, C.A."=>1703,
				"ALIADO ZOOM EPAC 2120, C.A."=>3150,
				"ALIADO ZOOM EXPRESS CORPORATION JM, C.A"=>3546,
				"ALIADO ZOOM FAST DELIVERY, C.A."=>2466,
				"ALIADO ZOOM INVERSIONES CASTILLO 2010, C.A."=>1195,
				"ALIADO ZOOM SHIPNET MARACAY"=>811,
				"ALIADO ZOOM TOTAL SOLUTIONS TOSOCA, C.A."=>2592,
				"ZOOM MATURIN"=>89,
				"ALIADO ZOOM GRUPO FERRECOLOR, C.A."=>1830,
				"ALIADO ZOOM MULTISERVICIOS BRIMI, C.A."=>2944,
				"ALIADO ZOOM MULTISERVICIOS HERSO, C.A."=>1580,
				"ALIADO ZOOM NEXUS VENEZUELA, C.A"=>2343,
				"ALIADO ZOOM REPRESENTACIONES SERVICIOS Y LOGISTICA ABAM. C.A"=>2712,
				"ZOOM MERIDA"=>95,
				"ALIADO ZOOM BOOCH, COMPAÑIA ANONIMA, C.A"=>3151,
				"ALIADO ZOOM ENVIOS PRADO MEDINA, C.A."=>2066,
				"ALIADO ZOOM INVERSIONES TERVAS, C.A."=>1797,
				"ALIADO ZOOM LA FERMATA DEL GIORNO C.A."=>3148,
				"ALIADO ZOOM SERVIPOST, C.A."=>382,
				"ALIADO ZOOM TELANGA SERVICIOS, C.A."=>1475,
				"ZOOM PORLAMAR"=>99,
				"ZOOM PORLAMAR CENTRO"=>235,
				"ALIADO ZOOM AFFARI BOOM, C.A."=>1811,
				"ALIADO ZOOM ENCOMIENDAS ISLA 2004, C.A. (CALLE CAMPOS)"=>1554,
				"ZOOM PUERTO AYACUCHO"=>100,
				"ZOOM PUERTO CABELLO"=>101,
				"ALIADO ZOOM GLOBAL G.S.M, C.A"=>2465,
				"ALIADO ZOOM GLOBAL G.S.M. II, C.A."=>2769,
				"ZOOM PUERTO ORDAZ"=>109,
				"ZOOM TAQUILLA CC ARTICA"=>1206,
				"ALIADO ZOOM CONSULTORES INTEGRALES EMPRESARIALES GIAGIAKOS C.A"=>3171,
				"ALIADO ZOOM GRUPO PF, C.A."=>1809,
				"ALIADO ZOOM REGION 416, C.A."=>775,
				"ZOOM PUNTO FIJO"=>110,
				"ALIADO ZOOM FCG 26, C.A."=>1799,
				"ALIADO ZOOM TRADE EXPRESS, C.A."=>1688,
				"ZOOM SAN ANTONIO"=>112,
				"ALIADO ZOOM VALYKAR, C.A."=>1689,
				"ZOOM SAN FELIPE"=>874,
				"ZOOM SAN FERNANDO DE APURE"=>871,
				"ALIADO ZOOM INVERSIONES VAS&LA, C.A."=>2568,
				"ZOOM SAN JUAN DE LOS MORROS"=>945,
				"ALIADO ZOOM INVERSIONES YEDAEVE, C.A."=>2590,
				"ALIADO ZOOM HEBRON DIATRIBUCIONES COMPAÑIA ANONIMA"=>3549,
				"ZOOM VALENCIA"=>136,
				"ZOOM AV. BOLIVAR NORTE"=>137,
				"ALIADO ZOOM ALLIANZ COURIER, C.A. ZONA INDUSTRIAL SUR."=>791,
				"ALIADO ZOOM ALLIANZ EXPRESS, C.A. SECTOR ROJAS QUEIPO."=>1050,
				"ALIADO ZOOM GRUPO MARPETH, C.A."=>2342,
				"ALIADO ZOOM INVERSIONES EMPORIO GRAFICO UNIVERSAL, C.A."=>1832,
				"ALIADO ZOOM INVERSIONES G-MARY, C.A."=>2176,
				"ALIADO ZOOM INVERSIONES J K M 1729, C.A."=>2569,
				"ALIADO ZOOM INVERSIONES KFF, C.A."=>816,
				"ALIADO ZOOM INVERSIONES SOFYKAR, C.A."=>1764,
				"ALIADO ZOOM LEYMA INVERSIONES, C.A.."=>2216,
				"ALIADO ZOOM MANINO EXPRESS SERVICES, C.A"=>3462,
				"ALIADO ZOOM OMEGA COLORS, C.A."=>1905,
				"ALIADO ZOOM PLAZA CENTER COMUNICACIONES (SECTOR LOS CAOBOS)"=>1132,
				"ALIADO ZOOM PUNTO IFO VALENCIA, C.A."=>1704,
				"ALIADO ZOOM PUNTO POST, C.A. (SAN DIEGO)"=>361,
				"ALIADO ZOOM RAPIDOS COURIER ETC, C.A."=>2352,
				"ALIADO ZOOM VIT PUBLICIDAD, C.A."=>1319,
				"ZOOM VALERA"=>139,
				"ALIADO ZOOM GRUPO FIGUE C.A"=>2979,
				"ALIADO ZOOM INVERSIONES MUNDO ELECTRONIC BOCONO, C.A."=>1755,
				"ZOOM VALLE DE LA PASCUA"=>889,
		"ALIADO ZOOM INVERSIONES TUENCOMIENDA.COM, C.A."=>1833
			], //getOficinas (getOffices)
			"country" => [
				"US" => 1,
				"PA" => 29,
				"VE" => 124
			], //getPaises (getCountries)
			'mode_type' => ['OFC' => 2, 'DOR' => 1], //Modalidad Tarifa
			'mode_type_description' => ['OFC' => __('Withdrawal By Office'), 'DOR' => __('Door To Door')],
			'shipment_type' => ['D' => 1, 'M' => 2],
			'shipment_type_description' => ['D' => __('DOCUMENT'), 'M' => __('COMMODITY')],//Tipo Envio (ShipmentType)
			'unit_of_measure' => ['KGS' => __('Kilograms'), 'LBS' => __('Pounds')]
		];
	}
}
