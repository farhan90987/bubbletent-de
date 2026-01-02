<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MetaExport {
    public function __construct() {
        add_action('wp_ajax_export_meta_data', [$this, 'export_meta_data']);
        add_action('wp_ajax_get_wpml_languages', [$this, 'get_wpml_languages']);
    }

    public function render_export_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $languages = apply_filters('wpml_active_languages', NULL, ['skip_missing' => 0]);
        $default_language = apply_filters('wpml_default_language', NULL);

        echo '<div class="wrap">';
        echo '<h1>Meta Export</h1>';
        echo '<form id="meta-export-form" method="post">';
        
        // Export Type Dropdown
        echo '<label for="export-type">Select Type:</label>';
        echo '<select id="export-type" name="export_type">';
        echo '<option value="pages">Pages</option>';
        echo '<option value="posts">Posts</option>';
        echo '<option value="products">Products</option>';
        echo '<option value="listings">Listings</option>';
        echo '</select>';

        // Language Dropdown
        echo '<label for="export-language">Select Language:</label>';
        echo '<select id="export-language" name="export_language">';
        if ($languages) {
            foreach ($languages as $code => $lang) {
                $selected = ($code === $default_language) ? 'selected' : '';
                echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($lang['translated_name']) . '</option>';
            }
        }
        echo '</select>';

        echo '<button type="button" id="export-button" class="button button-primary">Export</button>';
        echo '</form>';
        echo '<div id="export-result"></div>';
        echo '</div>';
    }

    public function export_meta_data() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');

        global $wpdb;

        $export_type = isset($_POST['export_type']) ? sanitize_text_field($_POST['export_type']) : '';
        $export_language = isset($_POST['export_language']) ? sanitize_text_field($_POST['export_language']) : '';

        if (!in_array($export_type, ['pages', 'posts', 'products', 'listings'])) {
            wp_send_json_error(['message' => 'Invalid export type.']);
        }

        // Get available WPML languages
        $available_languages = apply_filters('wpml_active_languages', NULL, ['skip_missing' => 0]);
        $default_language = apply_filters('wpml_default_language', NULL);

        // Ensure the selected language is valid
        if (!$export_language || (!isset($available_languages[$export_language]) && $export_language !== $default_language)) {
            wp_send_json_error(['message' => 'Invalid language selection.']);
        }

        // Determine post type
        $post_type = '';
        switch ($export_type) {
            case 'pages': $post_type = 'page'; break;
            case 'posts': $post_type = 'post'; break;
            case 'products': $post_type = 'product'; break;
            case 'listings': $post_type = 'listing'; break;
        }

        // Fetch only translated posts for the selected language
        $query = $wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_title, p.post_type, p.guid, p.post_status, t.trid
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id
            WHERE p.post_type = %s
              AND p.post_status IN ('publish', 'private', 'draft')
              AND t.language_code = %s",
            $post_type,
            $export_language
        );

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            wp_send_json_error(['message' => 'No data found for the selected type and language.']);
        }

        // Ensure the exports directory exists
        $exports_dir = plugin_dir_path(__FILE__) . 'exports';
        if (!is_dir($exports_dir)) {
            if (!mkdir($exports_dir, 0755, true) && !is_dir($exports_dir)) {
                wp_send_json_error(['message' => 'Failed to create exports directory.']);
            }
        }

        // Prepare CSV
        $file_name = 'meta_export_' . $export_type . '_' . $export_language . '_' . date('Y-m-d_H-i-s') . '.csv';
        $file_path = $exports_dir . '/' . $file_name;

        $file = fopen($file_path, 'w');
        if (!$file) {
            wp_send_json_error(['message' => 'Failed to create the export file.']);
        }

        // Ensure UTF-8 BOM for special characters
        fwrite($file, chr(239) . chr(187) . chr(191));

        // Write CSV headers
        fputcsv($file, ['ID', 'Title', 'Type', 'URL', 'SEO Title', 'Meta Description']);

        $exported_posts = [];

        // Write data rows
// Write data rows
foreach ($results as $row) {
    // Get the correct translated post ID
    $translated_post_id = apply_filters('wpml_object_id', $row->ID, $row->post_type, false, $export_language);

    // Ensure each post is exported only once
    if (in_array($translated_post_id, $exported_posts)) {
        continue;
    }

    // Fetch correct translated slug
    $translated_slug = get_post_field('post_name', $translated_post_id);

    // Get base URL of the site
    $site_url = get_site_url();

    // Construct the correct language-specific URL
    if ($export_language === $default_language) {
        // For default language (German), DO NOT add the language slug
        $translated_permalink = trailingslashit($site_url) . $translated_slug . '/';
    } else {
        // For secondary languages, include language slug
        $translated_permalink = trailingslashit($site_url) . trailingslashit($export_language) . $translated_slug . '/';
    }

    // Fetch SEO metadata
    $seo_title = get_post_meta($translated_post_id, '_yoast_wpseo_title', true) ?: 'N/A';
    $meta_description = get_post_meta($translated_post_id, '_yoast_wpseo_metadesc', true) ?: 'N/A';

    fputcsv($file, [
        $translated_post_id,
        get_the_title($translated_post_id),
        $row->post_type,
        $translated_permalink,
        $seo_title,
        $meta_description,
    ]);

    $exported_posts[] = $translated_post_id; // Mark post as exported
}


        fclose($file);

        // Return file URL
        $file_url = plugins_url('exports/' . $file_name, __FILE__);
        wp_send_json_success(['file_url' => $file_url]);
    }
}

new MetaExport();
