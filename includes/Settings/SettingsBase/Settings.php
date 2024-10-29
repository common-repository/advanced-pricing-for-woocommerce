<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Base;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\NoticeUtilsTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\SettingsUtilsTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\SettingsFormTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase\SettingsSubmitTrait;
use GPLSCore\GPLS_PLUGIN_WOOADPG\Utils\GeneralUtilsTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 */
abstract class Settings extends Base {

	use GeneralUtilsTrait, NoticeUtilsTrait, SettingsUtilsTrait, SettingsSubmitTrait, SettingsFormTrait;

	/**
	 * Settings ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Settings Key.
	 *
	 * @var string
	 */
	protected $settings_key;

	/**
	 * Settings Fields Object.
	 *
	 * @var array
	 */
	protected $settings_fields;

	/**
	 * Default Settings
	 *
	 * @var array
	 */
	protected $default_settings = array();

	/**
	 * Default Settings Fields
	 *
	 * @var array
	 */
	protected $default_settings_fields = array();

	/**
	 * Allow Direct Submit.
	 *
	 * @var boolean
	 */
	protected $allow_direct_submit = true;

	/**
	 * Allow AJAX Save.
	 *
	 * @var boolean
	 */
	protected $allow_ajax_submit = false;

	/**
	 * User Cap to save.
	 *
	 * @var string
	 */
	protected $cap = 'administrator';

	/**
	 * Is the Settings autoloaded.
	 *
	 * @var boolean
	 */
	protected $autoload = false;

	/**
	 * Is WooCommerce Settings.
	 *
	 * @var boolean
	 */
	protected $is_woocommerce = false;

	/**
	 * Settings Nonce.
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * Tab Key.
	 *
	 * @var string
	 */
	protected $tab_key = 'tab';

	/**
	 * Settings Fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Is CPT settings.
	 *
	 * @var boolean
	 */
	protected $is_cpt = false;

	/**
	 * Default AJAX Search Endpoints.
	 *
	 * @var array
	 */
	protected $ajax_search_endpoints = array();

	/**
	 * Overwrite Assets.
	 *
	 * @var boolean
	 */
	protected $overwrite_assets = false;

	/**
	 * Is Woo Variation.
	 *
	 * @var boolean
	 */
	protected $is_variation = false;

	/**
	 * Init Settings.
	 *
	 * @return object
	 */
	public static function init() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Settings Constructor.
	 */
	private function __construct() {
		$this->setup();
		$this->base_hooks();
	}

	/**
	 * Setup Settings.
	 *
	 * @return void
	 */
	public function setup() {
		$this->prepare();
		$this->after_prepare();
		if ( is_admin() ) {
			$this->_set_fields();
		}
	}

	/**
	 * Set Fields.
	 *
	 * @return void
	 */
	private function _set_fields() {
		if ( empty( $this->fields ) ) {
			$this->set_fields();
			$this->prepare_default_settings();
		}
	}

	/**
	 * Hooks function.
	 *
	 * @return void
	 */
	public function base_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		if ( $this->is_variation ) {
			add_action( 'woocommerce_save_product_variation', array( $this, 'submit_save_variation_settings' ), 1000, 1 );
		} elseif ( $this->is_cpt ) {
			add_action( 'edit_post_' . $this->is_cpt, array( $this, 'submit_save_cpt_settings' ) );
		} else {
			add_action( 'wp_loaded', array( $this, 'submit_save_settings' ) );
		}

		if ( $this->allow_ajax_submit ) {
			add_action( 'wp_ajax_' . $this->id, array( $this, 'ajax_save_settings' ) );
		}

		if ( $this->is_woocommerce ) {
			add_action( $this->id . '-form-close-submit-fields', array( $this, 'woo_submit_fields' ) );
		}

		if ( method_exists( $this, 'hooks' ) ) {
			$this->hooks();
		}

