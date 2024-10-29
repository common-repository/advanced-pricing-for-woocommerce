<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\FieldBase;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repeater Field.
 */
class RepeaterField extends FieldBase {

	/**
	 * Repeater Hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
		// add_action( $this->id . '-after-settings-field-' . ( ! empty( $this->field['filter'] ) ? $this->field['filter'] : $this->field['key'] ), array( $this, 'new_repeater_row_btn' ) );
	}

	/**
	 * New Repeater Row Btn.
	 *
	 * @return void
	 */
	public function new_repeater_row_btn() {
		?>
		<div class="repeater-new-item-btn d-flex flex-column my-3">
			<?php $this->loader_icon( 'big', 'add-repeater-field-item-loader hidden mx-auto' ); ?>
			<!-- Repeater Add Group Rule Button -->
			<button <?php $this->custom_attributes_html( 'repeat_add_attrs' ); ?> data-key="<?php echo esc_attr( $this->field['key'] ); ?>" data-variation_id="<?php echo esc_attr( ! empty( $this->field['variation_id'] ) ? $this->field['variation_id'] : 0 ); ?>" data-action="<?php echo esc_attr( $this->id . ( ! empty( $this->field['variation_id'] ) ? '-variation' : '' ) . '-get-repeater-item' ); ?>" data-target="<?php echo esc_attr( $this->id . '-' . $this->field['key'] . '-repeater-container' ); ?>" data-count="<?php echo esc_attr( count( $this->field['value'] ) ); ?>" class="my-4 btn btn-primary mx-auto w-auto <?php echo esc_attr( self::$plugin_info['prefix'] . '-add-rule-group' ); ?>"><?php echo esc_html( ! empty( $this->field['repeat_add_label'] ) ? $this->field['repeat_add_label'] : esc_html__( 'Add rule group' ) ); ?></button>
		</div>
		<?php
	}

