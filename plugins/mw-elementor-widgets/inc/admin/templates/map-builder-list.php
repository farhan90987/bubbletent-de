<?php

namespace MWEW\Inc\Admin\Templates;
use MWEW\Inc\Database\Listing_Maps_DB;
use MWEW\Inc\Logger\Logger;

class Map_Builder_List
{

    public static function render(){
        if(isset($_GET['page']) 
        && !empty($_GET['page']) 
        && $_GET['page'] == 'mw-map-builder' 
        && isset($_GET['action']) 
        && !empty($_GET['action'])
        && $_GET['page'] == 'mw-map-builder' 
        && isset($_GET['map_id']) 
        && !empty($_GET['map_id'])
        ){
            Listing_Maps_DB::delete_by_id($_GET['map_id']);
        }

        $maps = Listing_Maps_DB::get_all();
        

        ob_start(); ?>

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">SL</th>
                        <th class="px-6 py-3">Region</th>
                        <th class="px-6 py-3">Markers</th>
                        <th class="px-6 py-3">Created At</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; ?>
                    <?php foreach ($maps as $map): 
                        $term = get_term($map['region_id'], 'region');
                        $region_name = $term ? $term->name : '—';
                        $markers = maybe_unserialize($map['map_data']);
                    ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <?php echo $count; ?>
                        </th>
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <?php echo esc_html($region_name); ?>
                        </th>
                        <td class="px-6 py-4">
                            <?php echo is_array($markers) ? count($markers) : 0; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php echo esc_html(date('Y-m-d H:i', strtotime($map['created_at']))); ?>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="?page=mw-new-map-builder&action=edit&map_id=<?php echo $map['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                            <a href="#" class="mw-delete-map text-red-600 hover:underline" data-map-id="<?php echo esc_attr($map['id']); ?>">Delete</a>
                        </td>
                    </tr>
                    <?php $count++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>


        <?php
        ob_end_flush();
    }
}