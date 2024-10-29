<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\Field;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\RepeaterField;


defined( 'ABSPATH' ) || exit;

/**
 * Settings Utils functions.
 */
trait SettingsUtilsTrait {

	/**
	 * Get Settings ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get Settings Key.
	 *
	 * @return string
	 */
	public function get_settings_key() {
		return $this->settings_key;
	}

	/**
	 * Get Field.
	 *
	 * @param string $field_key
	 * @return array
	 */
	public function get_field( $field_key ) {
		return $this->default_settings_fields[ $field_key ];
	}

	/**
	 * Get Settings Fields.
	 *
	 * @return array
	 */
	public function get_fields( $tab = null ) {
		return ( is_null( $tab ) ? $this->fields : ( ! empty( $this->fields[ $tab ] ) ? $this->fields[ $tab ] : array() ) );
	}

	/**
	 * Woo Submit Fields.
	 *
	 * @return void
	 */
	public function woo_submit_fields() {
		wp_nonce_field( 'woocommerce-settings' );
	}

	/**
	 * Settings Base CSS.
	 *
	 * @return void
	 */
	private function base_css() {
		?>
		<style>
		.astrodivider i,.astrodivider span{position:absolute;border-radius:100%}.astrodivider{margin:64px auto;width:100%;max-width:100%;position:relative}.astrodividermask{overflow:hidden;height:20px}.astrodividermask:after{content:'';display:block;margin:-25px auto 0;width:100%;height:25px;border-radius:125px/12px;box-shadow:0 0 8px #8cade7}.astrodivider span{width:50px;height:50px;bottom:100%;margin-bottom:-25px;left:50%;margin-left:-25px;box-shadow:0 2px 4px #3f4acb;background:#fff}.astrodivider i{top:4px;bottom:4px;left:4px;right:4px;border:1px dashed #68beaa;text-align:center;line-height:40px;font-style:normal;color:#c1006b}
		</style>
		<?php
	}

	/**
	 * Get Current Tab.
	 *
	 * @return string|false
	 */
	private function get_current_tab() {
		return ! empty( $_GET[ $this->tab_key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->tab_key ] ) ) : $this->get_first_tab();
	}

	/**
	 * Get First Tab.
	 *
	 * @return void
	 */
	private function get_first_tab() {
		return array_key_first( $this->fields );
	}

	/**
	 * Get Settings Field.
	 *
	 * @param string $field_key
	 *
	 * @return array|false
	 */
	public function get_settings_field( $field_key, $get_settings_value = false, $custom_attrs = array() ) {
		// Loop over settings sections.
		foreach ( $this->fields as $tab_key => $sections ) {
			foreach ( $sections as $section_name => $section_settings ) {
				if ( isset( $section_settings['settings_list'][ $field_key ] ) ) {
					if ( $get_settings_value ) {
						$section_settings['settings_list'][ $field_key ]['value'] = $this->get_settings( $field_key, ! empty( $custom_attrs['variation_id'] ) ? $custom_attrs['variation_id'] : null );
					}
					$section_settings['settings_list'][ $field_key ]['base_id'] = $this->id;
					$section_settings['settings_list'][ $field_key ]['key']     = $field_key;
					$section_settings['settings_list'][ $field_key ]['filter']  = $field_key;
					$section_settings['settings_list'][ $field_key ]            = array_merge( $section_settings['settings_list'][ $field_key ], $custom_attrs );
					return $section_settings['settings_list'][ $field_key ];
				}
			}
		}

		return false;
	}

	/**
	 * Get Settings for Fields Listing.
	 *
	 * @return array
	 */
	public function get_tab_fields( $tab, $section = '', $custom_attrs = array() ) {
		$settings_fields = $this->fields[ $tab ];

		if ( ! empty( $section ) && ! empty( $settings_fields[ $section ] ) ) {
			$settings_fields = array( $section => $settings_fields[ $section ] );
		}

		foreach ( $settings_fields as $section_name => $section_settings ) {
			foreach ( $section_settings['settings_list'] as $setting_name => $setting_arr ) {

				$settings_fields[ $section_name ]['settings_list'][ $setting_name ] = $this->get_settings_field( $setting_name, true, $custom_attrs );
			}
		}

		$settings_fields = apply_filters( $this->id . '-settings-fields', $settings_fields );

		return $settings_fields;
	}

