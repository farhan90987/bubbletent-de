<?php
$template_loader = new Listeo_Core_Template_Loader;
?>
<!-- Content
================================================== -->
<div class="fs-container">

	<div class="fs-inner-container content">
		<div class="fs-content">

			<section class="search">
				<a href="#" id="show-map-button" class="show-map-button" data-enabled="<?php esc_attr_e('Show Map ', 'listeo'); ?>" data-disabled="<?php esc_attr_e('Hide Map ', 'listeo'); ?>"><?php esc_html_e('Show Map ', 'listeo') ?></a>
				<div class="row">
					<div class="col-md-12">

						<?php echo do_shortcode('[mwew_search_form]'); ?>

						<?php //echo do_shortcode('[listeo_search_form source="half" more_custom_class="margin-bottom-30"]'); ?>

					</div>
				</div>

			</section>
			<!-- Search / End -->

			<?php $content_layout = get_option('pp_listings_layout', 'list'); ?>
			<section class="listings-container margin-top-45">
				<!-- Sorting / Layout Switcher -->
				<div class="row fs-switcher">
					<?php
					do_action('listeo_archive_split_before_title');
					if (get_option('listeo_show_archive_title') == 'enable') { ?>
						<div class="col-md-12">
							<?php 
							$title = get_option('listeo_listings_archive_title');
							if (!empty($title) && is_post_type_archive('listing')) { ?>
								<h1 class="page-title"><?php echo esc_html($title); ?></h1>
							<?php } else {
								the_archive_title('<h1 class="page-title">', '</h1>');
							} ?>
						</div>
					<?php }
					do_action('listeo_archive_split_after_title');?>

					<?php $top_buttons = get_option('listeo_listings_top_buttons');

					if ($top_buttons == 'enable') {
						$top_buttons_conf = get_option('listeo_listings_top_buttons_conf');
						if (is_array($top_buttons_conf) && !empty($top_buttons_conf)) {

							if (($key = array_search('radius', $top_buttons_conf)) !== false) {
								unset($top_buttons_conf[$key]);
							}
							if (($key = array_search('filters', $top_buttons_conf)) !== false) {
								unset($top_buttons_conf[$key]);
							}
							$list_top_buttons = implode("|", $top_buttons_conf);
						} else {
							$list_top_buttons = '';
						}
					?>

						<?php do_action('listeo_before_archive', $content_layout, $list_top_buttons); ?>

					<?php
					} ?>

				</div>

				<!-- Listings -->
				<div class="row fs-listings">

					<?php

					switch ($content_layout) {
						case 'list':
						case 'grid':
							$container_class = $content_layout . '-layout';
							break;

						case 'compact':
							$container_class = $content_layout;
							break;

						default:
							$container_class = 'list-layout';
							break;
					}

					$data = '';

					$data .= ' data-region="' . get_query_var('region') . '" ';
					$data .= ' data-category="' . get_query_var('listing_category') . '" ';
					$data .= ' data-feature="' . get_query_var('listing_feature') . '" ';
					$data .= ' data-service-category="' . get_query_var('service_category') . '" ';
					$data .= ' data-rental-category="' . get_query_var('rental_category') . '" ';
					$data .= ' data-event-category="' . get_query_var('event_category') . '" ';
					$orderby_value = isset($_GET['listeo_core_order']) ? (string) $_GET['listeo_core_order']  : get_option('listeo_sort_by', 'date');
					?>
					<!-- Listings -->
					<div data-grid_columns="2" <?php echo $data; ?> data-orderby="<?php echo $orderby_value;  ?>" data-style="<?php echo esc_attr($content_layout) ?>" class="listings-container <?php echo esc_attr($container_class) ?>" id="listeo-listings-container">
						<div class="loader-ajax-container">
							<div class="loader-ajax"></div>
						</div>
						<div class="clearfix"></div>
					</div>
					<?php $ajax_browsing = get_option('listeo_ajax_browsing'); ?>
					<div class="pagination-container margin-top-45 margin-bottom-60 row"></div>
					<div class="copyrights margin-top-0 testing">
						<?php $copyrights = get_option('pp_copyrights', '&copy; Theme by Purethemes.net. All Rights Reserved.');
							// $copyrights = str_replace('%year%', date('Y'), $copyrights);
							$copyrights = str_replace(['2023', '2024', '%year%'], date('Y'), $copyrights);
							if (function_exists('icl_register_string')) {
								icl_register_string('Copyrights in footer', 'copyfooter', $copyrights);
								echo icl_t('Copyrights in footer', 'copyfooter', $copyrights);
							} else {
								echo wp_kses($copyrights, array('a' => array('href' => array(), 'title' => array()), 'br' => array(), 'em' => array(), 'strong' => array(),));
							} 
						?>

					</div>
				</div>
			</section>

		</div>
	</div>
	<div class="fs-inner-container map-fixed">

		<!-- Map -->
		<div id="map-container" class="">
			<div id="map" class="split-map" data-map-zoom="<?php echo get_option('listeo_map_zoom_global', 9); ?>" data-map-scroll="true">
				<!-- map goes here -->
			</div>

		</div>

	</div>
</div>

<div class="clearfix"></div>

<?php get_footer('empty'); ?>