<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\Field;

defined( 'ABSPATH' ) || exit;

/**
 * Settings Submit Handle.
 */
trait SettingsSubmitTrait {

	/**
	 * Save Variations Settings for Woo.
	 *
	 * @param int $variation_id
	 * @return void
	 */
	public function submit_save_variation_settings( $variation_id ) {
		$this->save_settings( $variation_id, true );
	}

	/**
	 * Save Settings for CPT.
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function submit_save_cpt_settings( $post_id ) {
		$this->save_settings( $post_id );
	}

	/**
	 * Save Settings using Submit.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function submit_save_settings() {

		if ( empty( $_POST[ $this->id . '-settings-save' ] ) ) {
			return;
		}

		if ( ! empty( $_POST[ $this->id . '-settings-nonce' ] ) && wp_verify_nonce( wp_unslash( $_POST[ $this->id . '-settings-nonce' ] ), $this->id . '-settings-nonce' ) ) {

			// Check user cap.
			if ( ! current_user_can( $this->cap ) ) {
				$this->add_error( esc_html__( 'You need a higher level of permission.' ) );
				return;
			}

			$this->save_settings();

			return;
		}

		self::expired_message();
	}

	/**
	 * AJAX Save Settings.
	 *
	 * @return void
	 */
	public function ajax_save_settings() {
		if ( wp_doing_ajax() && is_admin() && ! empty( $_POST['context'] ) ) {
			// Nonce Check.
			check_ajax_referer( $this->page_nonce, 'nonce' );

			// Cap Check.
			if ( ! current_user_can( $this->cap ) ) {
				wp_die(
					'<h1>' . esc_html__( 'You need a higher level of permission.' ) . '</h1>',
					403
				);
			}

			$this->save_settings();
		}

		wp_die( -1, 403 );
	}

	/**
	 * Save Settings.
	 *
	 * @return void
	 */
	private function save_settings( $post_id = null, $id_in_posted = false ) {
		$tab = $this->get_current_tab();
		if ( ! $tab ) {
			return;
		}

		$settings     = $this->get_settings();
		$old_settings = $settings;
		$fields       = $this->get_fields_for_save( $tab );

		if ( ! empty( $_post[ $this->id ] ) ) {
			return;
		}

		// Before tab Save.
		do_action( $this->id . '-before-settings-save', $settings, $fields );

		foreach ( $fields as $field_key => $field_arr ) {
			if ( ! empty( $field_arr['disable'] ) ) {
				continue;
			}
			$value                  = self::sanitize_submitted_field( $this->id, $id_in_posted ? $post_id : null, $field_key, $field_arr, $settings[ $field_key ] );
			$settings[ $field_key ] = is_null( $value ) ? $settings[ $field_key ] : $value;
		}

		$settings = apply_filters( $this->id . '-filter-settings-before-saving', $settings, $old_settings, $this, $tab );

		$saving = apply_filters( $this->id . '-just-before-saving', true, $settings, $this, $tab );

		if ( $saving ) {
			if ( is_null( $post_id ) ) {
				update_option( $this->settings_key, $settings, $this->autoload );
			} else {
				update_post_meta( $post_id, $this->settings_key, $settings );
			}
		}

		// after tab save.
		do_action( $this->id . '-after-settings-save', $settings, $old_settings, $tab, $saving, $this );

		if ( $saving ) {
			$this->add_message( esc_html__( 'Settings have been saved.' ) );
		}
	}

	/**
	 * Update Settings Key.
	 *
	 * @param string $key_name
	 * @param mixed $key_value
	 * @param int|null $post_id
	 * @return void
	 */
	public function update_settings_key( $key_name, $key_value, $post_id = null ) {
		$field_arr = $this->get_settings_field( $key_name );
		if ( ! $field_arr ) {
			return;
		}
		$field     = Field::new_field( $this->id, $field_arr, false );
		if ( is_null( $field ) ) {
			return;
		}
		$settings              = $this->get_settings( null, $post_id );
		$settings[ $key_name ] = $field->sanitize_field( $key_value );
		if ( is_null( $post_id ) ) {
			update_option( $this->settings_key, $settings, $this->autoload );
		} else {
			update_post_meta( $post_id, $this->settings_key, $settings );
		}
	}

	/**
	 * Get Fields for Save.
	 *
	 * @param string $tab
	 * @return array
	 */
	public function get_fields_for_save( $tab ) {
		$fields                   = $this->get_fields();
		$prepared_settings_fields = array();

		if ( empty( $fields[ $tab ] ) ) {
			return array();
		}

		foreach ( $fields[ $tab ] as $section_name => $section_settings ) {
			if ( ! empty( $section_settings['settings_list'] ) ) {
				foreach ( $section_settings['settings_list'] as $setting_name => $setting_arr ) {
					$prepared_settings_fields[ $setting_name ]           = $setting_arr;
					$prepared_settings_fields[ $setting_name ]['name']   = $setting_arr['name'] ?? $setting_name;
					$prepared_settings_fields[ $setting_name ]['key']    = $setting_name;
					$prepared_settings_fields[ $setting_name ]['filter'] = $setting_name;
				}
			}
		}

		return $prepared_settings_fields;
	}

	/**
	 * Sanitize Submitted Settings Field.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return mixed
	 */
	public static function sanitize_submitted_field( $id, $post_id, $posted_key, $field, $old_value ) {
		$field_obj = Field::new_field( $id, $field, false );

		// Resolve the posted value key.
		if ( is_string( $posted_key ) ) {
			$value = wp_unslash( $post_id ? ( $_POST[ $id ][ $post_id ][ $posted_key ] ?? null ) : ( $_POST[ $id ][ $posted_key ] ?? null ) );
		} else {
			$value = wp_unslash( $post_id ? ( $_POST[ $id ][ $post_id ] ?? null ) : ( $_POST[ $id ] ?? null ) );
			foreach ( $posted_key as $key ) {
				$value = $value[ $key ] ?? null;
				if ( is_null( $value ) ) {
					break;
				}
			}
		}

		if ( is_null( $value ) ) {
			// set to empty array if value is null and field is multiple select.
			if ( in_array( $field_obj->get_type(), array( 'select', 'repeater' ) ) ) {
				$value = array();
			} elseif ( is_null( $value ) && ( 'checkbox' !== $field_obj->get_type() ) ) {
				// fallback to old value if null, except checkboxes.
				$value = $old_value;
			}
		}

		// Accept array only if field is multiple, except repeater.
		if ( is_array( $value ) && ( 'repeater' !== $field_obj->get_type() ) && empty( $field['multiple'] ) ) {
			$value = array_values( $value )[0];
		}

		// Sanitize the value.
		if ( 'repeater' === $field_obj->get_type() ) {
			$value = $field_obj->sanitize_repeater_field( $value, $post_id );
		} else {
			$value = $field_obj->sanitize_field( $value );
		}

		// Fix numeric values.
		return is_numeric( $value ) ? $value + 0 : $value;
	}
}
