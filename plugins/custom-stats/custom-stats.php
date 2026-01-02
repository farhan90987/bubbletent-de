<?php
/**
 * Plugin Name: BaB Custom Stats
 * Description: Book a Bubble Stats Admin-Dashboard 
 * Version: 0.1.0
 * Author: Fouad Gantri
 * Requires Plugins: woocommerce
 */
if (!defined('ABSPATH')) {
	exit;
}

define('CUSTOM_STATS_PLUGIN_FILE', __FILE__);
define('CUSTOM_STATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOM_STATS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CUSTOM_STATS_PLUGIN_DIR . 'utils.php';
require_once CUSTOM_STATS_PLUGIN_DIR . 'includes/AdminPage.php';
require_once CUSTOM_STATS_PLUGIN_DIR . 'includes/Assets.php';
require_once CUSTOM_STATS_PLUGIN_DIR . 'includes/Rest.php';


register_activation_hook(__FILE__, function () {
	if (!class_exists('WooCommerce')) {
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die('Custom Stats benötigt WooCommerce.');
	}
});

add_action('plugins_loaded', function () {
	if (!class_exists('WooCommerce')) {
		return;
	}
	Custom_Stats_Admin_Page::init();
	Custom_Stats_Assets::init();
	Custom_Stats_Rest::init();
});



console_log(45);
console_log('Hello');
console_log(['Hello' => 1, 'World' => 2]);
console_log(new stdClass());