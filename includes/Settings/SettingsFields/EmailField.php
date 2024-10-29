<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Field.
 */
class EmailField extends TextField {


	/**
	 * Sanitize Email Field.
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize_field( $value ) {
		return sanitize_email( $value );
	}
}
