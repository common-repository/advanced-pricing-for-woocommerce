<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\Settings;
use function GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\Fields\pricing_fields;

/**
 * Pricing Settings CLass.
 */
final class PricingSettings extends Settings {

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
		$this->id     = self::$plugin_info['name'] . '-advanced-product-pricing-settings';
		$this->is_cpt = 'product';
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
		add_action( $this->id . '-after-settings-field-pricing_table_html_status', array( $this, 'pricing_table_html_shortcode' ), 100, 1 );
		add_action( $this->id . '-just-after-settings-field-dynamic_price_counter', array( $this, 'dynamic_price_sales_counter_overwrite' ), 100, 1 );
		add_filter( $this->id . '-filter-settings-before-saving', array( $this, 'update_sales_counter' ), 100, 2 );
	}

	/**
	 * Pricing Table HTML Shortcode.
	 *
	 * @param array $field
	 * @return void
	 */
	public function pricing_table_html_shortcode( $field ) {
		?>
		<div class="pricing-table-shortcode-container my-2 px-3 py-5 shadow-sm rounded bg-white">
			<div class="container d-flex justify-content-center align-items-center">
				<strong class="me-1"><?php esc_html_e( 'Pricing Table Shortcode:', 'advanced-pricing-for-woocommerce' ); ?></strong>
				<div class="shortcode-wrapper d-flex align-items-center">
					<code class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-pricing-table-shortcode' ); ?> me-1" >[<?php echo esc_attr( str_replace( '-', '_', self::$plugin_info['prefix'] . '-pricing-table' ) ); ?>]</code>
					<?php self::clipboard_icon( '.' . self::$plugin_info['prefix'] . '-pricing-table-shortcode' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Option to Overwrite sales counter.
	 *
	 * @param array $field
	 * @return void
	 */
	public function dynamic_price_sales_counter_overwrite( $field ) {
		?>
		<span class="ms-1">
			<?php esc_html_e( 'Overwrite ?', 'advanced-pricing-for-woocommerce' ); ?>
			<input type="checkbox" value="on" class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-sales-counter-overwrite-trigger' ); ?>" name="<?php echo esc_attr( self::$plugin_info['name'] . '-sales-counter-overwrite' ); ?>">
		</span>
		<?php
	}

	/**
	 * Update Sales Counter.
	 *
	 * @param array $settings
	 * @param array $old_settings
	 * @return array
	 */
	public function update_sales_counter( $settings, $old_settings ) {
		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-sales-counter-overwrite' ] ) && isset( $_POST[ $this->id ]['dynamic_price_counter'] ) ) {
			$settings['dynamic_price_counter'] = absint( sanitize_text_field( wp_unslash( $_POST[ $this->id ]['dynamic_price_counter'] ) ) );
		}
		return $settings;
	}
}
