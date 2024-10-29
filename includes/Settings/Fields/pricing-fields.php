<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Setup Settings Fields.
 *
 * @return array
 */
function pricing_fields( $settings, $core, $plugin_info ) {
	return array(
		'general' => array(
			'general'       => array(
				'settings_list' => array(
					'status'                        => array(
						'field_heading'            => esc_html__( 'Pricing Model', 'advanced-pricing-for-woocommerce' ),
						'field_heading_classes'    => 'bg-primary p-3 w-100 fs-3 text-white text-center',
						'field_subheading'         => esc_html__( 'Set Quantity-based pricing model', 'advanced-pricing-for-woocommerce' ),
						'field_subheading_classes' => 'mb-5',
						'key'                      => 'status',
						'wrapper_classes'          => 'col-lg-12 border shadow-sm p-2',
						'input_label'              => esc_html__( 'Enable pricing model', 'advanced-pricing-for-woocommerce' ),
						'classes'                  => 'pricing-status',
						'type'                     => 'checkbox',
						'value'                    => 'off',
					),
					'pricing_model'                 => array(
						'key'             => 'pricing_model',
						'wrapper_classes' => 'col-lg-12 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Choose pricing model', 'advanced-pricing-for-woocommerce' ),
						'classes'         => 'pricing-model',
						'type'            => 'radio',
						'value'           => 1,
						'options'         => array(
							array(
								'input_suffix' => esc_html__( 'All-Units | Volume Pricing Model', 'advanced-pricing-for-woocommerce' ) . '<button type="button" class="ms-2 py-2 px-3 ' . $plugin_info['prefix'] . '-guide-btn btn btn-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#all-units-pricing-model-guide">?</button>',
								'value'        => 1,
							),
							array(
								'input_suffix' => esc_html__( 'Tiered | Incremental Pricing Model', 'advanced-pricing-for-woocommerce' ) . '<button type="button" class="ms-2 py-2 px-3 ' . $plugin_info['prefix'] . '-guide-btn btn btn-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#tiered-pricing-model-guide">?</button>',
								'value'        => 2,
							),
							array(
								'input_suffix' => esc_html__( 'Package Pricing Model', 'advanced-pricing-for-woocommerce' ) . '<button type="button" class="ms-2 py-2 px-3 ' . $plugin_info['prefix'] . '-guide-btn btn btn-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#package-pricing-model-guide">?</button>'  . $core->pro_btn( '', 'Pro', '', '', true ),
								'value'        => 3,
								'attrs'        => array(
									'disabled' => 'disabled',
								),
							),
							array(
								'input_suffix' => esc_html__( 'Quantity Breaks Model', 'advanced-pricing-for-woocommerce' ) . '<button type="button" class="ms-2 py-2 px-3 ' . $plugin_info['prefix'] . '-guide-btn btn btn-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#qtybreaks-pricing-model-guide">?</button>'  . $core->pro_btn( '', 'Pro', '', '', true ),
								'value'        => 5,
								'attrs'        => array(
									'disabled' => 'disabled',
								),
							),
							array(
								'input_suffix' => esc_html__( 'Name your price Pricing Model', 'advanced-pricing-for-woocommerce' ),
								'value'        => 4,
							),
						),
					),
					'name_your_price_min_price'     => array(
						'key'             => 'name_your_price_min_price',
						'wrapper_classes' => 'col-lg-3 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Min price', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', 'margin-top:5px;', true ),
						'classes'         => 'name-your-price-min-price',
						'type'            => 'number',
						'value'           => 0,
						'inline'          => false,
						'attrs'           => array(
							'min'  => 0,
							'step' => 'any',
							'disabled' => 'disabled',
						),
						'collapse'        => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
							),
						),
					),
					'name_your_price_default_price' => array(
						'key'             => 'name_your_price_default_price',
						'wrapper_classes' => 'col-lg-3 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Default price', 'advanced-pricing-for-woocommerce' ),
						'classes'         => 'name-your-price-default-price',
						'type'            => 'number',
						'value'           => 0,
						'inline'          => false,
						'attrs'           => array(
							'min'      => 0,
							'step'     => 'any',
							'required' => true,
						),
						'collapse'        => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
							),
						),
					),
					'name_your_price_max_price'     => array(
						'key'             => 'name_your_price_max_price',
						'wrapper_classes' => 'col-lg-3 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Max price', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', 'margin-top:5px;', true ),
						'classes'         => 'name-your-price-max-price',
						'type'            => 'number',
						'value'           => '',
						'inline'          => false,
						'attrs'           => array(
							'min'  => 0,
							'step' => 'any',
							'disabled' => 'disabled',
						),
						'collapse'        => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
							),
						),
					),
					'name_your_price_hide_price'     => array(
						'key'             => 'name_your_price_hide_price',
						'wrapper_classes' => 'col-lg-3 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Hide original price', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', 'margin-top:5px;', true ),
						'classes'         => 'name-your-price-hide-price',
						'type'            => 'checkbox',
						'value'           => 'on',
						'inline'          => false,
						'collapse'        => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
							),
						),
						'attrs'           => array(
							'disabled' => 'disabled',
						),
					),
					'pricing_type'                  => array(
						'key'             => 'pricing_type',
						'wrapper_classes' => 'col-lg-12 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Set pricing table', 'advanced-pricing-for-woocommerce' ),
						'classes'         => 'pricing-type',
						'type'            => 'radio',
						'value'           => 1,
						'options'         => array(
							array(
								'input_suffix' => esc_html__( 'Manual table', 'advanced-pricing-for-woocommerce' ),
								'input_footer' => esc_html__( 'Set the pricing table manually.', 'advanced-pricing-for-woocommerce' ),
								'value'        => 1,
							),
							array(
								'input_suffix' => esc_html__( 'Dynamic table', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
								'input_footer' => esc_html__( 'Choose end price and change price rate per quantity, and the price will be calculated dynamically based on cart item quantity.', 'advanced-pricing-for-woocommerce' ),
								'classes'      => $plugin_info['classes_general'] . '-pro-field',
								'value'        => 2,
								'attrs'        => array(
									'disabled' => 'disabled',
								),
							),
						),
						'collapse'        => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
					),
					'direct_pricing'                => array(
						'inline'                         => false,
						'type'                           => 'repeater',
						'classes'                        => 'border p-3 shadow-sm bg-light',
						'input_label'                    => esc_html__( 'Manual Pricing Table', 'advanced-pricing-for-woocommerce' ),
						'input_label_classes'            => 'bg-success p-3 w-100 fs-3 text-white text-center',
						'input_label_subheading_classes' => 'w-100 text-center fs-6',
						'input_label_subheading'         => esc_html__( 'Set exact price for exact quantity.', 'advanced-pricing-for-woocommerce' ),
						'repeat_add_label'               => esc_html__( 'Add pricing rule', 'advanced-pricing-for-woocommerce' ),
						'show_divider'                   => false,
						'value'                          => array(),
						'default_subitem'                => array(
							'quantity' => array(
								'key'             => 'quantity',
								'wrapper_classes' => 'col-lg-6 border shadow-sm p-2',
								'input_label'     => esc_html__( 'Quantity', 'advanced-pricing-for-woocommerce' ),
								'classes'         => 'direct-pricing-qty',
								'type'            => 'number',
								'value'           => 2,
								'attrs'           => array(
									'min' => 1,
								),
							),
							'price'    => array(
								'key'             => 'price',
								'wrapper_classes' => 'col-lg-6 border shadow-sm p-2',
								'input_label'     => esc_html__( 'Price', 'advanced-pricing-for-woocommerce' ),
								'classes'         => 'direct-pricing-price',
								'type'            => 'number',
								'value'           => 1,
								'attrs'           => array(
									'min'  => 0,
									'step' => 'any',
								),
							),
						),
						'collapse'                       => array(
							array(
								'collapse_source' => 'pricing-type',
								'collapse_value'  => 1,
							),
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
					),
					'manual_table_pricing_swatches' => array(
						'key'                 => 'manual_table_pricing_swatches',
						'wrapper_classes'     => 'col-lg-4 border shadow-sm p-2',
						'label_wrapper_width' => 'col-lg-4',
						'input_wrapper_width' => 'col-lg-4',
						'input_label'         => esc_html__( 'Quantity Swatches', 'advanced-pricing-for-woocommerce' ),
						'input_footer'        => esc_html__( 'Display pricing table as quantity swatches in frontend.', 'advanced-pricing-for-woocommerce' ),
						'classes'             => 'manual-table-pricing-swatches',
						'type'                => 'checkbox',
						'value'               => 'no',
						'inline'              => false,
						'collapse'            => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
					),
					'manual_table_pricing_swatches_default' => array(
						'key'                 => 'manual_table_pricing_swatches_default',
						'wrapper_classes'     => 'col-lg-4 border shadow-sm p-2',
						'label_wrapper_width' => 'col-lg-4',
						'input_wrapper_width' => 'col-lg-4',
						'input_label'         => esc_html__( 'Default Swatch', 'advanced-pricing-for-woocommerce' ),
						'input_footer'        => esc_html__( 'Set default selected quantity swatch, set the pricing row starting quantity', 'advanced-pricing-for-woocommerce' ),
						'classes'             => 'manual-table-pricing-swatches-default',
						'type'                => 'number',
						'value'               => 0,
						'inline'              => false,
						'collapse'            => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
					),
					'manual_table_pricing_swatches_show_qty_field' => array(
						'key'                 => 'manual_table_pricing_swatches_show_qty_field',
						'wrapper_classes'     => 'col-lg-4 border shadow-sm p-2',
						'label_wrapper_width' => 'col-lg-4',
						'input_wrapper_width' => 'col-lg-4',
						'input_label'         => esc_html__( 'Keep Quantity field', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', 'margin-top:5px;', true ),
						'input_footer'        => esc_html__( 'Enabling the quantity swatches will hide the quantity field by default, check this box to keep showing the quantity field.', 'advanced-pricing-for-woocommerce' ),
						'classes'             => 'manual-table-pricing-swatches-show-qty-field',
						'type'                => 'checkbox',
						'value'               => 'no',
						'inline'              => false,
						'collapse'            => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
						'attrs' => array(
							'disabled' => 'disabled',
						),
					),
					'schedule_start'                => array(
						'key'                         => 'schedule_start',
						'wrapper_classes'             => 'col-lg-6 border shadow-sm p-2',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Schedule Start', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'input_suffix'                => '<a href="#" class="' . esc_attr( $plugin_info['prefix'] . '-reset-schedule' ) . '" >' . esc_html__( 'Reset' ) . '</a>',
						'classes'                     => 'schedule-start',
						'type'                        => 'datetime-local',
						'value'                       => '',
						'attrs' => array(
							'disabled' => 'disabled',
						)
					),
					'schedule_end'                  => array(
						'key'                         => 'schedule_end',
						'wrapper_classes'             => 'col-lg-6 border shadow-sm p-2',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Schedule End', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'input_suffix'                => '<a href="#" class="' . esc_attr( $plugin_info['prefix'] . '-reset-schedule' ) . '" >' . esc_html__( 'Reset' ) . '</a>',
						'classes'                     => 'schedule-end',
						'type'                        => 'datetime-local',
						'value'                       => '',
						'attrs' => array(
							'disabled' => 'disabled',
						)
					),
					'customers'                     => array(
						'key'                         => 'customers',
						'type'                        => 'select',
						'wrapper_classes'             => 'col-lg-12 border shadow-sm p-2',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Customers', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'input_footer'                => esc_html__( 'Select only specific users to apply this pricing for', 'advanced-pricing-for-woocommerce' ),
						'classes'                     => 'select2-input customers',
						'multiple'                    => true,
						'value'                       => array(),
						'select_type'                 => 'users',
						'search_data'                 => array(
							'action' => $settings->get_ajax_search_endpoints( 'users' ),
						),
						'attrs'                       => array(
							'data-select_args' => wp_json_encode(
								array(
									'allowClear'  => true,
									'placeholder' => array(
										'id'   => '-1',
										'text' => esc_html__( 'Search a user', 'gpls-waadtct-woo-advanced-pricing' ),
									),
								)
							),
							'disabled' => 'disabled',
						),
					),
					'pricing_table_html_status'     => array(
						'key'                         => 'pricing_table_html_status',
						'wrapper_classes'             => 'col-lg-12 border shadow-sm p-2',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Pricing Table', 'advanced-pricing-for-woocommerce' ),
						'input_footer'                => esc_html__( 'Display pricing table after the product summary', 'advanced-pricing-for-woocommerce' ),
						'classes'                     => 'pricing-table-html-status',
						'type'                        => 'checkbox',
						'value'                       => 'off',
						'collapse'                    => array(
							array(
								'collapse_source' => 'pricing-model',
								'collapse_value'  => 4,
								'collapse_reverse' => true,
							),
						),
					),
				),
			),
			// Dynamic Price based on Sold Qty.
			'dynamic_price' => array(
				'settings_list' => array(
					'dynamic_price_status'               => array(
						'key'                         => 'dynamic_price_status',
						'field_heading'               => esc_html__( 'Dynamic price', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'field_heading_classes'       => 'bg-primary p-3 w-100 fs-3 text-white text-center',
						'field_subheading'            => esc_html__( 'Update the product price after reaching specific sold quantity', 'advanced-pricing-for-woocommerce' ),
						'field_subheading_classes'    => 'mb-5',
						'wrapper_classes'             => 'col-lg-12 border shadow-sm p-2',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Enable Dynamic price', 'advanced-pricing-for-woocommerce' ),
						'classes'                     => 'dynamic-price-status',
						'type'                        => 'checkbox',
						'value'                       => 'off',
						'attrs' => array(
							'disabled' => 'disabled',
						)
					),
					'dynamic_price_counter'              => array(
						'key'                         => 'dynamic_price_counter',
						'wrapper_classes'             => 'col-lg-12 border shadow-sm p-2',
						'label_wrapper_width'         => 'col-lg-8',
						'input_wrapper_width'         => 'col-lg-4',
						'input_field_wrapper_classes' => 'd-flex align-items-center',
						'input_label'                 => esc_html__( 'Sales monitor', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'input_footer'                => esc_html__( 'This counter will be updated with product\'s sales count. The sales counter will be incremented for upcoming completed orders.', 'advanced-pricing-for-woocommerce' ),
						'classes'                     => 'dynamic-price-counter',
						'type'                        => 'number',
						'value'                       => 0,
						'disable'                     => true,
						'attrs'                       => array(
							'disabled' => 'disabled',
						),
					),
					'dynamic_price_pricing_type'         => array(
						'key'             => 'dynamic_price_pricing_type',
						'wrapper_classes' => 'col-lg-12 border shadow-sm p-2',
						'input_label'     => esc_html__( 'Set pricing table', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'classes'         => 'dynamic-price-pricing-type ' . $plugin_info['classes_general'] . '-pro-field',
						'type'            => 'radio',
						'value'           => 1,
						'options'         => array(
							array(
								'input_suffix' => esc_html__( 'Manual table', 'advanced-pricing-for-woocommerce' ),
								'input_footer' => esc_html__( 'Set the pricing table manually.', 'advanced-pricing-for-woocommerce' ),
								'value'        => 1,
							),
							array(
								'input_suffix' => esc_html__( 'Dynamic table', 'advanced-pricing-for-woocommerce' ),
								'input_footer' => esc_html__( 'Choose end price and change price rate per quantity, and the price will be calculated dynamically based on the product\'s sales count.', 'advanced-pricing-for-woocommerce' ),
								'value'        => 2,
							),
						),
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'dynamic_price_direct_pricing'       => array(
						'inline'                         => false,
						'type'                           => 'repeater',
						'classes'                        => 'border p-3 shadow-sm bg-light ' . $plugin_info['classes_general'] . '-pro-field',
						'input_label'                    => esc_html__( 'Manual Pricing Table', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', '', true ),
						'input_label_classes'            => 'bg-success p-3 w-100 fs-3 text-white text-center',
						'input_label_subheading_classes' => 'w-100 text-center fs-6',
						'input_label_subheading'         => esc_html__( 'Set exact price after reaching exact sold quantity.', 'advanced-pricing-for-woocommerce' ),
						'repeat_add_label'               => esc_html__( 'Add pricing rule', 'advanced-pricing-for-woocommerce' ),
						'repeat_add_attrs'               => array(
							'disabled' => 'disabled',
						),
						'show_divider'                   => false,
						'value'                          => array(),
						'default_subitem'                => array(
							'quantity' => array(
								'key'             => 'quantity',
								'wrapper_classes' => 'col-lg-6 border shadow-sm p-2',
								'input_label'     => esc_html__( 'Quantity', 'advanced-pricing-for-woocommerce' ),
								'classes'         => 'dynamic-price-manual-table-qty',
								'type'            => 'number',
								'value'           => 2,
								'attrs'           => array(
									'min' => 1,
								),
							),
							'price'    => array(
								'key'             => 'price',
								'wrapper_classes' => 'col-lg-6 border shadow-sm p-2',
								'input_label'     => esc_html__( 'Price', 'advanced-pricing-for-woocommerce' ),
								'classes'         => 'dynamic-price-manual-table-price',
								'type'            => 'number',
								'value'           => 1,
								'attrs'           => array(
									'min'  => 0,
									'step' => 'any',
								),
							),
						),
						'collapse'                       => array(
							'collapse_source' => 'dynamic-price-pricing-type',
							'collapse_value'  => 1,
						),
						'attrs' => array(
							'disabled' => 'disabled',
						),
					),
				),
			),
		),
	);
}

/**
 * Setup Main Settings Fields.
 *
 * @return array
 */
function main_fields( $settings, $core, $plugin_info ) {
	return array(
		'general' => array(
			'quatity_swatches' => array(
				'section_title' => esc_html__( 'Quantity Swatches styles', 'advanced-pricing-for-woocommerce' ) . $core->pro_btn( '', 'Pro', '', 'margin-top:5px;', true ),
				'settings_list' => array(
					'header_background_color' => array(
						'key'          => 'header_background_color',
						'input_label'  => esc_html__( 'Header background', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Header background color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'header-background-color',
						'type'         => 'color',
						'value'        => '#5175dc',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'header_text_color'       => array(
						'key'          => 'header_text_color',
						'input_label'  => esc_html__( 'Header front', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Header text color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'header-text-color',
						'type'         => 'color',
						'value'        => '#FFFFFF',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'body_background_color'   => array(
						'key'          => 'body_background_color',
						'input_label'  => esc_html__( 'Swatch Background', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Swatch body default background color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'body-background-color',
						'type'         => 'color',
						'value'        => '#ececec',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'body_text_color'         => array(
						'key'          => 'body_text_color',
						'input_label'  => esc_html__( 'Swatch front', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Swatch body default text color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'body-text-color',
						'type'         => 'color',
						'value'        => '#000',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'body_active_background'  => array(
						'key'          => 'body_active_background',
						'input_label'  => esc_html__( 'Active swatch background', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Selected swatch body background color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'body-active-background-color',
						'type'         => 'color',
						'value'        => '#009dff',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'body_active_text_color'  => array(
						'key'          => 'body_active_text_color',
						'input_label'  => esc_html__( 'Active swatch front', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Selected swatch body text color.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'body-active-text-color',
						'type'         => 'color',
						'value'        => '#FFFFFF',
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
					'swatch_template'         => array(
						'key'          => 'swatch_template',
						'input_label'  => esc_html__( 'Swatch template', 'advanced-pricing-for-woocommerce' ),
						'input_footer' => esc_html__( 'Select swatch template between different swatch structures.', 'advanced-pricing-for-woocommerce' ),
						'classes'      => 'swatch-template',
						'type'         => 'select',
						'value'        => '1',
						'options'      => array(
							'1' => esc_html__( 'Template 1', 'advanced-pricing-for-woocommerce' ),
							'2' => esc_html__( 'Template 2', 'advanced-pricing-for-woocommerce' ),
							'3' => esc_html__( 'Template 3', 'advanced-pricing-for-woocommerce' ),
							'4' => esc_html__( 'Template 4', 'advanced-pricing-for-woocommerce' ),
						),
						'attrs'        => array(
							'disabled' => 'disabled',
						),
					),
				),
			),
			// 'pricing_table' => array(
			// 'section_title' => esc_html__( 'Pricing Table styles', 'advanced-pricing-for-woocommerce' ),
			// 'settings_list' => array(
			// ),
			// ),
		),
	);
}
