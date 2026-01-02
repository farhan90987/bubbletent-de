<?php
namespace MWEW\Inc\Admin;

class Map_Image_Field {

    public function __construct() {
        add_action('region_add_form_fields', [$this, 'add_map_image_field']);
        add_action('region_edit_form_fields', [$this, 'edit_map_image_field']);
        add_action('created_region', [$this, 'save_map_image_field']);
        add_action('edited_region', [$this, 'save_map_image_field']);
    }

    public function add_map_image_field() {
        ?>
        <div class="form-field">
            <label for="map_image">Map Image</label>
            <input type="hidden" name="map_image" id="map_image" value="">
            <button class="upload_map_image_button button">Upload Image</button>
            <div id="map_image_preview"></div>
        </div>
        <?php
    }

    public function edit_map_image_field($term) {
        $map_image_id = get_term_meta($term->term_id, 'map_image', true);
        $map_image_url = $map_image_id ? wp_get_attachment_url($map_image_id) : '';
        ?>
        <tr class="form-field">
            <th scope="row"><label for="map_image">Map Image</label></th>
            <td>
                <input type="hidden" name="map_image" id="map_image" value="<?php echo esc_attr($map_image_id); ?>">
                <button class="upload_map_image_button button">Upload Image</button>
                <div id="map_image_preview">
                    <?php if ($map_image_url): ?>
                        <img src="<?php echo esc_url($map_image_url); ?>" style="max-width: 200px;">
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    public function save_map_image_field($term_id) {
        if (isset($_POST['map_image'])) {
            update_term_meta($term_id, 'map_image', intval($_POST['map_image']));
        }
    }
}
