<?php

/**
 * Import export for WooCommerce
 *
 *
 * @link              https://www.webtoffee.com/
 * @since             1.0.0
 * @package           Wt_Import_Export_For_Woo
 *
 * @wordpress-plugin
 * Plugin Name:       Import Export for WooCommerce Wrapper
 * Plugin URI:        https://www.webtoffee.com/product/woocommerce-import-export-suite/
 * Description:       Import Export Wrapper for WooCommerce
 * Version:           1.1.7
 * Author:            WebToffee
 * Author URI:        https://www.webtoffee.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wt-import-export-for-woo
 * Domain Path:       /languages
 * WC tested up to:   6.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define ( 'WT_IEW_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define ( 'WT_IEW_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define ( 'WT_IEW_PLUGIN_URL', plugin_dir_url(__FILE__));
define ( 'WT_IEW_PLUGIN_FILENAME', __FILE__);
define ( 'WT_IEW_SETTINGS_FIELD', 'wt_import_export_for_woo');
define ( 'WT_IEW_ACTIVATION_ID', 'wt-import-export-for-woo');
define ( 'WT_IEW_TEXT_DOMAIN', 'wt-import-export-for-woo');
define ( 'WT_IEW_PLUGIN_ID', 'wt_import_export_for_woo');
define ( 'WT_IEW_PLUGIN_NAME','Import Export for WooCommerce');
define ( 'WT_IEW_PLUGIN_DESCRIPTION','Import and Export From and To your WooCommerce Store.');
define ( 'WT_IEW_DEBUG_PRO_TROUBLESHOOT', 'https://www.webtoffee.com/finding-php-error-logs/' );

define ( 'WT_IEW_DEBUG', false );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WT_IEW_VERSION', '1.1.7' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt-import-export-for-woo-activator.php
 */
function activate_wt_import_export_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-activator.php';
	Wt_Import_Export_For_Woo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt-import-export-for-woo-deactivator.php
 */
function deactivate_wt_import_export_for_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-deactivator.php';
	Wt_Import_Export_For_Woo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wt_import_export_for_woo' );
register_deactivation_hook( __FILE__, 'deactivate_wt_import_export_for_woo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo.php';

$advanced_settings = get_option('wt_iew_advanced_settings', array());
$ier_get_max_execution_time = (isset($advanced_settings['wt_iew_maximum_execution_time']) && $advanced_settings['wt_iew_maximum_execution_time'] != '') ? $advanced_settings['wt_iew_maximum_execution_time'] : ini_get('max_execution_time');
if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) { 
	$current_execution_time =  ini_get( 'max_execution_time' );
	if( $current_execution_time < $ier_get_max_execution_time ){
		@set_time_limit($ier_get_max_execution_time); 
	}
} 


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wt_import_export_for_woo() {

	$plugin = new Wt_Import_Export_For_Woo();
	$plugin->run();

}
if(get_option('wt_iew_is_active'))
{
	run_wt_import_export_for_woo();
}
