<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingTable;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingTable\PricingTableInterface;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;

/**
 * Direct Pricing Class.
 */
class DirectPricingTable extends PricingBase implements PricingTableInterface {

	/**
	 * Constructor.
	 *
	 * @param string $prefix
	 */
	public function __construct( $prefix ) {
		parent::__construct();
		$this->prefix = $prefix;
	}

	/**
	 * Get Direct Pricing.
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function get_direct_pricing( $product_id ) {
		return $this->get_settings_key( 'direct_pricing', $product_id );
	}

	/**
	 * Get Pricing Table for a product based on the pricing table type.
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function get_pricing_table( $product_id ) {
		$pricing_table = $this->get_direct_pricing( $product_id );
		if ( is_null( $pricing_table ) ) {
			return array();
		}
		return $pricing_table;
	}

	/**
	 * Get Pricing Table Starting Point.
	 *
	 * @param \WC_Product|int $product
	 * @return array
	 */
	public function get_table_base( $product ) {
		if ( ! is_int( $product ) ) {
			$product = $product->get_id();
		}
		$pricing_table = $this->get_pricing_table( $product );
		if ( empty( $pricing_table ) ) {
			return array();
		}

		$pricing_table = $this->sort_pricing_table( $pricing_table );
		return $pricing_table[0];
	}
}
