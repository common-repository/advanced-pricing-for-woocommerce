<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\FieldBase;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL Field.
 */
class UrlField extends TextField {

	/**
	 * Sanitize Field.
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize_field( $value ) {
		return esc_url_raw( $value );
	}
}
