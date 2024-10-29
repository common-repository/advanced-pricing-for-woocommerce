<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\PricingSettings;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\PricingVariationSettings;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingBase;

/**
 * Pricing Product CPT related.
 */
class PricingCPT extends PricingBase {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Assets array.
	 *
	 * @var array
	 */
	private $assets = array();

	/**
	 * Variations Settings.
	 *
	 * @var PricingVariationSettings
	 */
	private $variations_settings;

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
		$this->setup();
		$this->hooks();
	}

	/**
	 * Setup.
	 *
	 * @return void
	 */
	private function setup() {
		$this->settings            = PricingSettings::init();
		$this->variations_settings = PricingVariationSettings::init();
		$this->assets   = array(
			array(
				'type'   => 'css',
				'handle' => self::$plugin_info['name'] . '-bootstrap-css',
				'url'    => self::$core->core_assets_lib( 'bootstrap', 'css' ),
			),
			array(
				'type'   => 'js',
				'handle' => self::$plugin_info['name'] . '-bootstrap-js',
				'url'    => self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ),
			),
		);
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'pricing_tab' ), 100, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'pricing_tab_settings' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'pricing_tab_settings_for_variation' ), PHP_INT_MAX, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'cpt_assets' ) );
	}

	/**
	 * Product Edit Page Assets.
	 *
	 * @return void
	 */
	public function cpt_assets() {
		$screen = get_current_screen();
		if ( is_a( $screen, '\WP_Screen' ) && ( 'post' === $screen->base ) && ( 'product' === $screen->id ) ) {
			$this->handle_enqueue_assets( $this->assets );
			$this->handle_enqueue_assets( $this->settings->get_settings_assets() );
		}
	}

	/**
	 * Pricing tab.
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function pricing_tab( $tabs ) {
		$tabs[ self::$plugin_info['name'] . '-settings-tab' ] = array(
			'label'    => esc_html__( 'Advanced Pricing [GrandPlugins]', 'advanced-pricing-for-woocommerce' ),
			'target'   => self::$plugin_info['name'] . '-settings-tab',
			'class'    => array(),
			'priority' => 60,
			'icon'     => 'dashicons-money',
		);

		return $tabs;
	}

	/**
	 * Pricing Tab Settings.
	 *
	 * @return void
	 */
	public function pricing_tab_settings() {
		global $post, $thepostid, $product_object;
		if ( ! $thepostid || ! $product_object || is_wp_error( $product_object ) ) {
			return;
		}
		?><div class="panel woocommerce_options_panel float-end" id="<?php echo esc_attr( self::$plugin_info['name'] . '-settings-tab' ); ?>">
		<?php $this->settings->print_fields(); ?>
		</div>
		<?php
		$this->all_units_pricing_guide();
		$this->tiered_pricing_guide();
		$this->package_pricing_guide();
		$this->qtybreaks_pricing_guide();
		$this->schedule_script();
		$this->settings_styles();
	}

	/**
	 * Settings Styles.
	 */
	private function settings_styles() {
		?>
		<style>.<?php echo esc_attr( self::$plugin_info['name'] . '-settings-tab_options' ); ?> a:before {content: "\f18e" !important; }</style>
		<?php
	}

	/**
	 * Pricing Tab Settings for Variations.
	 *
	 * @return void
	 */
	public function pricing_tab_settings_for_variation( $loop, $variation_data, $variation ) {
		global $post, $thepostid, $product_object;
		?><div class="woocommerce_options_panel w-100 <?php echo esc_attr( self::$plugin_info['classes_general'] . '-pro-field' ); ?>">
		<?php self::$core->pro_btn(); ?>
		<?php $this->variations_settings->print_variation_fields( $variation->ID ); ?>
		</div>
		<?php
	}

	/**
	 * Schedule reset Script.
	 *
	 * @return void
	 */
	private function schedule_script() {
		?>
		<script>
			( function($) {
				$( function(e) {
					$(document).on( 'click', '.<?php echo esc_attr( self::$plugin_info['prefix'] . '-reset-schedule' ); ?>', (e) => {
						e.preventDefault();
						let btn = $(e.target);
						btn.closest('.input-field-container').find('input').val('');
					});
					$(document).on( 'click', '.<?php echo esc_attr( self::$plugin_info['prefix'] . '-sales-counter-overwrite-trigger' ); ?>', (e) => {
						let btn               = $(e.target);
						let priceCounterInput = btn.closest('.input-field-container').find('.dynamic-price-counter');
						if ( btn.is(':checked' ) ) {
							priceCounterInput.attr( 'disabled', false );
						} else {
							priceCounterInput.attr( 'disabled', true );
						}
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * All Units Pricing Model Guide.
	 *
	 * @return void
	 */
	private function all_units_pricing_guide() {
		?>
		<div id="all-units-pricing-model-guide" tabindex="-1" class="modal fade" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content p-3">
					<p class="mb-3" style="font-size:17px;"><?php esc_html_e( 'In this pricing model, discount increases with the quantity of units sold. It means that the price per unit is dependent on the amount of items a shopper selects to purchase. For example, one unit costs $100, two—$80 each, five—$70, and so on', 'advanced-pricing-for-woocommerce' ); ?></p>
					<img src="<?php echo esc_url_raw( self::$plugin_info['url'] . 'assets/images/all-units.png' ); ?>" alt="all units pricing model table">
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Tiered Pricing Model Guide.
	 *
	 * @return void
	 */
	private function tiered_pricing_guide() {
		?>
		<div id="tiered-pricing-model-guide" tabindex="-1" class="modal fade" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content p-3">
					<p style="font-size:17px;"><?php esc_html_e( 'The tiered model applies a discount only to products ordered above a specific price level.', 'advanced-pricing-for-woocommerce' ); ?></p>
					<br/><br/>
					<p style="font-size:17px;" class="mb-3"><?php esc_html_e( 'For example, when a customer chooses one unit, the cost is the price tier for one, or $100. Selecting two units, the customer will pay $100 for the first unit and $80 for the second one. If the buyer chooses five units, the first will be $100, from the second to the fourth the cost will amount to $80, and the fifth one will be $70. Thus, the full price will be $410.', 'advanced-pricing-for-woocommerce' ); ?></p>
					<img src="<?php echo esc_url_raw( self::$plugin_info['url'] . 'assets/images/tiered.png' ); ?>" alt="tiered pricing model table">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Package Pricing Model Guide.
	 *
	 * @return void
	 */
	private function package_pricing_guide() {
		?>
		<div id="package-pricing-model-guide" tabindex="-1" class="modal fade" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content p-3">
					<p style="font-size:17px;"><?php esc_html_e( 'In the package pricing model, shoppers purchase packages of items at a fixed price. According to this volume discount formula, the discount is applicable for specific packages of the items.', 'advanced-pricing-for-woocommerce' ); ?></p>
					<br/><br/>
					<p style="font-size:17px;" class="mb-3"><?php esc_html_e( 'Thus, if a buyer has an intention to purchase an unstated amount, for example, 7 units, he/she will have to buy a combination of package deals: a package of five units for $350, and a package of two for $160 results in the total cost of $510. It will look like 7 units=(5 units *$70)+(2 units*$80)=$510.', 'advanced-pricing-for-woocommerce' ); ?></p>
					<img src="<?php echo esc_url_raw( self::$plugin_info['url'] . 'assets/images/package.png' ); ?>" alt="package pricing model table">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Quantity Breaks Pricing Model Guide.
	 *
	 * @return void
	 */
	private function qtybreaks_pricing_guide() {
		?>
		<div id="qtybreaks-pricing-model-guide" tabindex="-1" class="modal fade" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content p-3">
					<p style="font-size:17px;"><?php esc_html_e( 'In the Quantity-Breaks pricing model, customers get a discount if purchased an exact quantity of an item.', 'advanced-pricing-for-woocommerce' ); ?></p>
					<br/><br/>
					<p style="font-size:17px;" class="mb-3"><?php esc_html_e( 'Example: A product costs $10 will be sold for $9 if the customer bought 100 pieces. The discount won\'t be applied if the customer buys more or less than that exact quantity.', 'advanced-pricing-for-woocommerce' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}
