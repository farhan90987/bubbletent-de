<?php 
/*
 * Plugin Name:       MW Elementor Widgets
 * Plugin URI:        http://masum-billah.com
 * Description:       Additional Elementor Widgets by Mediusware.com
 * Version:           1.0.2
 * Requires at least: 6.2
 * Requires PHP:      7.2
 * Author:            Mediusware
 * Author URI:        https://mediusware.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://mediusware.com
 * Text Domain:       mwew
 * Domain Path:       /languages
 * Requires Plugins:  elementor, elementor-pro, listeo-core, smoobu-sync-wp
 */

defined( 'ABSPATH' ) || exit;


// if (
//     (defined('XMLRPC_REQUEST') || defined('REST_REQUEST') || (defined('WP_INSTALLING') && WP_INSTALLING) || wp_doing_ajax())
// ) {
//     @ini_set('display_errors', 1);
// }


require_once __DIR__ . '/autoloader.php';

if( ! defined('MWEW_VERSION') ) define( 'MWEW_VERSION', '1.0.0' );

if( ! defined('MWEW_DB_VERSION') ) define( 'MWEW_DB_VERSION', '1.0.0' );


if( ! defined('MWEW_DIR_PATH') ) define( 'MWEW_DIR_PATH', plugin_dir_path(__FILE__) );

if( ! defined('MWEW_PATH_URL') ) define( 'MWEW_PATH_URL', plugin_dir_url(__FILE__) );


function mwew_plugin_init() {
    new MWEW\Inc\Mwew_Init();
}
add_action( 'init', 'mwew_plugin_init' );

register_activation_hook( __FILE__, [ 'MWEW\Inc\Mwew_Init', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'MWEW\Inc\Mwew_Init', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'MWEW\Inc\Mwew_Init', 'uninstall' ] );