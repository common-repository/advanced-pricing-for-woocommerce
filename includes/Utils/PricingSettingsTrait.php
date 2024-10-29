<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Utils;

defined( 'ABSPATH' ) || exit;


/**
 * Pricing Settings Trait.
 */
trait PricingSettingsTrait {

	/**
	 * Settings Prefix.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Get Pricing Settings Key.
	 *
	 * @param string $key
	 * @param int    $product_id
	 * @return mixed
	 */
	protected function get_settings_key( $key, $product_id ) {
		return $this->settings->get_settings( $this->prefix . $key, $product_id );
	}

	/**
	 * Get Pricing Settings.
	 *
	 * @param string $key
	 * @param int    $product_id
	 * @return mixed
	 */
	protected function get_settings( $product_id ) {
		return $this->settings->get_settings( null, $product_id );
	}
}