		add_action( 'wp_ajax_' . $this->id . ( $this->is_variation ? '-variation' : '' ) . '-get-repeater-item', array( $this, 'ajax_get_repeater_item' ) );
		add_action( 'wp_ajax_' . $this->ajax_search_endpoints['posts'], array( $this, 'ajax_select_posts_search' ) );
		add_action( 'wp_ajax_' . $this->ajax_search_endpoints['products'], array( $this, 'ajax_select_products_search' ) );
		add_action( 'wp_ajax_' . $this->ajax_search_endpoints['taxs'], array( $this, 'ajax_select_taxs_search' ) );
		add_action( 'wp_ajax_' . $this->ajax_search_endpoints['users'], array( $this, 'ajax_select_users_search' ) );
	}

	/**
	 * AJAX Get Repeater Row HTML.
	 *
	 * @return void
	 */
	public function ajax_get_repeater_item() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->id . '-settings-nonce' ) ) {
			$key          = ! empty( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
			$index        = ! empty( $_POST['index'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['index'] ) ) ) : 0;
			$variation_id = ! empty( $_POST['variation_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) ) : 0;
			$rule_group = $this->get_default_repeater_field( $key, $index, $variation_id );
			$this->ajax_response( '', 'success', 200, 'get-rule-group', array( 'rule_group' => $rule_group ) );
		}
		$this->expired_response();
	}

	/**
	 * Ajax Select Posts Search.
	 *
	 * @return void
	 */
	public function ajax_select_posts_search() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->id . '-settings-nonce' ) ) {
			$result          = array();
			$search_text     = ! empty( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
			$post_type       = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
			$exclude         = ! empty( $_POST['exclude'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude'] ) ) : array();
			$query           = new \WP_Query(
				array(
					's'            => $search_text,
					'post__not_in' => $exclude,
					'post_type'    => $post_type,
				)
			);
			if ( $query->have_posts() ) {
				foreach ( $query->get_posts() as $_post ) {
					$result[] = array(
						'value' => $_post->ID,
						'label' => '#' . $_post->ID . ' ' . $_post->post_title,
					);
				}
			}
			wp_send_json( $result );
		}
		$this->expired_response();
	}

	/**
	 * Ajax Select Products Search.
	 *
	 * @return void
	 */
	public function ajax_select_products_search() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->id . '-settings-nonce' ) ) {
			$result          = array();
			$term            = ! empty( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
			$exclude         = ! empty( $_POST['exclude'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude'] ) ) : array();
			$data_store      = \WC_Data_Store::load( 'product' );
			$ids             = $data_store->search_products( $term, '', true, false, absint( apply_filters( 'woocommerce_json_search_limit', 30 ) ), array(), $exclude );
			$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
			$products        = array();

			foreach ( $product_objects as $product_object ) {
				$formatted_name = is_a( $product_object, '\WC_Product_variation' ) ? ( '#' . $product_object->get_id() . ' [' . $product_object->get_name() . '] ' . ( $product_object->get_sku() ? ' (' . $product_object->get_sku() . ')' : '' ) ) : $product_object->get_formatted_name();
				$products[]     = array(
					'value' => $product_object->get_id(),
					'label' => rawurldecode( $formatted_name ),
				);
			}

			$result = $products;

			wp_send_json( $result );
		}
		$this->expired_response();
	}

	/**
	 * Ajax Select Taxs Search.
	 *
	 * @return void
	 */
	public function ajax_select_taxs_search() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->id . '-settings-nonce' ) ) {
			$result      = array();
			$search_text = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
			$taxonomy    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';

			$args = array(
				'taxonomy'        => $taxonomy,
				'order'           => 'ASC',
				'hide_empty'      => false,
				'fields'          => 'all',
				'name__like'      => $search_text,
				'suppress_filter' => true,
			);

			$terms = get_terms( $args );

			foreach ( $terms as $term ) {
				$result[]     = array(
					'value' => $term->term_id,
					'label' => rawurldecode( '#' . $term->term_id . ' ' . $term->name ),
				);
			}

			wp_send_json( $result );
		}

		$this->expired_response();
	}

	/**
	 * Ajax Select Users Search.
	 *
	 * @return void
	 */
	public function ajax_select_users_search() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), $this->id . '-settings-nonce' ) ) {
			$result      = array();
			$search_text = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
			$args        = array(
				'number'         => -1,
				'search'         => '*' . $search_text . '*',
				'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
			);

			$user_search = new \WP_User_Query( $args );
			$users       = $user_search->get_results();

			foreach ( $users as $user ) {
				$result[]     = array(
					'value' => $user->ID,
					'label' => rawurldecode( '#' . $user->ID . ' | ' . $user->user_nicename . ' | ' . $user->user_email ),
				);
			}

			wp_send_json( $result );
		}

		$this->expired_response();
	}

	/**
	 * After Preparing Settings.
	 *
	 * @return void
	 */
	private function after_prepare() {
		$this->settings_key          = $this->id . '-settings-key';
		$this->nonce                 = wp_create_nonce( $this->id . '-settings-nonce' );
		$this->ajax_search_endpoints = array(
			'products' => $this->id . '-select-products-search',
			'posts'    => $this->id . '-select-posts-search',
			'taxs'     => $this->id . '-select-taxs-search',
			'users'    => $this->id . '-select-users-search',
		);
		if ( method_exists( $this, 'custom_search_endpoints' ) ) {
			$custom_endpoints            = $this->custom_search_endpoints();
			$this->ajax_search_endpoints = array_replace( $this->ajax_search_endpoints, $custom_endpoints );
		}
	}

	/**
	 * Get Search Endpoints.
	 *
	 * @return array
	 */
	public function get_ajax_search_endpoints( $endpoint ) {
		return $this->ajax_search_endpoints[ $endpoint ];
	}

	/**
	 * Get Settings Assets.
	 *
	 * @return array
	 */
	public function get_settings_assets() {
		$assets = array(
			array(
				'type'   => 'js',
				'handle' => 'select2',
				'url'    => self::$plugin_info['url'] . 'assets/libs/select2.full.min.js',
			),
			array(
				'type'   => 'css',
				'handle' => 'select2',
				'url'       => self::$plugin_info['url'] . 'assets/libs/select2.min.css',
			),
		);

		if ( ! $this->overwrite_assets ) {
			$assets[] = array(
				'type'      => 'js',
				'handle'    => self::$plugin_info['name'] . '-settings-actions',
				'url'       => self::$plugin_info['url'] . 'assets/libs/settings.min.js',
				// 'url'       => self::$plugin_info['url'] . 'assets/libs/settings.js',
				'localized' => array(
					'name' => 'gpls_core_settings_actions',
					'data' => array(
						'prefix'  => self::$plugin_info['prefix'],
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						'labels'  => array(
							'remove_item' => esc_html__( 'This item will be removed, proceed?' ),
							'search'      => esc_html__( 'Search...' ),
						),
						'actions' => array(
							'repeater_item_added' => esc_html__( $this->id . '-repeater-item-added' ),
						),
						'nonces'  => array(
							'settings_nonce' => wp_create_nonce( $this->id . '-settings-nonce' ),
						),
					),
				),
				array(
					'type'   => 'css',
					'handle' => self::$plugin_info['name'] . '-settings-notices',
					'url'    => self::$plugin_info['url'] . 'assets/libs/notice.min.css',
				),
			);
		}

		if ( method_exists( $this, 'custom_assets' ) ) {
			$assets = array_merge( $assets, $this->custom_assets() );
		}
		return $assets;
	}

	/**
	 * Set ID and Settings Fields.
	 *
	 * @return void
	 */
	abstract protected function prepare();

	/**
	 * Set Settings Fields.
	 *
	 * @return void
	 */
	abstract protected function set_fields();

	/**
	 * Get Default Settings.
	 *
	 * @return array
	 */
	protected function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Prepare Default settings.
	 *
	 * @return void
	 */
	protected function prepare_default_settings() {
		if ( ! empty( $this->default_settings ) && ! empty( $this->default_settings_fields ) ) {
			return;
		}
		$prepared_settings        = array();
		$prepared_settings_fields = array();

		foreach ( $this->fields as $tab_name => &$sections ) {
			foreach ( $sections as $section_name => &$section_settings ) {
				if ( ! empty( $section_settings['settings_list'] ) ) {
					foreach ( $section_settings['settings_list'] as $setting_name => &$setting_arr ) {
						$prepared_settings[ $setting_name ]                   = $setting_arr['value'];
						$prepared_settings_fields[ $setting_name ]            = $setting_arr;
						$prepared_settings_fields[ $setting_name ]['base_id'] = $this->id;
						$prepared_settings_fields[ $setting_name ]['key']     = $setting_name;
						$prepared_settings_fields[ $setting_name ]['filter']  = $setting_name;

						// Repeater Field.
						if ( 'repeater' === $setting_arr['type'] ) {
							foreach ( $setting_arr['default_subitem'] as $repeater_field_name => &$repeater_field_arr ) {
								$repeater_field_arr['filter'] = $setting_name . '-' . $repeater_field_name;
								$repeater_field_arr['key']    = $repeater_field_arr['key'] ?? $repeater_field_name;
							}
						}
					}
				}
			}
		}

		$this->default_settings        = $prepared_settings;
		$this->default_settings_fields = $prepared_settings_fields;
	}

	/**
	 * Get Option Settings.
	 *
	 * @param string $main_key
	 * @return array
	 */
	private function get_option_settings() {
		return (array) maybe_unserialize( get_option( $this->settings_key, $this->default_settings ) );
	}

	/**
	 * Get CPT Settings.
	 *
	 * @param int    $post_id
	 * @param string $main_key
	 * @return array
	 */
	private function get_cpt_settings( $post_id = null ) {
		if ( is_null( $post_id ) ) {
			global $post_id;
		}

		if ( ! $post_id ) {
			return array();
		}

		return (array) get_post_meta( $post_id, $this->settings_key, true );
	}

	/**
	 * Get Settings Values.
	 *
	 * @return mixed
	 */
	public function get_settings( $main_key = null, $post_id = null ) {
		$this->_set_fields();
		if ( $this->is_cpt ) {
			$settings = $this->get_cpt_settings( $post_id );
		} else {
			$settings = $this->get_option_settings();
		}
		if ( $settings ) {
			$settings = array_replace_recursive( $this->default_settings, $settings );
		} else {
			$settings = $this->default_settings;
		}

		// Handle sub-fields.
		foreach ( $this->default_settings_fields as $field_name => $field_arr ) {
			if ( ! empty( $field_arr['default_subitem'] ) ) {
				foreach ( $settings[ $field_name ] as $index => $subfield ) {
					$settings[ $field_name ][ $index ] = array_merge( array_combine( array_keys( $field_arr['default_subitem'] ), array_column( $field_arr['default_subitem'], 'value' ) ), $subfield );
				}
			}
		}

		if ( ! is_null( $main_key ) ) {
			if ( ! isset( $settings[ $main_key ] ) ) {
				return false;
			}
			return $settings[ $main_key ];
		}
		return $settings;
	}

	/**
	 * Get Tab Settings Values.
	 *
	 * @return array|string
	 */
	public function get_tab_settings( $tab_name, $section = '' ) {
		$settings = $this->get_option_settings();
		if ( $settings ) {
			$settings = array_replace_recursive( $this->default_settings, $settings );
		} else {
			$settings = $this->default_settings;
		}

		$tab_fields = $this->get_tab_fields( $tab_name );

		if ( $section ) {
			$fields_keys = array_keys( $tab_fields[ $section ]['settings_list'] );
		} else {
			$fields_keys = array_keys( array_merge( ...array_column( array_values( $tab_fields ), 'settings_list' ) ) );
		}

		$settings = array_intersect_key( $settings, array_flip( $fields_keys ) );

		return $settings;
	}
}
