<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;

/**
 * Main Pricing Class.
 */
class Pricing extends PricingBase {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Singular Init.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		parent::__construct();
		$this->setup();
		$this->hooks();
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	private function setup() {  }

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'adjust_cart_items_price' ), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_cart_item_product', array( $this, 'filter_cart_item_product_price' ), PHP_INT_MAX, 3 );
	}

	/**
	 * Adjust Cart Item Pricing.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function adjust_cart_items_price( $cart ) {
		foreach ( $cart->get_cart_contents() as $cart_item_key => &$cart_item_arr ) {
			$this->handle_pricing( $cart_item_arr );
		}
	}

	/**
	 * Filter Cart item Product Price.
	 *
	 * @param \WC_Product $_product
	 * @param array       $cart_item
	 * @param string      $cart_item_key
	 * @return \WC_Product
	 */
	public function filter_cart_item_product_price( $_product, $cart_item, $cart_item_key ) {
		$this->handle_pricing( $cart_item );
		return $_product;
	}

	/**
	 * Handle Pricing.
	 *
	 * @param array $cart_item
	 * @return void
	 */
	private function handle_pricing( &$cart_item ) {
		// 1) Get Cart Item product ID.
		$cart_item_product_id = $this->get_cart_item_id( $cart_item );

		// 2) Check if there a pricing is enabled.
		$is_enabled = $this->is_pricing_enabled( $cart_item['data'] );
		if ( ! $is_enabled ) {
			return;
		}

		// It's variation and follows the parent Variable product.
		if ( is_numeric( $is_enabled ) ) {
			$cart_item_product_id = $is_enabled;
		}

		// Check if custom price in Advanced Add To Cart.
		if ( ! empty( $cart_item[ self::$plugin_info['related_plugins']['woo_advanced_add_to_cart']['prefix'] . '-has-custom-price' ] ) ) {
			return;
		}

		// ) Get Product Selected Pricing model Class.
		$pricing_model = $this->get_product_pricing_model( $cart_item_product_id );

		if ( isset( $pricing_model ) && ! is_null( $pricing_model ) ) {
			// ) Get Pricing Table.
			$pricing_table = $this->get_product_pricing_table( $cart_item_product_id );

			// ) Calculate Price based on the model.
			$pricing_model->calculate_price( $cart_item, $pricing_table );
		}
	}
}
