<?php
if(isset($_GET[$data->name])) {
	$value = stripslashes(sanitize_text_field($_GET[$data->name]));
} else {
	if(isset($data->default) && !empty($data->default)){
		$value = $data->default;
	} else {
		$value = '';	
	}
} 

if (isset($_GET['check_in'])) {
	$check_in = $_GET['check_in'];
} else {
	$check_in = date('Y-m-d');
} if (isset($_GET['check_out'])) {
	$check_out = $_GET['check_out'];
} else {
	$check_out = date('Y-m-d');
} if (isset($_GET['listing_reigon'])) {
	$listing_reigon = $_GET['listing_reigon'];
}

$terms = get_terms([
	'taxonomy'   => 'listing_feature',
	'hide_empty' => false,
]);

?>
<div class="<?php if(isset($data->class)) { echo esc_attr($data->class); } ?> <?php if(isset($data->css_class)) { echo esc_attr($data->css_class); }?>">
	<!-- <div id="autocomplete-container">
		<input  autocomplete="off" name="<?php echo esc_attr($data->name);?>" id="<?php echo esc_attr($data->name);?>" type="text" placeholder="<?php echo esc_attr($data->placeholder);?>" value="<?php if(isset($value)){ echo esc_attr($value); }  ?>"/>
	</div>
	<a href="#"><i title="<?php esc_html_e('Find My Location','listeo_core') ?>" class="tooltip left fa fa-map-marker"></i></a>
	<span class="type-and-hit-enter"><?php esc_html_e('type and hit enter','listeo_core') ?></span> -->

	<input type="date" name="check_in" class="starting_date" value="<?= $check_in; ?>">
	<input type="date" name="check_out" class="ending_date" value="<?= $check_out; ?>">

	<?php if (!is_wp_error($terms) && !empty($terms)) : ?>
		<select name="listing_reigon" id="listing-reigon">
			<option value="">Select a Reigon</option>
			<?php foreach ($terms as $term) : ?>
				<option value="<?= esc_attr($term->slug); ?>" <?= ($term->slug == $listing_reigon) ? 'selected' : ''; ?>><?= esc_html($term->name); ?></option>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
</div>
