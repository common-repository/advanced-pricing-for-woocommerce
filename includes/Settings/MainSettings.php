<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\Settings;
use function GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\Fields\main_fields;

/**
 * Main Settings CLass.
 */
final class MainSettings extends Settings {

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
		$this->id = self::$plugin_info['name'] . '-advanced-product-pricing-main-settings';
	}

	/**
	 * Set Fields.
	 *
	 * @return void
	 */
	protected function set_fields() {
		$this->fields  = main_fields( $this, self::$core, self::$plugin_info );
		$this->tab_key = 'action';
	}

	/**
	 * Settings Hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
		add_action( $this->id . '-just-after-settings-field-swatch_template', array( $this, 'swatch_template_preview' ), 100, 1 );
	}

	/**
	 * Swatch Template Preview
	 *
	 * @param array $field
	 * @return void
	 */
	public function swatch_template_preview( $field ) {
		$swatch_template   = $this->get_settings( 'swatch_template' );
		$template_url_base = self::$plugin_info['url'] . 'assets/images/template-';
		$template_url      = $template_url_base . $swatch_template . '.png';
		?>
		<img data-url_base="<?php esc_url_raw( $template_url_base ); ?>" class="swatches-template-preview" width="150px" src="<?php echo esc_attr( $template_url ); ?>">
		<script>
			(function($){
				$(function() {
					$('.swatch-template').on( 'change', function(e) {
						e.preventDefault();
						let templateID  = $(this).val();
						let templateURL = '<?php echo esc_url_raw( $template_url_base ); ?>' + templateID + '.png';
						$('.swatches-template-preview').attr('src', templateURL );
					});
				});
			})(jQuery);
		</script>
		<?php
	}

}
