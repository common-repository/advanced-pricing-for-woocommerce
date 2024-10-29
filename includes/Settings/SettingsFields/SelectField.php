<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsFields\FieldBase;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Select Field.
 */
class SelectField extends FieldBase {


	/**
	 * Get Select Field HTML.
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
		<select <?php $this->select_args(); ?> <?php $this->search_attrs(); ?> <?php $this->field_id(); ?> <?php $this->field_classes(); ?> <?php $this->field_name(); ?> <?php $this->custom_attributes_html(); ?> <?php echo esc_attr( ! empty( $this->field['multiple'] ) ? 'multiple' : '' ); ?> >
		<?php
		$options                = array();
		$this->field['options'] = $this->field['options'] ?? array();
		if ( ! empty( $this->field['select_type'] ) ) {
			if ( ! empty( $this->field['value'] ) ) {
				switch ( $this->field['select_type'] ) {
					case 'products':
						foreach ( (array) $this->field['value'] as $id ) {
							$product_object = wc_get_product( $id );
							$formatted_name = is_a( $product_object, '\WC_Product_variation' ) ? ( '#' . $product_object->get_id() . ' [' . $product_object->get_name() . '] ' . ( $product_object->get_sku() ? ' (' . $product_object->get_sku() . ')' : '' ) ) : $product_object->get_formatted_name();
							$options[ $id ] = rawurldecode( $formatted_name );
						}
						break;
					case 'posts':
					case 'pages':
						foreach ( (array) $this->field['value'] as $id ) {
							$product_object = get_post( $id );
							$options[ $id ] = get_the_title( $id );
						}
						break;
					case 'taxs':
						$terms = get_terms(
							array(
								'taxonomy'   => $this->field['tax_name'],
								'include'    => (array) $this->field['value'],
								'fields'     => 'id=>name',
								'hide_empty' => false,
							)
						);

						foreach ( $terms as $term_id => $term_name ) {
							$options[ $term_id ] = $term_name;
						}
						break;
					case 'users':
						$users = get_users(
							array(
								'include' => (array) $this->field['value'],
							)
						);
						foreach ( $users as $user ) {
							$options[ $user->ID ] = '#' . $user->ID . ' | ' . $user->user_nicename . ' | ' . $user->user_email;
						}
						break;

				}
				$this->field['options'] = array_replace( $this->field['options'], $options );
				$this->field['options'] = apply_filters( $this->id . '-' . $this->field['filter'] . '-options', $this->field['options'], $this->field );
				foreach ( $this->field['options'] as $value => $label ) :
					?>
				<option selected value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php
				endforeach;
			}
		} else {
			$this->field['options'] = apply_filters( $this->id . '-' . $this->field['filter'] . '-options', $this->field['options'], $this->field );
			foreach ( $this->field['options'] as $value => $label ) :
				?>
			<option <?php selected( $value, $this->field['value'] ); ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php
			endforeach;
		}
		?>
		</select>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Set Select Search attributes.
	 *
	 * @return void
	 */
	private function search_attrs() {
		echo wp_kses_post( ! empty( $this->field['search_data'] ) ? esc_attr( 'data-search_params' ) . '="' . esc_attr( wp_json_encode( $this->field['search_data'] ) ) . '"' : '' );
	}

	/**
	 * Set Select2 args attributes.
	 *
	 * @return void
	 */
	private function select_args() {
		echo wp_kses_post( ! empty( $this->field['select_args'] ) ? esc_attr( 'data-select_args' ) . '="' . esc_attr( wp_json_encode( $this->field['select_args'] ) ) . '"' : '' );
	}

	/**
	 * Get Empty Value.
	 *
	 * @return string
	 */
	public function get_empty_value() {
		return ! empty( $this->field['multiple'] ) ? array() : $this->field['value'];
	}

}
