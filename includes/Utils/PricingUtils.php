<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Utils;

/**
 * Pricing Modules Utils Functions.
 */
trait PricingUtils {

	/**
	 * Check if the Pricing Table is matching the current Cart item Quantity.
	 *
	 * @param int   $quantity
	 * @param array $pricing_table
	 * @return boolean
	 */
	protected function is_pricing_table_applicable( $quantity, $pricing_table ) {
		if ( empty( $pricing_table ) ) {
			return false;
		}
		return ( $quantity >= min( array_column( $pricing_table, 'quantity' ) ) );
	}

	/**
	 * Sort Pricing Table.
	 *
	 * @param array  $pricing_table
	 * @param string $sort_base
	 * @param string $order
	 * @return array
	 */
	protected function sort_pricing_table( $pricing_table, $sort_base = 'quantity', $order = 'asc' ) {
		usort(
			$pricing_table,
			function( $a, $b ) use ( $order, $sort_base ) {
				if ( 'asc' === $order ) {
					return $a[ $sort_base ] - $b[ $sort_base ];
				} else {
					return $b[ $sort_base ] - $a[ $sort_base ];
				}
			}
		);

		return $pricing_table;
	}

	/**
	 * Pricing Quantity Swatches HTML.
	 *
	 * @param array        $pricing_table
	 * @param \WC_Product  $product
	 * @param MainSettings $main_settings
	 * @return void
	 */
	protected function pricing_quantity_swatches_html( $pricing_table, $product, $main_settings ) {
		$product_price           = (float) $product->get_price();
		$product_id              = $product->get_id();
		$has_default_swatch      = $this->product_has_default_swatch( $product_id ) ? $this->get_product_default_swatch( $product_id ) : 1;
		$pricing_model           = $this->get_product_pricing_model( $product_id );
		$is_full_package_pricing = $this->is_full_package_pricing( $product_id );
		if ( is_null( $pricing_model ) ) {
			return;
		}

		$swatch_template = $main_settings->get_settings( 'swatch_template' );
		if ( ! $this->is_pricing_table_has_starting_qty( $pricing_table ) ) {
			array_unshift(
				$pricing_table,
				array(
					'price'    => $product_price,
					'quantity' => 1,
				)
			);
		}
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatches' ); ?>">
			<?php
			foreach ( $pricing_table as $index => $pricing_row ) {
				$pricing_row['price'] = (float) $pricing_row['price'];
				$original_price       = $is_full_package_pricing ? $product_price : ( $product_price * $pricing_row['quantity'] );
				$after_price          = $pricing_model->_calculate_price( $pricing_row['quantity'], $pricing_table, $product_id, true );
				$low_price            = $original_price > $after_price ? $after_price : $original_price;
				$big_price            = $original_price > $after_price ? $original_price : $after_price;

				if ( $big_price !== $low_price ) {
					$swatch_price = wc_format_sale_price( $big_price, $low_price );
				} else {
					$swatch_price = wc_price( $after_price );
				}

				$swatch_price = apply_filters( self::$plugin_info['prefix'] . '-swatch-price', $swatch_price, $product_id );

				$args = array(
					'plugin_info'        => self::$plugin_info,
					'has_default_swatch' => $has_default_swatch,
					'product'            => $product,
					'pricing_row'        => $pricing_row,
					'product_price'      => $product_price,
					'swatch_price'       => $swatch_price,
				);

				$args['discount'] = '';

				if ( $big_price !== $low_price ) {
					$discount_value = wc_round_discount( ( ( $big_price - $low_price ) / $big_price ) * 100, 0 );
					// translators: %s Quantity swatches save percentage.
					$args['discount'] = sprintf( '%s %d%%', esc_html__( 'Save', 'advanced-pricing-for-woocommerce' ), $discount_value );
					$args['discount'] = apply_filters( self::$plugin_info['prefix'] . '-swatch-discount', $args['discount'], $pricing_row, $discount_value, $product_id );
				} else {
					$discount_value = 0;
				}

				$args['discount']     = apply_filters( self::$plugin_info['prefix'] . '-swatch-discount', $args['discount'], $pricing_row, $discount_value, $product_id );
				$swatch_template_path = apply_filters( self::$plugin_info['prefix'] . '-swatch-template', self::$plugin_info['path'] . 'includes/Templates/swatches/template-' . $swatch_template . '.php', $product_id );
				load_template(
					$swatch_template_path,
					false,
					array( 'args' => $args )
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Check if a pricing table has 1 quantity condition.
	 *
	 * @param array $pricing_table
	 * @return boolean
	 */
	protected function is_pricing_table_has_starting_qty( $pricing_table ) {
		foreach ( $pricing_table as $index => $pricing_row ) {
			if ( 1 === $pricing_row['quantity'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if inside single product add to cart form.
	 *
	 * @return boolean
	 */
	protected function single_product_add_to_cart_form() {
		return ( did_action( 'woocommerce_before_add_to_cart_form' ) > did_action( 'woocommerce_after_add_to_cart_form' ) );
	}

	/**
	 * Check if inside Variable Add To Cart Form.
	 *
	 * @return boolean
	 */
	protected function is_variable_add_to_cart_form() {
		return ( did_action( 'woocommerce_before_single_variation' ) > did_action( 'woocommerce_after_single_variation' ) );
	}
}
