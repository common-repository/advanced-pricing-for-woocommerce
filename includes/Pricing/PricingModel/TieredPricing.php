<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\PricingModelInterface;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\CartUtils;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingUtils;

/**
 * Tiered Pricing Model Class.
 */
class TieredPricing extends PricingBase implements PricingModelInterface {
	use CartUtils, PricingUtils;

	/**
	 * Get Pricing model name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tier';
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

		$cart_item['data']->set_price( $this->_calculate_price( $cart_item['quantity'], $pricing_table, $this->get_cart_item_id( $cart_item ) ) );
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
		$total_price = 0;
		$_product    = wc_get_product( $product_id );
		$base_price  = (float) $_product->get_price();

		// Loop over the Cart item Quantity => Get the target Price.
		for ( $i = 1; $i <= $quantity; $i++ ) {
			$target_pricing_division = $this->get_pricing_division( $pricing_table, $i );
			if ( is_null( $target_pricing_division ) ) {
				$total_price += $base_price;
			} else {
				$total_price += $target_pricing_division['price'];
			}
		}

		return ( $return_total_price ? $total_price : ( $total_price / $quantity ) );
	}

	/**
	 * Get the current Quantity counter position in the pricing table quantities division.
	 *
	 * @param array $pricing_table
	 * @param int   $quantity_counter
	 * @return array|null Pricing Row.
	 */
	private function get_pricing_division( $pricing_table, $quantity_counter ) {
		$target_pricing_row = null;
		$closest_qty        = 0;
		foreach ( $pricing_table as $pricing_row ) {
			if ( $pricing_row['quantity'] === $quantity_counter ) {
				$target_pricing_row = $pricing_row;
				break;
			}

			if ( ( $quantity_counter > $pricing_row['quantity'] ) && ( $pricing_row['quantity'] > $closest_qty ) ) {
				$closest_qty        = $pricing_row['quantity'];
				$target_pricing_row = $pricing_row;
			}
		}

		return $target_pricing_row;
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
				$base_price = $_product->get_price();
				foreach ( $pricing_table as $index => $pricing_row ) :
					$label = null;
					$price = $pricing_row['price'];
					if ( (float) $price < (float) $base_price ) {
						$price = wc_format_sale_price( wc_get_price_to_display( $_product, array( 'price' => $base_price ) ), wc_get_price_to_display( $_product, array( 'price' => $price ) ) ) . $_product->get_price_suffix();
					} else {
						$price = wc_price(
							wc_get_price_to_display(
								$_product,
								array(
									'qty'   => 1,
									'price' => $price,
								)
							)
						) . $_product->get_price_suffix();
					}
					// First Row.
					if ( 0 === $index ) {
						// First Quantity is not 1. Set Base price row.
						if ( 1 !== $pricing_row['quantity'] ) {
							$base_price_html = $_product->get_price_html();
							$qty             = $pricing_row['quantity'];
							$qty_threshold   = $qty - 1;
							$label           = '<strong>1</strong> ' . ( ( 1 < $qty_threshold ) ? ( esc_attr( '—' ) . ' <strong>' . esc_attr( $qty_threshold ) . '</strong>' ) : '' );
							?>
							<tr>
								<td><?php echo wp_kses_post( $label ); ?></td>
								<td class="price"><?php echo wp_kses_post( $base_price_html ); ?></td>
							</tr>
							<?php
							$price    = $pricing_row['price'];
							$qty      = $pricing_row['quantity'];
							$next_qty = $pricing_table[ $index + 1 ]['quantity'] - 1;
							$label    = '<strong>' . $qty . '</strong>' . esc_attr( '—' ) . ' <strong>' . $next_qty . '</strong>';
							if ( (float) $price < (float) $base_price ) {
								$price = wc_format_sale_price( wc_get_price_to_display( $_product, array( 'price' => $base_price ) ), wc_get_price_to_display( $_product, array( 'price' => $price ) ) ) . $_product->get_price_suffix();
							} else {
								$price = wc_price(
									wc_get_price_to_display(
										$_product,
										array(
											'qty'   => 1,
											'price' => $price,
										)
									)
								) . $_product->get_price_suffix();
							}
							?>
							<tr>
								<td><?php echo wp_kses_post( $label ); ?></td>
								<td class="price"><?php echo wp_kses_post( $price ); ?></td>
							</tr>
							<?php
							continue;
						}

						$qty   = $pricing_table[ $index + 1 ]['quantity'] - 1;
						$label = '<strong>1</strong> ' . ( 1 < $qty ) ? ( esc_attr( '—' ) . ' <strong>' . esc_attr( $qty ) . '</strong>' ) : '';
						$price = wc_price(
							wc_get_price_to_display(
								$_product,
								array(
									'qty'   => 1,
									'price' => $pricing_row['price'],
								)
							)
						) . $_product->get_price_suffix();

					} elseif ( ( count( $pricing_table ) - 1 ) === $index ) {
						// Last Row.
						$label = '<strong>' . $pricing_row['quantity'] . '</strong> ' . esc_html__( ' and more', 'advanced-pricing-for-woocommerce' );
					} else {
						// In-Between.
						$next_qty = $pricing_table[ $index + 1 ]['quantity'] - 1;
						$label    = '<strong>' . $pricing_row['quantity'] . '</strong> ' . esc_attr( '—' ) . ' <strong>' . $next_qty . '</strong>';
					}

					if ( is_null( $price ) || is_null( $label ) ) {
						continue;
					}
					?>
					<tr>
						<td><?php echo wp_kses_post( $label ); ?></td>
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
