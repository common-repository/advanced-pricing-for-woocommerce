<?php
namespace GPLSCore\GPLS_PLUGIN_WOOADPG\Pages\PagesBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_WOOADPG\Pages\SettingsPage;

/**
 * Init Pages.
 */
function setup_pages() {
	SettingsPage::init();
}
