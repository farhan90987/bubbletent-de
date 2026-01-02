<?php

if (!defined('ABSPATH')) {
	exit;
}

class Custom_Stats_Assets {
	public static function init() {
		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
	}

	private static function get_manifest_path() {
		$modern = trailingslashit(CUSTOM_STATS_PLUGIN_DIR) . 'assets/.vite/manifest.json';
		$legacy = trailingslashit(CUSTOM_STATS_PLUGIN_DIR) . 'assets/manifest.json';
		return file_exists($modern) ? $modern : $legacy;
	}

	private static function to_asset_url($relative_path) {
		$path = ltrim($relative_path, '/');
		// Wenn der Manifest-Pfad nicht mit 'assets/' beginnt, im Plugin-Kontext prefixen
		if (strpos($path, 'assets/') !== 0) {
			$path = 'assets/' . $path;
		}
		return trailingslashit(CUSTOM_STATS_PLUGIN_URL) . $path;
	}

	public static function enqueue_admin_assets($hook) {
		$screen = get_current_screen();
		if (!$screen || $screen->base !== 'toplevel_page_custom-stats-dashboard') {
			return;
		}

		$manifest_path = self::get_manifest_path();
		if (!file_exists($manifest_path)) {
			return; // noch nichts gebaut
		}

		$json = file_get_contents($manifest_path);
		$manifest = json_decode($json, true);
		if (!is_array($manifest)) {
			return;
		}

		$entry = $manifest['index.html']
			?? $manifest['src/main.jsx']
			?? $manifest['/src/main.jsx']
			?? null;
		if (!$entry) {
			return;
		}

		if (!empty($entry['file'])) {
			$js_url = self::to_asset_url($entry['file']);
			wp_enqueue_script(
				'custom-stats-app',
				$js_url,
				['wp-api', 'wp-element'],
				null,
				true
			);
			wp_localize_script('custom-stats-app', 'wpApiSettings', [
				'root'  => esc_url_raw(rest_url()),
				'nonce' => wp_create_nonce('wp_rest')
			]);
		}

		if (!empty($entry['css']) && is_array($entry['css'])) {
			foreach ($entry['css'] as $css_file) {
				$css_url = self::to_asset_url($css_file);
				wp_enqueue_style(
					'custom-stats-style-' . md5($css_file),
					$css_url,
					[],
					null
				);
			}
		}
	}
}
