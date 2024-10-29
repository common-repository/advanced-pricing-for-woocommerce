<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\SettingsBase;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WOOADPG\Settings\MainSettings;

/**
 * Setup Metaboxes.
 *
 * @return void
 */
function setup_settings() {
    MainSettings::init();
}
