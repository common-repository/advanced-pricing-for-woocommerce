<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\FieldBase;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Radio Field.
 */
class RadioField extends FieldBase {


	/**
	 * Get Radio Field HTML.
	 *
	 * @param boolean $return;
	 *
	 * @return mixed
	 */
	public function get_field_html( $return = false ) {
		if ( $return ) {
			ob_start();
		}
        foreach ( $this->field['options'] as $field_option ) :
		?>
		<div class="col d-flex-align-items-center mb-3">
			<div class="input w-100 d-flex align-items-center flex-wrap">
				<input type="radio" <?php $this->field_id(); ?> <?php $this->field_classes(); ?> <?php $this->field_name(); ?> value="<?php echo esc_attr( ! empty( $field_option['value'] ) ? $field_option['value'] : '' ); ?>" <?php $this->custom_attributes_html( 'attrs', ! empty( $field_option['attrs'] ) ? $field_option['attrs'] : array() ); ?> <?php $this->checked( ( ( empty( $field['value'] ) && ! empty( $field_option['default'] ) ) || ( ! empty( $field_option['value'] ) && $this->field['value'] === $field_option['value'] ) ) ); ?> >
				<?php if ( ! empty( $field_option['input_suffix'] ) ) : ?>
					<span class="ms-1"><?php echo wp_kses_post( $field_option['input_suffix'] ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $field_option['input_footer'] ) ) : ?>
					<h6 class="small text-muted mt-1 ms-4"><?php echo wp_kses_post( $field_option['input_footer'] ); ?></h6>
			<?php endif; ?>
		</div>
        <?php
        endforeach;
		if ( $return ) {
			return ob_get_clean();
		}
	}
}