	/**
	 * Get Repeater Field HTML.
	 *
	 * @param boolean $return;
	 *
	 * @return mixed
	 */
	public function get_field_html( $return = false ) {
		if ( $return ) {
			ob_start();
		}
		foreach ( $this->field['value'] as $index => $repeater_row ) {
			?>
			<div id="repeater-item-<?php echo esc_attr( $this->field['key'] . '-' . $index ); ?>" class="repeater-item position-relative <?php echo esc_attr( ! empty( $this->field['classes'] ) ? $this->field['classes'] : '' ); ?>">
				<div class="position-absolute top-0 end-0 bg-black" style="border-radius:50%;padding:4px 5px;margin:5px;">
					<button type="button" class="btn-close btn btn-close-white" aria-label="Close" style="opacity:1;"></button>
				</div>
				<div class="container-fluid">
					<div class="row mt-2">
					<?php
					foreach ( $repeater_row as $subitem_key => $subitem_value ) :
						$subitem_settings_field = $this->prepare_repeater_subitem_settings_field( $subitem_key, $index );
						$settings_field         = new Field();
						$subitem_field          = $settings_field->new_field( $this->id, $subitem_settings_field );
						$subitem_field->get_field();
					endforeach
					?>
					</div>
				</div>
			</div>
			<?php
		}
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Prepare Repeater Row Subitem Settings Field.
	 *
	 * @param string $subitem_key
	 * @param int    $repeater_row_index
	 * @return array
	 */
	public function prepare_repeater_subitem_settings_field( $subitem_key, $repeater_row_index ) {
		$subitem_settings_field                   = $this->field['default_subitem'][ $subitem_key ];
		$subitem_settings_field['id']             = ( $subitem_settings_field['id'] ?? $this->field['key'] ) . '-' . $subitem_key . '-' . $repeater_row_index;
		$subitem_settings_field['repeater_index'] = $repeater_row_index;
		$subitem_settings_field['name']           = $this->id . ( ! empty( $this->field['variation_id'] ) ? '[' . absint( esc_attr( $this->field['variation_id'] ) ) . ']' : '' ) . '[' . $this->field['key'] . '][' . $repeater_row_index . '][' . $subitem_key . ']';
		$subitem_settings_field['filter']         = $this->field['key'] . '-' . $subitem_key;
		$subitem_settings_field['value']          = $this->field['value'][ $repeater_row_index ][ $subitem_key ] ?? $this->field['default_subitem'][ $subitem_key ]['value'];
		return $subitem_settings_field;
	}

	/**
	 * Get Repeater Field Default Item HTML
	 *
	 * @param int $index
	 * @return mixed
	 */
	public function get_default_field( $index, $return = false ) {
		$settings_field = new Field();
		if ( $return ) {
			ob_start();
		}
		?>
		<div id="repeater-item-<?php echo esc_attr( $this->field['key'] . '-' . $index ); ?>" <?php $this->field_classes( 'repeater-item position-relative' ); ?> >
			<div class="position-absolute top-0 end-0 bg-black" style="border-radius:50%;padding:4px 5px;margin:5px;">
				<button type="button" class="btn-close btn btn-close-white" aria-label="Close" style="opacity:1;"></button>
			</div>
			<div class="container-fluid">
				<div class="row mt-2">
				<?php
				foreach ( $this->field['default_subitem'] as $subitem_key => $subitem_field ) {
					$subitem_field['id']             = ( $subitem_field['id'] ?? $this->field['key'] ) . '-' . $subitem_key . '-' . $index;
					$subitem_field['key']            = $subitem_key;
					$subitem_field['repeater_index'] = $index;
					$subitem_field['name']           = $this->id . ( ! empty( $this->field['variation_id'] ) ? '[' . absint( esc_attr( $this->field['variation_id'] ) ) . ']' : '' ) . '[' . $this->field['key'] . '][' . $index . '][' . $subitem_key . ']';
					$subitem_field['filter']         = $this->field['key'] . '-' . $subitem_key;
					$subitem_settings_field          = $settings_field->new_field( $this->id, $subitem_field );
					$subitem_settings_field->get_field();
				}
				?>
				</div>
			</div>
		</div>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get Repeater SubItem Settings Field Default.
	 *
	 * @param string $subitem_key
	 * @param int $index
	 * @return array
	 */
	public function get_default_subitem_settings_field( $subitem_key, $index ) {
		$subitem_field                   = $this->field['default_subitem'][ $subitem_key ];
		$subitem_field['id']             = ( $subitem_field['id'] ?? $this->field['key'] ) . '-' . $subitem_key . '-' . $index;
		$subitem_field['key']            = $subitem_key;
		$subitem_field['repeater_index'] = $index;
		$subitem_field['name']           = $this->id . ( ! empty( $this->field['variation_id'] ) ? '[' . absint( esc_attr( $this->field['variation_id'] ) ) . ']' : '' ) . '[' . $this->field['key'] . '][' . $index . '][' . $subitem_key . ']';
		$subitem_field['filter']         = $this->field['key'] . '-' . $subitem_key;
		return $subitem_field;
	}

	/**
	 * Get Repeater SubItem Field Default Item HTML.
	 *
	 * @param int $index
	 * @return mixed
	 */
	public function get_default_subitem_field( $subitem_key, $index, $return = false ) {
		if ( $return ) {
			ob_start();
		}
		$settings_field                  = new Field();
		$subitem_settings_field          = $settings_field->new_field( $this->id, $this->get_default_subitem_settings_field( $subitem_key, $index ) );
		$subitem_settings_field->get_field();

		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Sanitize Submitted Repeater Field.
	 *
	 * @param string $key
	 * @param array  $settings
	 * @return mixed
	 */
	public function sanitize_field( $value ) {
		return array();
	}

	/**
	 * Sanitize Repeater Field.
	 *
	 * @param array $value
	 * @param int|null $variation_id
	 * @return mixed
	 */
	public function sanitize_repeater_field( $value, $variation_id = null ) {
		$settings              = array();
		$default_field_subitem = $this->field['default_subitem'];

		// Loop over the submitted array for index and sanitize each sub-item.
		foreach ( $value as $item_index => $item_arr ) {
			$subitem    = array();
			$item_index = absint( sanitize_text_field( $item_index ) );
			foreach ( $default_field_subitem as $subitem_key => $subitem_arr ) {
				$posted_key              = array( $this->field['key'], $item_index, $subitem_key );
				$subitem[ $subitem_key ] = Settings::sanitize_submitted_field( $this->id, $variation_id, $posted_key, $default_field_subitem[ $subitem_key ], $default_field_subitem[ $subitem_key ]['value'] );
			}
			$settings[] = $subitem;
		}

		return $settings;
	}


	/**
	 * Get Empty Value.
	 *
	 * @return array
	 */
	public function get_empty_value() {
		return array();
	}
}
