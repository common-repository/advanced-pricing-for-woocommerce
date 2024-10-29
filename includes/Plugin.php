<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Base;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingCPT;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pricing;
use GPLSCore\GPLS_PLUGIN_WOOADPG\PricingFront;
use GPLSCore\GPLS_PLUGIN_WOOADPG\NameYourPrice;
use function GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\setup_settings;
use function GPLSCore\GPLS_PLUGIN_WOOADPG\Pages\PagesBase\setup_pages;

/**
 * Plugin Class for Activation - Deactivation - Uninstall.
 */
class Plugin extends Base {

	/**
	 * Main Class Load.
	 *
	 * @return void
	 */
	public static function load() {
		setup_settings();
		setup_pages();
		PricingCPT::init();
		Pricing::init();
		NameYourPrice::init();
		PricingFront::init();
	}

	/**
	 * Plugin is activated.
	 *
	 * @return void
	 */
	public static function activated() {
		// Activation Custom Code here...
	}

	/**
	 * Plugin is Deactivated.
	 *
	 * @return void
	 */
	public static function deactivated() {
		// Deactivation Custom Code here...
	}

	/**
	 * Plugin is Uninstalled.
	 *
	 * @return void
	 */
	public static function uninstalled() {
		// Uninstall Custom Code here...
	}

	/**
	 * Is Plugin Active.
	 *
	 * @param string $plugin_basename
	 * @return boolean
	 */
	public static function is_plugin_active( $plugin_basename ) {
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( $plugin_basename );
	}
}
