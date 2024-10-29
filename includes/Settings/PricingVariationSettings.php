<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\Settings;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\GeneralUtilsTrait;
use function GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\Fields\pricing_fields;

/**
 * Pricing Variations Settings CLass.
 */
final class PricingVariationSettings extends Settings {

    use GeneralUtilsTrait;

	/**
	 * Singleton Instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Prepare Settings.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->id           = self::$plugin_info['name'] . '-advanced-product-pricing-settings';
		$this->is_cpt       = 'product';
		$this->is_variation = true;
	}

	/**
	 * Set Fields.
	 *
	 * @return void
	 */
	protected function set_fields() {
		$this->fields = pricing_fields( $this, self::$core, self::$plugin_info );
	}

	/**
	 * Settings Hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
	}
}
