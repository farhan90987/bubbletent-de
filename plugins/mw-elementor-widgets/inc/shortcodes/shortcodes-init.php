<?php 
namespace MWEW\Inc\Shortcodes;

class Shortcodes_Init{
    public function __construct(){
        

        add_filter('template_include', [$this, 'listing_archive'], 99);

        new MW_Search_Form_Shortcode();
        new MW_Search_Action();
    }


    public function listing_archive($template) {
        if (is_post_type_archive('listing')) {
            $custom_template = MWEW_DIR_PATH . 'templates/archive-listing.php';

            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }
}