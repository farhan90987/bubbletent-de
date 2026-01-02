<?php

if (!defined('ABSPATH')) {
	exit;
}

class Custom_Stats_Admin_Page {
	public static $menu_slug = 'custom-stats-dashboard';

	public static function init() {
		add_action('admin_menu', [__CLASS__, 'register_menu']);
	}

	public static function register_menu() {
		add_menu_page(
			'Custom Stats',
			'Custom Stats',
			'manage_options',
			self::$menu_slug,
			[__CLASS__, 'render_page'],
			'dashicons-chart-area',
			58
		);
	}

	public static function render_page() {
		echo '<div id="custom-stats-root"></div>';
	}
}
