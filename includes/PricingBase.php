<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingMapperTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingSettingsTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\PricingUtils;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\CartUtils;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\GeneralUtilsTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\PricingSettings;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing\PricingModel\PricingModelInterface;


defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Base;

/**
 * Pricing Base Class.
 */
class PricingBase extends Base {

	use GeneralUtilsTrait, CartUtils, PricingUtils, PricingMapperTrait, PricingSettingsTrait;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->settings = PricingSettings::init();
	}

	/**
	 * Pricing Settings.
	 *
	 * @var PricingSettings
	 */
	protected $settings;

	/**
	 * Get Conditions.
	 *
	 * @param string $condition_type
	 * @return array
	 */
	protected function get_conditions( $conditions_type, $product_id ) {
		return $this->get_settings_key( $conditions_type, $product_id );
	}

	/**
	 * Is Pricing Enabled.
	 *
	 * @param \WC_Product|int $_product
	 * @return boolean|int
	 */
	protected function is_pricing_enabled( $_product ) {
		if ( is_int( $_product ) ) {
			$_product = wc_get_product( $_product );
		}
		$enabled = ( 'on' === $this->get_settings_key( 'status', $_product->get_id() ) );
		if ( $enabled ) {
			return true;
		}

		if ( is_a( $_product, \WC_Product_Variation::class ) ) {
			$parent_product_id = $_product->get_parent_id();
			$enabled           = ( 'on' === $this->get_settings_key( 'status', $parent_product_id ) );
			if ( $enabled ) {
				return $parent_product_id;
			}
		}

		return false;
	}

	/**
	 * Check if a product Advanced Pricing is available.
	 *
	 * @param int|\WC_Product $_product
	 * @return boolean|int
	 */
	protected function is_product_advanced_pricing_enabled( $_product ) {
		if ( is_int( $_product ) ) {
			$product_id = $_product;
			$_product   = wc_get_product( $_product );
		} else {
			$product_id = $_product->get_id();
		}

		// Pricing status check.
		$is_enabled = $this->is_pricing_enabled( $product_id );
		if ( ! $is_enabled ) {
			return false;
		}

		// It's variation and follows the parent Variable product.
		if ( is_numeric( $is_enabled ) ) {
			$product_id                 = $is_enabled;
			$variation_following_parent = true;
		}
		if ( ! $this->is_pricing_enabled( $product_id ) ) {
			return false;
		}

		return isset( $variation_following_parent ) ? $variation_following_parent : true;
	}

	/**
	 * Check if variation has its own pricing.
	 *
	 * @param int|\WC_Product_Variation $variation_id
	 * @return boolean
	 */
	protected function is_variation_has_own_pricing( $variation_id ) {
		if ( ! is_int( $variation_id ) ) {
			$variation_id = $variation_id->get_id();
		}

		$is_enabled = $this->is_pricing_enabled( $variation_id );
		if ( is_numeric( $is_enabled ) ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Get Product Pricing Model.
	 *
	 * @param int $product_id
	 * @return int
	 */
	protected function get_pricing_model( $product_id ) {
		return $this->get_settings_key( 'pricing_model', $product_id );
	}

	/**
	 * Check if the product is name your price product.
	 *
	 * @param int $product_id
	 * @return boolean|int
	 */
	protected function is_name_your_price_product( $product_id ) {
		$is_enabled = $this->is_pricing_enabled( $product_id );
		if ( ! $is_enabled ) {
			return false;
		}

		// It's variation and follows the parent Variable product.
		if ( is_numeric( $is_enabled ) ) {
			$product_id                 = $is_enabled;
			$variation_following_parent = true;
		}

		if ( 4 !== absint( $this->get_pricing_model( $product_id ) ) ) {
			return false;
		}

		if ( isset( $variation_following_parent ) ) {
			return $product_id;
		}

		return true;
	}

	/**
	 * Hide Original Price for Name your price product.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function is_hide_original_price_for_name_your_price( $product_id ) {
		return ( $this->is_name_your_price_product( $product_id ) && ( 'on' === $this->get_settings_key( 'name_your_price_hide_price', $product_id ) ) );
	}

	/**
	 * Get Package Pricing Type.
	 *
	 * @param int $product_id
	 * @return int
	 *
	 * 1 => Price per unit.
	 * 2 => Package Full Price.
	 */
	protected function get_package_pricing_type( $product_id ) {
		return $this->get_settings_key( 'package_pricing_type', $product_id );
	}

	/**
	 * Get product Pricing Type.
	 *
	 * @param int $product_id
	 * @return int
	 */
	protected function get_pricing_type( $product_id ) {
		return $this->get_settings_key( 'pricing_type', $product_id );
	}

	/**
	 * Get pricing table status.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function get_pricing_table_status( $product_id ) {
		return ( 'on' === $this->get_settings_key( 'pricing_table_html_status', $product_id ) );
	}

	/**
	 * Get Product Pricing Table.
	 *
	 * @param int $product_id
	 * @return array|null
	 */
	protected function get_product_pricing_table( $product_id, $sort = false ) {
		$pricing_type          = $this->get_pricing_type( $product_id );
		$pricing_table_handler = $this->pricing_table_mapping( $pricing_type, $this->prefix );

		if ( is_null( $pricing_table_handler ) ) {
			return array();
		}

		$pricing_table = $pricing_table_handler->get_pricing_table( $product_id );
		$pricing_table = apply_filters( self::$plugin_info['prefix'] . '-pricing-table', $pricing_table );
		if ( $sort ) {
			$pricing_table = $this->sort_pricing_table( $pricing_table, 'quantity', 'asc' );
		}
		return $pricing_table;
	}

	/**
	 * Choose Price Model.
	 *
	 * @return PricingModelInterface|null
	 */
	protected function get_product_pricing_model( $cart_item_product_id ) {
		// 1) Get product Pricing Model settings [ 1 - 2 -3 ].
		$product_pricing_model = $this->get_pricing_model( $cart_item_product_id );
		// 2) Get the pricing model by settings [ 1 => All-Units - 2 => tiered - 3 => Package ].
		return $this->pricing_model_mapping( $product_pricing_model );
	}

	/**
	 * Check if product has pricing model by name.
	 *
	 * @param int $product_id
	 * @param string $model_name
	 * @return boolean
	 */
	protected function is_product_pricing_model( $product_id, $model_name ) {
		$pricing_model = $this->get_product_pricing_model( $product_id );
		if ( ! $pricing_model ) {
			return false;
		}
		return ( $pricing_model->get_name() === $model_name );
	}

	/**
	 * Check if product suitable for quantity swatches.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function is_product_quantity_swatches( $product_id ) {
		return ( ( 'on' === $this->get_settings_key( 'manual_table_pricing_swatches', $product_id ) ) );
	}

	/**
	 * Get product default quantity swatch.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function get_product_default_swatch( $product_id ) {
		return $this->get_settings_key( 'manual_table_pricing_swatches_default', $product_id );
	}

	/**
	 * Check if product has default quantity swatch.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function product_has_default_swatch( $product_id ) {
		return ( 0 !== $this->get_settings_key( 'manual_table_pricing_swatches_default', $product_id ) );
	}

	/**
	 * Check if product is package pricing model and full package pricing.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function is_full_package_pricing( $product_id ) {
		return ( $this->is_product_pricing_model( $product_id, 'package' ) && ( 2 === $this->get_package_pricing_type( $product_id ) ) );
	}

}
