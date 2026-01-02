<?php
/**
 * Plugin Name:       WPForms Surveys and Polls
 * Plugin URI:        https://wpforms.com
 * Description:       Create Surveys and Polls with WPForms.
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.16.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-surveys-polls
 * Domain Path:       /languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

use WPFormsSurveys\Loader;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
const WPFORMS_SURVEYS_POLLS_VERSION = '1.16.0';

/**
 * Plugin file.
 *
 * @since 1.0.0
 */
const WPFORMS_SURVEYS_POLLS_FILE = __FILE__;

/**
 * Plugin path.
 *
 * @since 1.12.0
 */
define( 'WPFORMS_SURVEYS_POLLS_PATH', plugin_dir_path( WPFORMS_SURVEYS_POLLS_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 1.6.1
 * @since 1.12.0 Renamed from wpforms_surveys_polls_required to wpforms_surveys_polls_load.
 * @since 1.12.0 Uses requirements feature.
 */
function wpforms_surveys_polls_load() {

	$requirements = [
		'file'    => WPFORMS_SURVEYS_POLLS_FILE,
		'wpforms' => '1.9.4',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_surveys_polls();
}

add_action( 'wpforms_loaded', 'wpforms_surveys_polls_load' );

/**
 * Get the instance of the addon main class.
 *
 * @since 1.11.0
 *
 * @return Loader
 */
function wpforms_surveys_polls() {

	require_once WPFORMS_SURVEYS_POLLS_PATH . 'vendor/autoload.php';

	return Loader::get_instance();
}
