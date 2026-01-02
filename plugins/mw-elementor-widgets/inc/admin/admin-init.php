<?php
namespace MWEW\Inc\Admin;

use MWEW\Inc\Admin\Pages\Map_Builder;
use MWEW\Inc\Admin\Pages\New_Map_Builder;
class Admin_Init{
    public function __construct(){
        new Map_Builder();
        new New_Map_Builder();
        new Map_Image_Field();
        new Map_Builder_Actions();

        new GTM_GA4_Settings();
    }
}


