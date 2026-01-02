<?php

namespace MWEW\Inc\Shortcodes;

use MWEW\Inc\Services\Listing_Repo;

class MW_Search_Form_Shortcode
{
    public function __construct()
    {

        add_shortcode('mwew_search_form', array($this, 'output_search_form'));
    }

    public function output_search_form($atts = array())
    {
        $checkin  = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
        $checkout = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';

        $country_list = Listing_Repo::get_countries_by_region();

        $selected_country_id = isset($_GET['country_id']) ? sanitize_text_field($_GET['country_id']) : '';
?>

        <form id="listing-search-form" action="#">
            <div class="row mwew-date-picker-wrap">
                <div class="form-group col-md-4">
                    <label for="mwew-checkin-date"><?php echo __("Check in date", "mwew"); ?>:</label>
                    <input type="text" name="check_in" class="form-control checkin-date" value="<?php echo $checkin; ?>" placeholder="<?php echo __("Arrival", 'mwew'); ?>" id="mwew-checkin-date">
                </div>
                <div class="form-group col-md-4">
                    <label for="mwew-checkout-date"><?php echo __("Check out date", "mwew"); ?>:</label>
                    <input type="text" name="check_out" class="form-control checkout-date" value="<?php echo $checkout; ?>" placeholder="<?php echo __("Departure", 'mwew'); ?>" id="mwew-checkout-date">
                </div>
                <div class="form-group col-md-4">
                    <label for="mwew-country-id"><?php echo __("Country", "mwew"); ?>:</label>
                    <select name="country_id" class="form-control" id="mwew-country-id">
                        <option value="" disabled <?php echo empty($selected_country_id) ? 'selected' : ''; ?>>
                            <?php echo __('Select a country', 'mwew'); ?>
                        </option>
                        <?php
                        foreach ($country_list as $id => $country) {
                            $selected = ($id == $selected_country_id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($id) . '" ' . $selected . '>' . esc_html($country) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="display:none;">
                <span id="search-radius-value" style="font-weight: bolder;"></span>
                <label for="search-radius" class="form-label"><?php echo __("Radius search", "mwew"); ?></label>
                <input type="range" class="form-range" name="search_radius" id="search-radius" min="20" max="500">
            </div>

            <button type="submit" class="btn listing-search-btn" data-loading-text="<?php echo __("Processing...", "mwew"); ?>"><?php echo __("View available bubble tents", "mwew"); ?></button>
        </form>

        <script>
            
        </script>

<?php
        $output = ob_get_clean();
        echo $output;
    }
}
