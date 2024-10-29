<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\FieldBase;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text Field.
 */
class TextField extends FieldBase {


	/**
	 * Get Text Field HTML.
	 *
	 * @param boolean $return;
	 *
	 * @return mixed
	 */
	public function get_field_html( $return = false ) {
		if ( $return ) {
			ob_start();
		}
		?>
		<input type="<?php echo esc_attr( ! empty( $this->field['type'] ) ? $this->field['type'] : 'text' ); ?>" <?php $this->field_id(); ?> <?php $this->field_classes(); ?> <?php $this->field_name(); ?> value="<?php echo esc_attr( isset( $this->field['value'] ) ? $this->field['value'] : '' ); ?>" <?php $this->custom_attributes_html(); ?> >
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

}
