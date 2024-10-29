<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Utils;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\PricingModelInterface;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\AllUnitPricing;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\TieredPricing;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingTable\PricingTableInterface;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingTable\DirectPricingTable;

/**
 * Pricing Mapper Trait.
 *
 */
trait PricingMapperTrait {

	/**
	 * Get Product Pricing Model based on settings value.
	 *
	 * @param int $pricing_model
	 * @return PricingModelInterface|null
	 */
	protected function pricing_model_mapping( $pricing_model ) {
		switch ( $pricing_model ) {
			case 1:
				return new AllUnitPricing();
			case 2:
				return new TieredPricing();
			default:
				return null;
		}
	}

	/**
	 * Pricing table Mapping.
	 *
	 * @param int $pricing_type
	 * @return PricingTableInterface|null
	 */
	protected function pricing_table_mapping( $pricing_type, $prefix = '' ) {
		switch ( $pricing_type ) {
			case 1:
				return new DirectPricingTable( $prefix );
			default:
				return null;
		}
	}

}
