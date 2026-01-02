<?php
/**
 * Calendar view
 *
 * @package smoobu-calendar
 */

do_action( 'smoobu_before_calendar_view', $property_id, $layout );
$name            = "smoobu-calendar-start-$property_id";
$currency_symbol = '';
?>
<?php if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) && is_checkout() ) ) : ?>
		<?php
		if ( isset( $_GET['empty-cart'] ) ) {
			WC()->cart->empty_cart();
		}

		// Get the price and currency symbol.
		if ( ! empty( $link ) ) {
			$query = wp_parse_url( $link, PHP_URL_QUERY );
			parse_str( str_replace( '&amp;', '&', $query ), $query_array );
			if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
				$currency_symbol = get_woocommerce_currency_symbol();
			}
		}
		?>

	<div
		id="smoobu-cost-calculator-container"
		data-layout="<?php echo esc_attr( $layout ); ?>"
		data-property-id="<?php echo esc_attr( $property_id ); ?>"
	>
		<div class="smoobu-price-display-container">
			<?php
			// Check if $query_array['prices'] is set and not empty.
			if ( isset( $query_array['prices'] ) && ! empty( $query_array['prices'] ) ) {
				// Sanitize the price value.
				$price = esc_html( $query_array['prices'] );

				// Format the price string.
				// translators:%1$s is the price of the product and %2$s is the currency symbol.
				$formatted_price = sprintf( __( 'From %1$s%2$s / Night', 'smoobu-calendar' ), $price, $currency_symbol );

				// Output the formatted price.
				echo $formatted_price; //phpcs:ignore
			} else {
				// Handle the case where $query_array['prices'] is not set or empty.
				echo esc_html__( 'Price not available', 'smoobu-calendar' );
			}
			?>
		</div>
         <p class="smobuutext"><img src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/1.svg" /></a><span> <?php esc_html_e('Wähle deinen Reisezeitraum aus', 'listeo_core'); ?></span></p>
		<form id="smoobu-check-availability" name="smoobu-check-availability" action="" method="POST">
		<input
			class="smoobu-calendar"
			type="text"
			id="smoobu-calendar-start-<?php echo esc_attr( $property_id ); ?>"
			name="smoobu-calendar-start-<?php echo esc_attr( $property_id ); ?>"
			placeholder="<?php esc_html_e( 'Check-In', 'smoobu-calendar' ); ?>"
		/>
		<input
			class="smoobu-calendar"
			type="text"
			id="smoobu-calendar-end-<?php echo esc_attr( $property_id ); ?>"
			name="smoobu-calendar-end-<?php echo esc_attr( $property_id ); ?>"
			placeholder="<?php esc_html_e( 'Check-Out', 'smoobu-calendar' ); ?>"
		/>
	</form>
    <p class="smobuutext secondparasobu"><img src="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/images/2.svg" /></a><span><?php esc_html_e('Kicke danach auf "Jetzt reservieren"', 'listeo_core'); ?></span></p> 
<?php else : ?>
	<?php
	$is_in_cart  = false;
	$cart        = WC()->cart->get_cart();
	$custom_date = array_column( $cart, 'custom_data' );
	$start_date  = $custom_date[0]['start-date'];
	$end_date    = $custom_date[0]['end-date'];


	foreach ( $cart as $cart_item ) {
		$product = wc_get_product( $cart_item['data']->get_id() );
		if ( $product->is_type( 'listing_booking' ) ) {
			$is_in_cart = true;
			break;
		}
	}

	?>
	<?php if ( $is_in_cart ) : ?>
		<div
			id="smoobu-check-availability"
			data-start-date="<?php echo esc_attr( $start_date ); ?>"
			data-end-date="<?php echo esc_attr( $end_date ); ?>"
			data-property-id="<?php echo esc_attr( $property_id ); ?>"
			data-layout="<?php echo esc_attr( $layout ); ?>"
			class="smoobu-check-availability"
		>
	<?php endif; ?>

<?php endif; ?>


<?php if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) && is_checkout() ) ) : ?>
	<?php
	global $post;
	$page_id = $post->ID;
	$query   = wp_parse_url( $link, PHP_URL_QUERY );
	if ( $query ) {
		$new_link  = $link . "&page-id=$page_id";
		$new_link .= "&property_id=$property_id";
	} else {
		$new_link  = $link . "?page-id=$page_id";
		$new_link .= "&property_id=$property_id";
	}
	?>
		<div class="smoobu-calendar-estimate" id="st-booking-cost"></div>
		<!-- <hr class="smoobu-calendar-line" /> -->
		<p class="smoobu-calendar-button-container">
			<a href="<?php echo esc_url( $new_link ); ?>" class="button st-cashier disabled">
				<?php esc_html_e( 'Reserve now', 'smoobu-calendar' ); ?>
			</a>
		</p>
	</div>
<?php else : ?>
	<?php if ( $is_in_cart ) : ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
<?php
do_action( 'smoobu_after_calendar_view', $property_id, $layout );
