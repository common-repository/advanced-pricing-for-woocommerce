<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\MainSettings;
use GPLSCore\GPLS_PLUGIN_WOOADPG\NameYourPrice;

/**
 * Pricing Frontend Related Class.
 */
class PricingFront extends PricingBase {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Main Settings
	 *
	 * @var MainSettings
	 */
	private $main_settings;

	/**
	 * Name Your Price Class.
	 *
	 * @var NameYourPrice
	 */
	private $name_your_price;

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
		$this->main_settings   = MainSettings::init();
		$this->name_your_price = NameYourPrice::init();
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'pricing_shortcodes' ) );
		// Filter Product price HTML.
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_product_price' ), PHP_INT_MAX, 2 );
		// Show Pricing Table.
		add_action( 'woocommerce_single_product_summary', array( $this, 'product_pricing_table_html' ), 29 );
		// Pricing Table Styles.
		add_action( 'wp_footer', array( $this, 'pricing_table_styles' ), 18 );
		// Quantity Swatches.
		add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'pricing_quantity_swatches' ), 100 );
		// Quantity field filter for Quantity swatches.
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'filter_qty_field_for_quantity_swatches' ), PHP_INT_MAX, 2 );

		add_filter( self::$plugin_info['prefix'] . '-quantity-swatch-qty', array( $this, 'filter_swatch_qty' ), 10, 3 );
		add_filter( self::$plugin_info['prefix'] . '-swatch-price', array( $this, 'filter_swatch_pricing' ), 10, 2 );

		add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'variation_quantity_swatches_placeholder' ) );
		add_action( 'wp_footer', array( $this, 'front_assets' ), 18 );
	}

	/**
	 * Filter Swatch price.
	 *
	 * @param string $swatch_price
	 * @param int $product_id
	 * @return string
	 */
	public function filter_swatch_pricing( $swatch_price, $product_id ) {
		$_product = wc_get_product( $product_id );
		// Remove swatch price if the product is variable.
		if ( is_a( $_product, '\WC_Product_Variable' ) ) {
			$swatch_price = '';
		}
		return $swatch_price;
	}

	/**
	 * Filter Swatch Quantity for Package Pricing with full package pricing.
	 *
	 * @return string
	 */
	public function filter_swatch_qty( $qty_html, $pricing_table, $product_id ) {
		// Disable Qty * Price per unit for package pricing model with full package pricing or tier pricing model.
		if ( $this->is_full_package_pricing( $product_id ) || ( $this->is_product_pricing_model( $product_id, 'tier' ) ) ) {
			$qty_html = 'x' . $pricing_table['quantity'];
		}
		// Disable Price per unit for variable product.
		$_product = wc_get_product( $product_id );
		if ( is_a( $_product, '\WC_Product_Variable' ) ) {
			$qty_html = 'x' . $pricing_table['quantity'];
		}
		return $qty_html;
	}

	/**
	 * Variation Quantity Swatches Placeholder.
	 *
	 * @return void
	 */
	public function variation_quantity_swatches_placeholder() {
		if ( ! $this->is_variable_add_to_cart_form() ) {
			return;
		}
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-variation-quantity-swatches' ); ?>" ></div>
		<?php
	}

	/**
	 * Get Pricing Quantity Swatches.
	 *
	 * @param \WC_Product $product
	 * @return string
	 */
	public function get_pricing_quantity_swatches( $product ) {
		ob_start();
		$this->pricing_quantity_swatches( $product );
		return ob_get_clean();
	}

	/**
	 * Frontend Quantity Swatches.
	 *
	 * @param \WC_Product $product
	 * @return mixed
	 */
	public function pricing_quantity_swatches( $product = null ) {
		if ( ! is_a( $product, '\WC_Product' ) ) {
			global $product;
		}
		if ( ! is_a( $product, '\WC_Product' ) ) {
			return '';
		}

		$product_id = $product->get_id();
		if ( ! $this->is_product_quantity_swatches( $product_id ) ) {
			return '';
		}

		$pricing_table = $this->get_product_pricing_table( $product_id, true );
		if ( empty( $pricing_table ) ) {
			return '';
		}

		$GLOBALS[ self::$plugin_info['prefix'] . '-quantity-swatches' ] = true;

		$this->pricing_quantity_swatches_html( $pricing_table, $product, $this->main_settings );
	}

	/**
	 * Filter Quantity field arguments to hide and setting quantity for quantity swatches.
	 *
	 * @param array       $args
	 * @param \WC_Product $product
	 * @return array
	 */
	public function filter_qty_field_for_quantity_swatches( $args, $product ) {
		// Qty input class for swatches select.
		$args['classes'][] = self::$plugin_info['prefix'] . '-qty-swatch';

		if ( ! $this->single_product_add_to_cart_form() ) {
			return $args;
		}
		if ( ! $this->is_product_quantity_swatches( $product->get_id() ) ) {
			return $args;
		}

		// Qty input field default value.
		if ( $this->product_has_default_swatch( $product->get_id() ) ) {
			$args['input_value'] = $this->get_product_default_swatch( $product->get_id() );
		}

		return $args;
	}

	/**
	 * Pricing Shortcodes.
	 *
	 * @return void
	 */
	public function pricing_shortcodes() {
		add_shortcode( str_replace( '-', '_', self::$plugin_info['prefix'] . '-pricing-table' ), array( $this, 'pricing_table_shortcode' ) );
	}

	/**
	 * Pricing Table Shortcode.
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function pricing_table_shortcode( $attrs ) {
		global $product;
		if ( ! is_a( $product, '\WC_Product' ) ) {
			return '';
		}

		return $this->get_product_pricing_table_html( $product->get_id() );
	}

	/**
	 * WooCommerce Filter Price HTML.
	 *
	 * @param string      $price
	 * @param \WC_Product $_product
	 * @return string
	 */
	public function filter_product_price( $price, $_product ) {
		$base_advanced_price = $this->get_custom_base_price( $_product );
		if ( false !== $base_advanced_price ) {
			$price = wc_price(
				wc_get_price_to_display(
					$_product,
					array(
						'qty'   => 1,
						'price' => $base_advanced_price,
					)
				)
			) . $_product->get_price_suffix();
		}

		return $price;
	}

	/**
	 * Return Custom Pricing For Quantity 1.
	 *
	 * @param \WC_Product $product
	 * @return string|false
	 */
	private function get_custom_base_price( $product ) {
		$is_enabled = $this->is_product_advanced_pricing_enabled( $product );
		if ( ! $is_enabled ) {
			return false;
		}

		$product_id = $product->get_id();

		if ( is_numeric( $is_enabled ) ) {
			$product_id = $is_enabled;
		}

		// 1) Get pricing tables.
		$pricing_type          = $this->get_pricing_type( $product_id );
		$pricing_table_handler = $this->pricing_table_mapping( $pricing_type );
		if ( is_null( $pricing_table_handler ) ) {
			return false;
		}

		$pricing_table_start = $pricing_table_handler->get_table_base( $product_id );
		if ( empty( $pricing_table_start ) ) {
			return false;
		}

		if ( 1 !== $pricing_table_start['quantity'] ) {
			return false;
		}

		return $pricing_table_start['price'];
	}

	/**
	 * Product Pricing Table HTML after Product Summary.
	 *
	 * @return void
	 */
	public function product_pricing_table_html() {
		global $product;
		if ( ! $product ) {
			return;
		}

		$this->get_product_pricing_table_html( $product->get_id(), true );
	}

	/**
	 * Product Pricing table HTML.
	 *
	 * @param int     $product_id
	 * @param boolean $echo
	 * @return mixed
	 */
	public function get_product_pricing_table_html( $product_id, $echo = false ) {
		if ( ! $this->is_product_advanced_pricing_enabled( $product_id ) ) {
			return '';
		}

		if ( ! $this->get_pricing_table_status( $product_id ) ) {
			return '';
		}

		$pricing_model = $this->get_product_pricing_model( $product_id );
		if ( is_null( $pricing_model ) ) {
			return '';
		}

		$GLOBALS[ self::$plugin_info['prefix'] . '-pricing-table' ] = true;

		if ( ! $echo ) {
			return $pricing_model->pricing_table_html( $product_id, $echo );
		}

		$pricing_model->pricing_table_html( $product_id, $echo );
	}

	/**
	 * Pricing Table Styles.
	 *
	 * @return void
	 */
	public function pricing_table_styles() {
		if ( ! $this->assets_needed() ) {
			return;
		}
		?>
		<style>
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-d-none' ); ?> {display: none;}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> {
				display: table;
				border-spacing: 0;
				width: 100%;
				margin: 0 0 1.41575em;
				border-collapse: separate;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> thead {
				display: table-header-group;
				vertical-align: middle;
				border-color: inherit;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> tr {
				display: table-row;
				vertical-align: inherit;
				border-color: inherit;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> th {
				background-color: #f8f8f8;
				padding: 1.41575em;
				vertical-align: middle;
				font-weight: 600;
				text-align: left;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> tbody {
				display: table-row-group;
				vertical-align: middle;
				border-color: inherit;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> td {
				background-color: #fdfdfd;
				padding: 1em 1.41575em;
				text-align: left;
				vertical-align: top;
				display: table-cell;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-model-table' ); ?> tbody tr:nth-child(2n) td {
				background-color: #fbfbfb;
				padding: 1em 1.41575em;
				text-align: left;
				vertical-align: top;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatches' ); ?> {
				display: flex;
				clear: both;
				flex-wrap: wrap;
				flex-direction: row;
				justify-content: flex-start;
				margin: 15px auto;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch' ); ?> {
				display: flex;
				flex-direction: column;
				align-items: stretch;
				justify-content: center;
				cursor: pointer;
				font-weight: bold;
				margin: 1px;
				flex-grow: 1;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch' ); ?>.active .<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch__body' ); ?>{background-color: #009dff; color: #FFFFFF;}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch__header' ); ?> {
				align-self: stretch;
				text-align: center;
				background-color: #5175dc;
				color: #FFFFFF;
				padding: 5px
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price' ); ?> {
				display: flex;
				align-items: flex-start;;
				margin: 10px auto;
				flex-wrap: wrap;
				flex-direction: column;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-name-your-price-loop' ); ?> {
				align-items: center;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch__body' ); ?> {
				display: flex;
				flex-direction: column;
				align-items: center;
				flex-grow: 1;
				justify-content: center;
				padding: 15px 20px;
				background-color: #ececec;
				color: #000;
			}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch__body' ); ?> del {opacity: 0.6;margin-right:5px;}
			.<?php echo esc_attr( self::$plugin_info['prefix'] . '-quantity-swatch__body' ); ?> ins {font-weight: bold;}
		</style>
		<?php
	}

	/**
	 * Check if assets are needed.
	 *
	 * @return boolean
	 */
	private function assets_needed() {
		return (
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-quantity-swatches' ] )
			||
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-pricing-table' ] )
			||
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-name-your-price-field' ] )
			||
			$this->is_variable_product()
		);
	}

	/**
	 * Front Assets.
	 *
	 * @return void
	 */
	public function front_assets() {
		if ( ! $this->assets_needed() ) {
			return;
		}
		wp_enqueue_script( self::$plugin_info['prefix'] . '-front-actions', self::$plugin_info['url'] . 'assets/dist/js/front/actions.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
		wp_localize_script(
			self::$plugin_info['prefix'] . '-front-actions',
			str_replace( '-', '_', self::$plugin_info['prefix'] . '-localize-vars' ),
			array(
				'prefix'  => self::$plugin_info['prefix'],
				'classes' => array(
					'nameYourPriceWrapper'            => self::$plugin_info['prefix'] . '-name-your-price',
					'nameYourPriceField'              => self::$plugin_info['prefix'] . '-name-your-price-field',
					'nameYourPriceFieldLoop'          => self::$plugin_info['prefix'] . '-name-your-price-field-loop',
					'quantitySwatch'                  => self::$plugin_info['prefix'] . '-quantity-swatch',
					'qtySwatchField'                  => self::$plugin_info['prefix'] . '-qty-swatch',
					'variationQtySwatchesPlaceholder' => self::$plugin_info['prefix'] . '-variation-quantity-swatches',
					'quantitySwatchesWrapper'         => self::$plugin_info['prefix'] . '-quantity-swatches',
				),
				'keys'    => array(
					'nameYourPriceKey'            => $this->name_your_price->get_name_your_price_field_name(),
					'hideQtySwatchField'          => self::$plugin_info['prefix'] . '-quantity-swatches-hide-qty-field',
					'defaultSwatch'               => self::$plugin_info['prefix'] . '-quantity-swatches-default',
					'followsParent'               => self::$plugin_info['prefix'] . '-follows-parent',
					'variationHasOwnPricingModel' => self::$plugin_info['prefix'] . '-has-own-pricing-model',
				),
				'configs' => array(
					'currency_symbol'    => get_woocommerce_currency_symbol(),
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
				),
			)
		);
	}

	/**
	 * Is variable product page.
	 *
	 * @return boolean
	 */
	private function is_variable_product() {
		if ( ! is_product() ) {
			return false;
		}
		global $product;
		if ( 'variable' !== $product->get_type() ) {
			return false;
		}
		return true;
	}
}
