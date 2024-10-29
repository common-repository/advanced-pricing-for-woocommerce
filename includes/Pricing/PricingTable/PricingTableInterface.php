<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingTable;

/**
 * Pricing Table Interface.
 *
 */
interface PricingTableInterface {

	/**
	 * Get Pricing Table for a product based on the pricing table type.
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function get_pricing_table( $product_id );

	/**
	 * Get Pricing table Starting Price - Quantity.
	 *
	 * @param \WC_Product|int $product
	 * @return array
	 */
	public function get_table_base( $product );
}
