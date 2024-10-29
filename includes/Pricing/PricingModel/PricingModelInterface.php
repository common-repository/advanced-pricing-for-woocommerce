<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel;

/**
 * Pricing Model Interface.
 *
 */
interface PricingModelInterface {

	/**
	 * Get Pricing model name
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Validate Custom Pricing for a cart items.
	 *
	 * @param array $cart_item
	 * @param array $pricing_table
	 * @return mixed [ Price for Quantity ]
	 */
	public function calculate_price( &$cart_item, $pricing_table );

	/**
	 * Pricing Table HTML.
	 *
	 * @param int|\WC_Product $product_id
	 * @return mixed
	 */
	public function pricing_table_html( $product_id, $echo = false );

	/**
	 * Calculate Pricing Model Function.
	 *
	 * @param int $quantity
	 * @param array $pricing_table
	 * @param int $product_id
	 * @param boolean $return_total_price
	 * @return mixed
	 */
	public function _calculate_price( $quantity, $pricing_table, $product_id, $return_total_price = false );
}
