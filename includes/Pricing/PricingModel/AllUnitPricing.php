<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\PricingModelInterface;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingUtils;

/**
 * All Unit Pricing Model Class.
 */
class AllUnitPricing extends PricingBase implements PricingModelInterface {

	/**
	 * Get Pricing model name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'allunit';
	}

	/**
	 * Validate Custom Pricing for a cart items.
	 *
	 * @param array $cart_item
	 * @param array $pricing_table
	 * @return void
	 */
	public function calculate_price( &$cart_item, $pricing_table ) {

		if ( ! $this->is_pricing_table_applicable( $cart_item['quantity'], $pricing_table ) ) {
			return;
		}

		$target_price = $this->_calculate_price( $cart_item['quantity'], $pricing_table, $this->get_cart_item_id( $cart_item ) );
		if ( -1 === $target_price ) {
			return;
		}

		$cart_item['data']->set_price( $target_price );
	}

	/**
	 * Calculate Pricing Model Function.
	 *
	 * @param int $quantity
	 * @param array $pricing_table
	 * @param int $product_id
	 * @return mixed
	 */
	public function _calculate_price( $quantity, $pricing_table, $product_id, $return_total_price = false ) {
		$price_per_qty = $this->get_target_price( $pricing_table, $quantity );
		return ( $return_total_price ? ( $price_per_qty * $quantity ) : $price_per_qty );
	}

	/**
	 * Get Matching Quantity Pricing table with Cart Item Quantity.
	 *
	 * @param array $pricing_table
	 * @param int   $item_qty
	 * @return int
	 */
	private function get_target_price( $pricing_table, $item_qty ) {
		$target_price          = -1;
		$subs                  = array();
		$lowest_val_in_pricing = min( array_column( $pricing_table, 'quantity' ) );

		if ( $item_qty < $lowest_val_in_pricing ) {
			return $target_price;
		}

		foreach ( $pricing_table as $pricing_row ) {
			if ( $pricing_row['quantity'] > $item_qty ) {
				continue;
			}

			if ( $item_qty === $pricing_row['quantity'] ) {
				return $pricing_row['price'];
			}

			$sub          = $item_qty - $pricing_row['quantity'];
			$subs[ $sub ] = $pricing_row['price'];
		}

		return empty( $subs ) ? array() : $subs[ min( array_keys( $subs ) ) ];
	}

	/**
	 * Pricing Table HTML.
	 *
	 * @param int|\WC_Product $product_id
	 * @return mixed
	 */
	public function pricing_table_html( $product_id, $echo = false ) {
		if ( is_int( $product_id ) ) {
			$_product = wc_get_product( $product_id );
		} else {
			$_product   = $product_id;
			$product_id = $_product->get_id();
		}

		$pricing_table = $this->get_product_pricing_table( $product_id, true );
		if ( empty( $pricing_table ) ) {
			return;
		}

		if ( ! $echo ) {
			ob_start();
		}
		?>
		<table class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?>" >
			<thead>
				<tr>
					<th><?php esc_html_e( 'Quantity', 'advanced-pricing-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Price per unit', 'advanced-pricing-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $pricing_table as $index => $pricing_row ) :
					// First Row.
					if ( 0 === $index ) {
						if ( 1 !== $pricing_row['quantity'] ) {
							$base_price_html = $_product->get_price_html();
							?>
							<tr>
								<td><strong>1</strong></td>
								<td class="price"><?php echo wp_kses_post( $base_price_html ); ?></td>
							</tr>
							<?php
						}
					}

					if ( (float) $pricing_row['price'] < (float) $_product->get_price() ) {
						$price = wc_format_sale_price( wc_get_price_to_display( $_product, array( 'price' => $_product->get_price() ) ), wc_get_price_to_display( $_product, array( 'price' => $pricing_row['price'] ) ) ) . $_product->get_price_suffix();
					} else {
						$price = wc_price(
							wc_get_price_to_display(
								$_product,
								array(
									'qty'   => 1,
									'price' => $pricing_row['price'],
								)
							)
						) . $_product->get_price_suffix();
					}
					?>
					<tr>
						<td><strong><?php echo esc_html( $pricing_row['quantity'] ); ?></strong></td>
						<td class="price"><?php echo wp_kses_post( $price ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}
}
