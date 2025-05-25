<?php
namespace Dfe\ZoomVe;
# 2025-05-25 Dmitrii Fediuk https://upwork.com/fl/mage2pro
# 1) "«San Cristóbal» is absent in the `origin_city` list in `Dfe\ZoomVe\Helper\Config::getConfigData()`":
# https://github.com/mage2pro/zoom-ve/issues/7
# 2) `getCiudades`: https://documenter.getpostman.com/view/6789630/S1Zz6V2v#c0910a5d-866a-4185-b999-b54ec817fa42)
# 3) https://sandbox.zoom.red/baaszoom/public/canguroazul/getCiudades?filtro=origen
final class OriginCityLocator {
	/**
	 * 2025-05-25
	 */
	static function p(string $v):int {return dfa(self::d(), mb_strtoupper($v));}

	/**
	 * 2025-05-25
	 * @used-by self::p()
	 */
	private static function d():array {return df_cache_get_simple('', function() {return df_map_r(
		dfa(
			df_http_json('https://sandbox.zoom.red/baaszoom/public/canguroazul/getCiudades?filtro=origen')
			,'entidadRespuesta'
		)
		,function(array $a):array {return array_values(dfa($a, ['nombre_ciudad', 'codciudad']));}
	);});}
}