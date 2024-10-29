<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pages;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\NoticeUtilsTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Pages\PagesBase\AdminPage;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\MainSettings;

/**
 * Advanced Catpcha Settings Page CLass.
 */
final class SettingsPage extends AdminPage {

	use NoticeUtilsTrait;

	/**
	 * Singular Instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Prepare Page.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->page_props['page_title']       = esc_html__( 'Woo Advanced Pricing', 'advanced-pricing-for-woocommerce' );
		$this->page_props['menu_title']       = esc_html__( 'Woo Advanced Pricing [GrandPlugins]', 'advanced-pricing-for-woocommerce' );
		$this->page_props['menu_slug']        = self::$plugin_info['name'] . '-settings';
		$this->page_props['is_woocommerce']   = true;
		$this->page_props['hide_save_button'] = true;
		$this->page_props['tab_key']          = 'action';
		$this->tabs                           = array(
			'general' => array(
				'default'           => true,
				'title'             => esc_html__( 'General', 'advanced-pricing-for-woocommerce' ),
				'hide_title'        => true,
				'woo_hide_save_btn' => true,
			),
		);
		$this->settings                       = MainSettings::init();
	}

	/**
	 * Page Hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
		add_filter( 'plugin_action_links_' . self::$plugin_info['basename'], array( $this, 'page_link' ), 10, 1 );
	}

	/**
	 * Page Link.
	 *
	 * @param array $links
	 * @return array
	 */
	public function page_link( $links ) {
		$links[] = '<a href="' . esc_url_raw( $this->get_page_path() ) . '" >' . esc_html__( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	 * General tab Settings fields.
	 *
	 * @return void
	 */
	protected function general_tab() {
		$this->settings->print_settings( 'general' );
	}

	/**
	 * Set Assets.
	 *
	 * @return void
	 */
	protected function set_assets() {
		$this->assets = array();
	}
}
