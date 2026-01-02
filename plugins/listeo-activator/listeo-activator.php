<?php
/*
Plugin Name: Listeo Activator
Plugin URI: http://listeo.pro
Description: This plugin activates Listeo in case of problems with connection. Do not redistribute 
Version: 1.0
Author: purethemes
Author URI: http://purethemes.net
License: GPL2
License URI: http://purethemes.net

*/



add_filter('listeo_license_check', '__return_true');

?>