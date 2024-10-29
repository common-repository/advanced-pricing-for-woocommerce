<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Utils;

/**
 * Cart related Functions.
 */
trait CartUtils {

	/**
	 * Get Cart.
	 *
	 * @return \WC_Cart
	 */
	protected function get_cart() {
		return WC()->cart;
	}

	/**
	 * Get Cart Item.
	 *
	 * @return  array
	 */
	protected function get_cart_item( $cart_item_key ) {
		return $this->get_cart_contents()[ $cart_item_key ];
	}

	/**
	 * Get Cart Item Product ID.
	 *
	 * @param string|array $cart_item_arr
	 * @return int
	 */
	protected function get_cart_item_id( $cart_item_arr ) {
		if ( is_string( $cart_item_arr ) ) {
			$cart_item_arr = $this->get_cart_item( $cart_item_arr );
		}
		return $cart_item_arr['variation_id'] ? $cart_item_arr['variation_id'] : $cart_item_arr['product_id'];
	}


	/**
	 * Get Cart Total.
	 *
	 * @return mixed
	 */
	protected function get_cart_total() {
		return $this->get_cart()->get_total( 'edit' );
	}

	/**
	 * Get Cart Contents.
	 *
	 * @return array
	 */
	protected function get_cart_contents() {
		return WC()->cart->get_cart();
	}

	/**
	 * Get Cart total Count.
	 *
	 * @return int
	 */
	protected function get_cart_total_qty() {
		$cart = $this->get_cart();
		return $cart->get_cart_contents_count();
	}

	/**
	 * Get Products IDs from Cart.
	 *
	 * @param string $cart_item_key
	 * @return array
	 */
	protected function resolve_product_ids_from_cart( $cart_item_key = null, $include_full = false ) {
		$target_cart_items = is_null( $cart_item_key ) ? array_values( $this->get_cart_contents() ) : array_fill_keys( array( $cart_item_key ), $this->get_cart_contents()[ $cart_item_key ] );
		$result            = array();
		foreach ( $target_cart_items as $cart_item_arr ) {
			$product_id            = $cart_item_arr['variation_id'] ? $cart_item_arr['variation_id'] : $cart_item_arr['product_id'];
			$result[ $product_id ] = $cart_item_arr;
		}

		return $include_full ? $result : array_keys( $result );
	}

	/**
	 * Empty The Cart.
	 *
	 * @return void
	 */
	protected function empty_the_cart() {
		$this->get_cart()->empty_cart( true );
	}

	/**
	 * Get Cart Items By Products IDs [ Resolving Variables ].
	 *
	 * @param array $products_ids
	 * @return array
	 */
	protected function get_cart_items_by_ids( $products_ids ) {
		$filtered_cart_items = array();
		$cart_items          = $this->resolve_product_ids_from_cart( null, true );
		foreach ( $cart_items as $product_id => $cart_item_arr ) {
			if ( array_intersect( $products_ids, array_filter( array( $cart_item_arr['variation_id'], $cart_item_arr['product_id'] ) ) ) ) {
				$filtered_cart_items[ $product_id ] = $cart_item_arr;
			}
		}
		return $filtered_cart_items;
	}

	/**
	 * Get All Cart Items IDs [ Resolving Variable ].
	 *
	 * @return array
	 */
	protected function resolve_cart_items_ids( $cart_item_key = null, $return_unique = true ) {
		$all_ids    = array();
		$cart_items = is_null( $cart_item_key ) ? $this->get_cart_contents() : array( $this->get_cart_item( $cart_item_key ) );

		foreach ( $cart_items as $cart_item_key => $cart_item_arr ) {
			if ( $cart_item_arr['variation_id'] ) {
				$all_ids[] = $cart_item_arr['variation_id'];
			}
			$all_ids[] = $cart_item_arr['product_id'];
		}

		return $return_unique ? array_unique( $all_ids ) : $all_ids;
	}

	/**
	 * Get Cart Items Keys.
	 *
	 * @return array
	 */
	protected function get_cart_keys() {
		return array_keys( $this->get_cart_contents() );
	}

	/**
	 * Get Cart items Keys from cart items inside matched conditions ['cart_items'].
	 *
	 * @param array $cart_items   array( product_id => cart_item_arr, .... ).
	 * @return array
	 */
	protected function get_cart_items_keys_after_matching( $cart_items ) {
		// foreach ( $cart_items as $product_id => $cart_items ) {

		// }
	}

}