	/**
	 * Get Repeater Field.
	 *
	 * @param string $field_key
	 * @param string $repeater_item_key
	 * @return array|null
	 */
	public function get_default_repeater_field( $field_key, $index, $variation_id = 0 ) {
		$repeater_item_field                 = $this->default_settings_fields[ $field_key ];
		$repeater_item_field['variation_id'] = $variation_id;
		if ( is_null( $repeater_item_field ) ) {
			return $repeater_item_field;
		}
		$repeater_field = new RepeaterField( $this->id, $repeater_item_field );
		return $repeater_field->get_default_field( $index, true );
	}

	/**
	 * Get Repeater Submite Settings Field.
	 *
	 * @param string $repeater_key
	 * @param string $subitem_key
	 * @param int    $repeater_index
	 * @param array  $custom_attrs
	 * @return array
	 */
	public function get_repeater_subitem_settings_field( $repeater_key, $subitem_key, $repeater_index, $custom_attrs = array() ) {
		$repeater_settings_field = $this->get_settings_field( $repeater_key, true, $custom_attrs );
		$id                      = $repeater_settings_field['base_id'] ?? self::$plugin_info['name'];
		$repeater_field          = new RepeaterField( $id, $repeater_settings_field, false );
		if ( is_null( $repeater_index ) ) {
			$subitem_settings_field = $repeater_field->get_default_subitem_settings_field( $subitem_key, 0 );
		} else {
			$subitem_settings_field = $repeater_field->prepare_repeater_subitem_settings_field( $subitem_key, $repeater_index );
		}

		return $subitem_settings_field;
	}

	/**
	 * Print Repeater Settings Subitem Field.
	 *
	 * @param string  $repeater_key
	 * @param string  $subitem_key
	 * @param int     $repeater_row_index
	 * @param boolean $full_field
	 * @param boolean $echo
	 * @param boolean $ignore_hide
	 * @return mixed
	 */
	public function print_repeater_subitem_field( $repeater_key, $subitem_key, $repeater_row_index, $full_field = true, $custom_attrs = array(), $echo = true, $ignore_hide = true ) {
		$subitem_settings_field = $this->get_repeater_subitem_settings_field( $repeater_key, $subitem_key, $repeater_row_index, $custom_attrs );
		return Field::print_field( $subitem_settings_field, $full_field, $echo, $ignore_hide );
	}

	/**
	 * Print Settings Field.
	 *
	 * @param string  $field_key
	 * @param boolean $full_field
	 * @param boolean $echo
	 * @return mixed
	 */
	public function print_field( $field_key, $full_field = true, $custom_attrs = array(), $echo = true, $ignore_hide = false ) {
		return Field::print_field( $this->get_settings_field( $field_key, true, $custom_attrs ), $full_field, $echo, $ignore_hide );
	}

	/**
	 * Print Settings Fields.
	 *
	 * @param string  $tab
	 * @param string  $section
	 * @param boolean $full_field
	 * @param array   $custom_attrs
	 * @param boolean $echo
	 * @param boolean $ignore_hide
	 * @return mixed
	 */
	public function print_fields( $tab = 'general', $section = '', $full_field = true, $custom_attrs = array(), $echo = true, $ignore_hide = false ) {
		return Field::print_fields( $this->get_tab_fields( $tab, $section, $custom_attrs ), $full_field, $echo, $ignore_hide );
	}

	/**
	 * Print varitions Settings Fields.
	 *
	 * @param int     $variation_id
	 * @param string  $tab
	 * @param string  $section
	 * @param boolean $full_field
	 * @param array   $custom_attrs
	 * @param boolean $echo
	 * @param boolean $ignore_hide
	 * @return mixed
	 */
	public function print_variation_fields( $variation_id, $tab = 'general', $section = '', $full_field = true, $custom_attrs = array(), $echo = true, $ignore_hide = false ) {
		$custom_attrs['variation_id'] = $variation_id;
		return Field::print_fields( $this->get_tab_fields( $tab, $section, $custom_attrs ), $full_field, $echo, $ignore_hide );
	}
}
