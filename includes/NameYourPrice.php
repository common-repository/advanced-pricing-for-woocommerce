<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\CartUtils;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingUtils;

/**
 * Name Your Price Pricing Model Class.
 */
class NameYourPrice extends PricingBase {
	use CartUtils, PricingUtils;

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Name your price value.
	 *
	 * @var mixed
	 */
	private $name_your_price_val = null;

	/**
	 * Name your price input field name.
	 *
	 * @var string
	 */
	private $name_your_price_name_field = '';

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
	private function setup() {
		$this->name_your_price_name_field = str_replace( '-', '_', self::$plugin_info['prefix'] . '-name-your-price' );
	}

	/**
	 * Get Name your price field name.
	 *
	 * @return string
	 */
	public function get_name_your_price_field_name() {
		return $this->name_your_price_name_field;
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'name_your_price_input_loop' ), PHP_INT_MAX, 3 );
		add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'name_your_price_input_single' ), 10 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_product_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_cart_id', array( $this, 'filter_cart_item_key' ), PHP_INT_MAX, 5 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'set_cart_items_price' ), PHP_INT_MAX, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'adjust_cart_items_price' ), PHP_INT_MAX, 1 );
		add_action( 'woocommerce_before_mini_cart_contents', array( $this, 'adjust_cart_items_price_mini' ), 100 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_name_your_price_products' ), PHP_INT_MAX, 4 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'filter_loop_add_to_cart_url' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'filter_loop_add_to_cart_args' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'variation_name_your_price_field' ), PHP_INT_MAX, 3 );
	}

	/**
	 * Variation Pricing Table HTML after Variation Summary.
	 *
	 * @param array                 $variation_data
	 * @param \WC_Product_Variable  $variable_parent_product
	 * @param \WC_Product_Variation $variation_product
	 * @return array
	 */
	public function variation_name_your_price_field( $variation_data, $variable_parent_product, $variation_product ) {
		$is_name_your_price = $this->is_name_your_price_product( $variation_product->get_id() );
		if ( ! $is_name_your_price ) {
			return $variation_data;
		}

		$product_id = $variation_product->get_id();

		if ( $this->is_variation_has_own_pricing( $product_id ) ) {
			$variation_data['variation_description'] .= $this->name_your_price_field( $variation_product, 0, false, 'single' );
		} else {
			$product_id = $is_name_your_price;
		}

		if ( $this->is_hide_original_price_for_name_your_price( $product_id ) ) {
			$variation_data['price_html'] = '';
		}

		$variation_data[ self::$plugin_info['prefix'] . '-name-your-price' ] = true;

		return $variation_data;
	}

	/**
	 * Filter Add to Cart Arguments.
	 *
	 * @param array       $args
	 * @param \WC_Product $product
	 * @return array
	 */
	public function filter_loop_add_to_cart_args( $args, $product ) {
		if ( $this->is_name_your_price_product( $product->get_id() ) ) {
			$name_your_price = $this->get_name_your_pricing( $product->get_id() );
			$default_price   = $name_your_price['price_default'];
			$args['attributes'][ 'data-' . $this->name_your_price_name_field . '_' . $product->get_id() ] = $default_price;
		}
		return $args;
	}

	/**
	 * Filter Add To Cart URL.
	 *
	 * @param string      $url
	 * @param \WC_Product $product
	 * @return string
	 */
	public function filter_loop_add_to_cart_url( $url, $product ) {
		if ( $this->is_name_your_price_product( $product->get_id() ) ) {
			$name_your_price = $this->get_name_your_pricing( $product->get_id() );
			$default_price   = $name_your_price['price_default'];
			$url             = add_query_arg(
				array(
					$this->name_your_price_name_field . '_' . $product->get_id() => $default_price,
				),
				$url
			);
		}
		return $url;
	}

	/**
	 * Filter Cart Item Key.
	 *
	 * @param string      $cart_item_key
	 * @param int         $product_id
	 * @param int         $variation_id
	 * @param \WC_Product $variation
	 * @param array       $cart_item_data
	 * @return string
	 */
	public function filter_cart_item_key( $cart_item_key, $product_id, $variation_id, $variation, $cart_item_data ) {
		$_product_id = ! empty( $variation_id ) ? $variation_id : $product_id;
		if ( $this->is_name_your_price_product( $_product_id ) & isset( $this->name_your_price_val ) && is_numeric( $this->name_your_price_val ) ) {
			$cart_item_key .= '-' . hash( 'crc32b', (string) $this->name_your_price_val );
		}
		return $cart_item_key;
	}

	/**
	 * Filter Product Price HTML.
	 *
	 * @param string      $price
	 * @param \WC_Product $product
	 * @return string
	 */
	public function filter_product_price( $price, $product ) {
		if ( $this->is_hide_original_price_for_name_your_price( $product->get_id() ) ) {
			$price = '';
		}
		return $price;
	}


	/**
	 * Name Your Price input field for single product page.
	 *
	 * @return void
	 */
	public function name_your_price_input_single() {
		$this->name_your_price_input( 0, 'single' );
	}

	/**
	 * Name your price input field for product page in shop and archive.
	 *
	 * @param string $add_to_cart_btn
	 * @param \WC_Product $product
	 * @param array $args
	 * @return string
	 */
	public function name_your_price_input_loop( $add_to_cart_btn, $product, $args ) {
		ob_start();
		$this->name_your_price_input( $product->get_id(), 'loop' );
		$add_to_cart_btn = ob_get_clean() . $add_to_cart_btn;
		return $add_to_cart_btn;
	}

	/**
	 * Name your price field.
	 *
	 * @return void
	 */
	public function name_your_price_input( $product_id = 0, $context = 'single' ) {
		if ( ! $product_id ) {
				global $post, $product;
				if ( ! is_a( $post, '\WP_Post' ) ) {
					return;
				}
				$product = wc_get_product( $post->ID );
				if ( ! is_a( $product, '\WC_Product' ) ) {
					return;
				}
				$product_id = $product->get_id();
		}

		if ( ! $this->is_name_your_price_product( $product_id ) ) {
				return;
		}

		$this->name_your_price_field( $product_id, 0, true, $context );
	}

	/**
	 * Adjust Cart Items Price.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function set_cart_items_price( $cart_item_arr, $cart_item_key ) {
		$product_id         = ! empty( $cart_item_arr['variation_id'] ) ? $cart_item_arr['variation_id'] : $cart_item_arr['product_id'];
		$is_name_your_price = $this->is_name_your_price_product( $product_id );
		if ( ! $is_name_your_price ) {
			return $cart_item_arr;
		}

		if ( is_numeric( $is_name_your_price ) ) {
			$product_id = $is_name_your_price;
		}

		if ( isset( $this->name_your_price_val ) && is_numeric( $this->name_your_price_val ) ) {
			$cart_item_arr['data']->set_price( $this->name_your_price_val );
			$cart_item_arr[ self::$plugin_info['prefix'] . '-already-set-price' ] = $this->name_your_price_val;
			$this->name_your_price_val                                            = null;
		}
		return $cart_item_arr;
	}

	/**
	 * Adjust Cart Items Pricec in Mini cart.
	 *
	 * @return void
	 */
	public function adjust_cart_items_price_mini() {
		$this->adjust_cart_items_price( WC()->cart );
	}

	/**
	 * Adjust Cart Items Price.
	 *
	 * @param \WC_Cart $cart
	 * @return void
	 */
	public function adjust_cart_items_price( $cart ) {
		foreach ( $cart->cart_contents as $cart_item_key => $cart_item_arr ) {
			$product_id         = ! empty( $cart_item_arr['variation_id'] ) ? $cart_item_arr['variation_id'] : $cart_item_arr['product_id'];
			$is_name_your_price = $this->is_name_your_price_product( $product_id );
			if ( ! $is_name_your_price ) {
				continue;
			}

			if ( is_numeric( $is_name_your_price ) ) {
				$product_id = $is_name_your_price;
			}

			if ( isset( $cart_item_arr[ self::$plugin_info['prefix'] . '-already-set-price' ] ) ) {
				$cart_item_arr['data']->set_price( $cart_item_arr[ self::$plugin_info['prefix'] . '-already-set-price' ] );
			}
		}
	}

	/**
	 * Pricing Table HTML.
	 *
	 * @param int|\WC_Product $product_id
	 * @return mixed
	 */
	public function name_your_price_field( $product_id, $variation_id = 0, $echo = false, $context = 'single' ) {
		if ( is_int( $product_id ) ) {
			$_product = wc_get_product( $product_id );
		} else {
			$_product   = $product_id;
			$product_id = $_product->get_id();
		}

		if ( ! is_a( $_product, '\WC_Product' ) ) {
			return '';
		}

		if ( ( 'loop' === $context ) && ! is_a( $_product, \WC_Product_Simple::class ) ) {
			return '';
		}

		if ( ! $echo ) {
			ob_start();
		}
		$fields        = $this->get_name_your_pricing( $product_id );
		$default_price = number_format( (float) $fields['price_default'], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
		$min_price     = ( ! empty( $fields['min_price'] ) && is_numeric( $fields['min_price'] ) ) ? number_format( (float) $fields['min_price'], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) : '';
		$max_price     = ! empty( $fields['max_price'] ) ? number_format( (float) $fields['max_price'], wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) : '';
		$step          = 1 / pow( 10, wc_get_price_decimals() );
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price ' . self::$plugin_info['prefix'] . '-name-your-price-' . $context . ' ' . self::$plugin_info['prefix'] . '-name-your-price-' . $_product->get_type() ); ?>">
			<label class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price-label' ); ?>" for="<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price-field-' . ( $variation_id ? $variation_id : $product_id ) ); ?>">
									 <?php
										esc_html_e( 'Name your Price', 'advanced-pricing-for-woocommerce' );
										echo esc_html( ' (' . get_woocommerce_currency_symbol() . ')' );
										?>
			</label>
			<input inputmode="decimal" step="<?php echo esc_attr( $step ); ?>" data-context="<?php echo esc_attr( $context ); ?>" name="<?php echo esc_attr( $this->name_your_price_name_field . '_' . ( $variation_id ? $variation_id : $product_id ) ); ?>" data-product_id="<?php echo esc_attr( $variation_id ? $variation_id : $product_id ); ?>" size="4" type="number" id="<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price-field-' . ( $variation_id ? $variation_id : $product_id ) ); ?>" class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price-field' ); ?>" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" value="<?php echo esc_attr( $default_price ); ?>" title="<?php esc_html_e( 'Name your Price', 'advanced-pricing-for-woocommerce' ); ?>"  >
		</div>
		<?php
		$GLOBALS[ self::$plugin_info['prefix'] . '-name-your-price-field' ] = true;
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get Name your Price Pricing
	 *
	 * @param int $product_id
	 * @return array
	 */
	private function get_name_your_pricing( $product_id ) {
		return array(
			'min_price'     => $this->get_settings_key( 'name_your_price_min_price', $product_id ),
			'price_default' => $this->get_settings_key( 'name_your_price_default_price', $product_id ),
			'max_price'     => $this->get_settings_key( 'name_your_price_max_price', $product_id ),
		);
	}

	/**
	 * Validate Name your price Products.
	 *
	 * @param boolean $validation
	 * @param int     $product_id
	 * @param int     $quantity
	 * @return boolean
	 */
	public function validate_name_your_price_products( $validation, $product_id, $quantity, $variation_id = null ) {
		$is_name_your_price = $this->is_name_your_price_product( $variation_id ? $variation_id : $product_id );
		if ( ! $is_name_your_price ) {
			return $validation;
		}

		$_product_id = $variation_id ? $variation_id : $product_id;

		if ( is_numeric( $is_name_your_price ) ) {
			$_product_id = $is_name_your_price;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_REQUEST[ $this->name_your_price_name_field . '_' . $_product_id ] ) && is_numeric( $_REQUEST[ $this->name_your_price_name_field . '_' . $_product_id ] ) ) {
			$product_price           = wc_add_number_precision( wc_remove_number_precision( (float) sanitize_text_field( wp_unslash( $_REQUEST[ $this->name_your_price_name_field . '_' . $_product_id ] ) ) ) );  // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product_price           = $product_price < 0 ? ( $product_price * -1 ) : $product_price;
			$name_your_price_configs = $this->get_name_your_pricing( $_product_id );

			if ( ! empty( $name_your_price_configs['min_price'] ) && ( $name_your_price_configs['min_price'] > $product_price ) ) {
				$product = wc_get_product( $_product_id );
				/* translators: %1$s: Product name %2$d: Product name-your-price minimum price */
				wc_add_notice( sprintf( __( 'Minimum price allowed of %1$s is %2$s', 'gpls-waadtct-woo-advanced-add-to-cart' ), $product->get_name(), wc_price( $name_your_price_configs['min_price'] ) ), 'error' );
				$validation = false;
			}

			if ( ! empty( $name_your_price_configs['max_price'] ) && ( $product_price > $name_your_price_configs['max_price'] ) ) {
				$product = wc_get_product( $_product_id );
				/* translators: %1$s: Product name %2$d: Product name-your-price maximum price */
				wc_add_notice( sprintf( __( 'Maximum price allowed of %1$s is %2$s', 'gpls-waadtct-woo-advanced-add-to-cart' ), $product->get_name(), wc_price( $name_your_price_configs['max_price'] ) ), 'error' );
				$validation = false;
			}

			$this->name_your_price_val = $product_price;
		}

		return $validation;
	}
}
