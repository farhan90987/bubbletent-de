<?php


$exclude_regions = array(284, 274);

$region_id = $args['id'];
$region_name = $args['name'];
$region_url	= $args['url'];
$region_img = $args['image'];

if (!in_array($region_id, $exclude_regions)) { ?>

	<div class="region-item" style="background-image:url(<?php echo $region_img; ?>);">
		<a href="<?php echo esc_url($region_url); ?>">
			<div><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
			<h4><?php esc_html_e($region_name, 'listeo_core'); ?></h4>
		</a>
	</div>

<?php }