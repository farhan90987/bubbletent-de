<?php

namespace MWEW\Inc\Admin\Templates;

use MWEW\Inc\Database\Listing_Maps_DB;
use MWEW\Inc\Services\Map_Repo;
use MWEW\Inc\Logger\Logger;

class Map_Builder_Template
{

    public static function render($regions, $map_id = 0)
    {

        $map_id = isset($_GET['map_id']) ? intval($_GET['map_id']) : '';

        $map_data = Listing_Maps_DB::get_by_id($map_id);

        $data_titles = Map_Repo::get_listing_title_by_id($map_data);

        ob_start();

?>
<div class="flex flex-col gap-2">
    <div class="flex flex-row p-4 gap-2 justify-start">
        <!-- Input Map Section -->
        <div class="w-full bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold"><?php echo __("Click to Add Markers", 'mwew'); ?></h2>
            <div class="flex items-center space-x-4">
            <span id="marker-count" class="text-sm text-gray-600"><?php echo __("Markers: 0", "mwew"); ?></span>
            <button id="clear-markers" class="btn bg-red-500 text-white px-4 py-2 rounded-lg disabled:opacity-50"
                disabled>
                <?php echo __("Clear All Markers", "mwew"); ?>
            </button>
            </div>
        </div>
        <div class="mb-4 flex flex-row gap-2 items-center justify-between">
            <div class="flex flex-row gap-2 items-center justify-between">
                <div>
                    <label for="regionSelect" class="block text-sm font-medium text-gray-700"><?php echo __("Select Region", "mwew"); ?></label>
                    <select id="regionSelect"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option selected disabled value=""><?php echo __("Select Region", "mwew"); ?></option>
                        <?php foreach($regions as $region) : 
                            $selected = ($region['id'] == $map_data['region_id']) ? 'selected="selected"' : '';
                        ?>
                            <option value="<?php echo esc_attr($region['id']); ?>" 
                                    data-image="<?php echo esc_url($region['image']); ?>"
                                    <?php echo $selected; ?>>
                                <?php echo esc_html($region['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="marker-id" class="block text-sm font-medium text-gray-700"><?php echo __("Select Location", "mwew"); ?></label>
                    <select id="marker-id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value=""><?php echo __("Select a location", "mwew"); ?></option>
                    </select>
                </div>
            </div>
            <div>
                <a href="#" id="save-map-location" data-map-id="<?php echo $map_id; ?>" class="inline-block px-4 py-2 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition">Save Map</a>
            </div>
        </div>
        <figure id="input-container" class="mw-map-builder relative w-full rounded-[20px] bg-[#F2F2F2] aspect-[6.7/5.2] text-white">
            <img id="input-map" src="<?php echo MWEW_PATH_URL . "assets/images/select-a-region.jpg"; ?>" alt="Map of Switzerland"
                class="h-full w-full object-contain"/>
        </figure>
        </div>

        <!-- Output Map Section -->
        <div class="w-full bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-bold mb-4"><?php echo __("Preview", "mwew"); ?></h2>
        <figure id="output-container" class="mw-map-builder relative w-full rounded-[20px] bg-[#F2F2F2] aspect-[6.7/5.2] text-white">
            <img id="output-map" src="<?php echo MWEW_PATH_URL . "assets/images/select-a-region.jpg"; ?>" alt="Map Output"
                class="h-full w-full object-contain"/>
        </figure>
        </div>
    </div>

  <template id="tooltip-template">
    <div
      class="tooltip absolute bg-[#008000] text-white p-2 rounded text-xs pointer-events-none z-10 transform -translate-x-1/2 -translate-y-full mt-[-10px] max-w-[200px] whitespace-normal">
      <div class="wrapper flex flex-col gap-2">
        <strong class="name"></strong>
        <svg class="absolute h-2 text-[#008000] w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255"
          xml:space="preserve">
          <polygon class="fill-current" points="0,0 127.5,127.5 255,0" />
        </svg>

      </div>
    </div>
  </template>

  </div>

<?php if (!empty($map_id)) : ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        var regionSelect = document.getElementById("regionSelect");
        if (regionSelect) {
            var markers = <?php echo wp_json_encode($map_data['map_data']); ?>;
            localStorage.setItem("markers", JSON.stringify(markers));

            var dataTitles = <?php echo wp_json_encode($data_titles); ?>

            localStorage.setItem("dataTitles", JSON.stringify(dataTitles));

            regionSelect.dispatchEvent(new Event("change"));
            if (window.jQuery) {
                jQuery(regionSelect).trigger("change");
            }
        }
    }, 100);
});
</script>
<?php endif; ?>


<?php
    ob_end_flush();

    }
}
