<?php
function get_invoice_pdf_button_by_order_id($order_id) {
	$url = esc_url( wp_nonce_url(
		admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_view_order_invoice_download&order_id=' . $order_id ),
		'wp-wc-invoice-pdf-download-view-order'
	) );
    ob_start();
    ?>
    <style>
        .gm-pdf-loader {
            display: inline-block;
            margin-left: 8px;
            width: 16px;
            height: 16px;
            border: 2px solid #ccc;
            border-top: 2px solid #007cba;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
        }
		.contact-detail{
			padding:15px 0;
		}
		.contact-detail input.wpcf7-form-control.wpcf7-submit.has-spinner {
				background-color: #e0d47c;
				border-radius: 25px;
				padding: 0px 35px;
				margin-top: 10px;
				color: #000;
				transition: all 0.3s;
				box-shadow: none;
				border: none;
				font-weight:400;
				font-size:15px;
			}
		.contact-detail input.wpcf7-form-control.wpcf7-submit.has-spinner:hover{
			background:#54775e;
		}

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .gm-pdf-loader.hidden {
            display: none;
        }
    </style>
	<a class="button-primary wp-wc-invoice-pdf gm-pdf-download-button" href="<?php echo esc_url($url); ?>" download>
		<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style="margin:-5px 3px 0 0;">
			<path d="M16.6248 4.61667L13.7165 1.70833C12.6165 0.608333 11.1498 0 9.5915 0H5.83317C3.53317 0 1.6665 1.86667 1.6665 4.16667V15.8333C1.6665 18.1333 3.53317 20 5.83317 20H14.1665C16.4665 20 18.3332 18.1333 18.3332 15.8333V8.74167C18.3332 7.18333 17.7248 5.71667 16.6248 4.61667ZM15.4498 5.79167C15.7165 6.05833 15.9415 6.35 16.1248 6.66667H12.5082C12.0498 6.66667 11.6748 6.29167 11.6748 5.83333V2.21667C11.9915 2.4 12.2832 2.625 12.5498 2.89167L15.4582 5.8L15.4498 5.79167ZM16.6665 15.8333C16.6665 17.2083 15.5415 18.3333 14.1665 18.3333H5.83317C4.45817 18.3333 3.33317 17.2083 3.33317 15.8333V4.16667C3.33317 2.79167 4.45817 1.66667 5.83317 1.66667H9.5915C9.72484 1.66667 9.8665 1.66667 9.99984 1.68333V5.83333C9.99984 7.20833 11.1248 8.33333 12.4998 8.33333H16.6498C16.6665 8.46667 16.6665 8.6 16.6665 8.74167V15.8333ZM5.90817 10.8333H4.99984C4.5415 10.8333 4.1665 11.2083 4.1665 11.6667V15.3667C4.1665 15.6583 4.39984 15.8833 4.68317 15.8833C4.9665 15.8833 5.19984 15.65 5.19984 15.3667V14.35H5.89984C6.88317 14.35 7.68317 13.5583 7.68317 12.5917C7.68317 11.625 6.88317 10.8333 5.89984 10.8333H5.90817ZM5.90817 13.3083H5.2165V11.875H5.9165C6.3165 11.875 6.65817 12.2 6.65817 12.5917C6.65817 12.9833 6.3165 13.3083 5.9165 13.3083H5.90817ZM15.8498 11.3583C15.8498 11.65 15.6165 11.875 15.3332 11.875H13.9248V12.825H14.9582C15.2498 12.825 15.4748 13.0583 15.4748 13.3417C15.4748 13.625 15.2415 13.8583 14.9582 13.8583H13.9248V15.3583C13.9248 15.65 13.6915 15.875 13.4082 15.875C13.1248 15.875 12.8915 15.6417 12.8915 15.3583V11.35C12.8915 11.0583 13.1248 10.8333 13.4082 10.8333H15.3332C15.6248 10.8333 15.8498 11.0667 15.8498 11.35V11.3583ZM10.0748 10.8417H9.1665C8.70817 10.8417 8.33317 11.2167 8.33317 11.675V15.375C8.33317 15.6667 8.5665 15.8417 8.84984 15.8417C9.13317 15.8417 10.0665 15.8417 10.0665 15.8417C11.0498 15.8417 11.8498 15.05 11.8498 14.0833V12.6C11.8498 11.6333 11.0498 10.8417 10.0665 10.8417H10.0748ZM10.8165 14.0833C10.8165 14.475 10.4748 14.8 10.0748 14.8H9.38317V11.8833H10.0832C10.4832 11.8833 10.8248 12.2083 10.8248 12.6V14.0833H10.8165Z" fill="#ffffff"/>
		</svg> <?php esc_html_e('Download invoice pdf', 'woocommerce-german-market'); ?>
	</a>
    <span class="gm-pdf-loader hidden" id="gm-loader-<?php echo esc_attr($order_id); ?>"></span>

    <script>
        (function($) {
            $(document).ready(function() {
                $('.gm-pdf-download-button').on('click', function(e) {
                    var loader = $(this).next('.gm-pdf-loader');
                    loader.removeClass('hidden');

                    // Hide loader after few seconds just in case
                    setTimeout(function() {
                        loader.addClass('hidden');
                    }, 6000); // fallback timeout
                });
            });
        })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}

function get_listing_by_order_id($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);

    if (!$order) return null;

    $matched_listings = [];

    // Loop through the order items
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();

        // Search for listings with meta key matching this product ID
        $listings = get_posts([
            'post_type'  => 'listing',
            'post_status'=> 'publish',
            'meta_key'   => 'product_id', // replace with your actual meta key
            'meta_value' => $product_id,
            'numberposts'=> -1
        ]);

        if (!empty($listings)) {
            $matched_listings = array_merge($matched_listings, $listings);
        }
    }

    return $matched_listings;
}

function get_order_details()
{
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
		$listings = get_listing_by_order_id($order_id);
        $order = wc_get_order($order_id);
        $current_user_id = get_current_user_id();
        // $entered_password = md5(sanitize_text_field($_POST['user_password']));
        $entered_password = sanitize_text_field($_POST['user_password']);
        $saved_password = get_post_meta($order_id, '_random_password', true);
        $vendor_id = get_post_meta($order_id, '_dokan_vendor_id', true);
        $vendor = get_user_by('id', $vendor_id);
        $vendor_name = $vendor->display_name;
        $vendor_email = $vendor->user_email;
        $vendor_number = get_user_meta($vendor_id, 'phone', true);
        // echo "<pre>";
        // print_r($vendor_meta);
        // echo "</pre>";
        // if (!is_user_logged_in()) {
        //     echo '<div class="order-not_found"><p>Please log in to view order details.</p></div>';
        //     wp_die();
        // }


        if ($order) {
            // if ($order->get_user_id() != $current_user_id || $entered_password != $saved_password) {
            if ($entered_password != $saved_password) {
                echo '<div class="order-not_found"><p>You are not authorized to view this order.</p></div>';
                wp_die();
            }
            // if ($order->get_user_id() != $current_user_id) {
            //     echo '<div class="order-not_found"><p>You are not authorized to view this order.</p></div>';
            //     wp_die();
            // }
            foreach ($order->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
                $product_id = $item->get_product_id();

                // Query posts with the custom field
                $args = array(
                    'post_type' => 'listing',
                    'meta_query' => array(
                        array(
                            'key' => 'product_id', // Your CMB2 custom field key
                            'value' => $product_id, // Compare with the product ID
                            'compare' => '='
                        ),
                    ),
                );

                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $product_link = get_permalink();
                        $product_name = get_the_title();
                        $arrival_and_departure_heading_text = get_field('arrival_and_departure_heading_text');
                        $arrival_and_departure = get_field('arrival_and_departure');
                        $booking_cancellation_main_heading = get_field('booking_&_cancellation_main_heading');
                        $_booking_and_cancellation = get_field('_booking_&_cancellation');
                        $house_rules_and_information_main_heading = get_field('house_rules_&_information_main_heading');
                        $house_rules_and_information_list = get_field('house_rules_&_information_list');
                        $house_rules_and_information_popup_icons = get_field('house_rules_&_information_popup_icons');
                        $listing_owner_no = get_field('_phone');
                        $tent_address = get_field('_friendly_address');
						$geo_address = get_field('_address');
                        $latitude = get_field('_geolocation_lat');
                        $longitude = get_field('_geolocation_long');
                        $address = get_field('_address');
                        $wichtige_informationen = get_field('wichtige_informationen');
                        $disable_address = get_option('listeo_disable_address');
						$featured_img_url = get_field('order_page_image');
                        if (!empty($latitude) && $disable_address) {
                            $dither = '0.001';
                            $latitude = $latitude + (rand(5, 15) - 0.5) * $dither;
                        }
                    }
                }
                wp_reset_postdata();
            }

            $order_total = $order->get_total();
            $currency_symbol = get_woocommerce_currency_symbol();
            $formatted_price = number_format($order_total, 2, ',', '');
            $order_price = $formatted_price . ' ' . $currency_symbol;
            $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			$first_name = $order->get_billing_first_name();
			$last_name = $order->get_billing_last_name();
            $customer_phone = $order->get_billing_phone();
            $billing_address = $order->get_billing_address_1() . ' ' . $order->get_billing_city();
            $billing_country = $order->get_billing_country();
            $billing_email = $order->get_billing_email();
            $start_date = get_post_meta($order_id, 'smoobu_calendar_start', true);
            $end_date = get_post_meta($order_id, 'smoobu_calendar_end', true);
            $checkin_time = get_post_meta($order_id, 'smoobu_checkin_time', true);
            $addon_price = get_post_meta($order_id, '_custom_addon_price_0', true);
            if ($start_date && $end_date) {
                $start_date_obj = new DateTime($start_date);
                $end_date_obj = new DateTime($end_date);
                // Format the dates in 'l, F j, Y' format
                $formatted_start_date = $start_date_obj->format('l, F j, Y');
                $formatted_end_date = $end_date_obj->format('l, F j, Y');
				$check_in_date = $formatted_start_date;
				$check_out_date = $formatted_end_date;

            }
            $order_create_date = $order->get_date_created()->date('l, F j, Y'); // Order date
            $adults = get_post_meta($order_id, '_number_of_adults', true);
            $kids = get_post_meta($order_id, '_number_of_kids', true);
            $guest_count = $adults + $kids;
            $coupon_used = $order->get_coupon_codes() ? implode(', ', $order->get_coupon_codes()) : 'No coupon used';
            $addons = '';
            foreach ($order->get_items('fee') as $addon_item) {
                $addons_quantity .= $addon_item->get_quantity();
                $addons .= $addon_item->get_name() . ' (x' . $addons_quantity . ')';
                // $addons_price .= $addon_item->get_price();
            }
            $guest_message = (get_post_meta($order_id, 'message_to_landlord', true)) ? get_post_meta($order_id, 'message_to_landlord', true) : '*No message to the host*';
            ob_start();
			
            ?>
			<!-- html start -->
            <input type="text" name="order_create_date" id="order_create_date" value="<?php echo esc_html($order_create_date); ?>" style="display:none">
            <input type="text" name="vendor_name" id="vendor_name" value="<?php echo esc_html($vendor_name); ?>"
                style="display:none" disabled>
            <input type="email" name="vendor_email" id="vendor_email" value="<?php echo esc_html($vendor_email); ?>"
                style="display:none" disabled>
            <input type="email" name="listing_owner_no" id="listing_owner_no" value="<?php echo esc_html($listing_owner_no); ?>"
                style="display:none" disabled>
            <input type="text" name="new_order_no" id="new_order_no" placeholder="*****" value="<?php echo esc_html($order_id); ?>"
                style="display:none" disabled>
            <input type="text" name="order_password" id="order_password" placeholder="*****"
                value="<?php echo esc_html($saved_password); ?>" style="display:none" disabled>
			<input type="text" name="user-name" id="user_name" value="<?php echo esc_html($customer_name); ?>" style="display:none">
			<input type="text" name="first-name" id="first_name" value="<?php echo esc_html($first_name); ?>" style="display:none">
 			<input type="text" name="last-name" id="last_name" value="<?php echo esc_html($last_name); ?>" style="display:none">
            <input type="text" name="customer_phone" id="customer_phone" value="<?php echo esc_html($customer_phone); ?>"
                style="display:none">
            <input type="text" name="billing_address" id="billing_address" value="<?php echo esc_html($billing_address); ?>"
                style="display:none">
            <input type="text" name="billing_country" id="billing_country" value="<?php echo esc_html($billing_country); ?>"
                style="display:none">
            <input type="email" name="billing_email" id="billing_email" value="<?php echo esc_html($billing_email); ?>"
                style="display:none">
            <input type="text" name="order_name" id="order_name" value="<?php echo esc_html($product_name); ?>"
                style="display:none">
            <input type="text" name="start-date" id="start_date" value="<?php echo esc_attr($start_date); ?>" style="display:none">
            <input type="text" name="end-date" id="end_date" value="<?php echo esc_attr($end_date); ?>" style="display:none">
            <input type="text" name="checkin_time" id="checkin_time" value="<?php echo esc_attr($checkin_time); ?>"
                style="display:none">
            <input type="text" name="order-create-date" id="order_start_date" value="<?php echo esc_attr($formatted_start_date); ?>"
                style="display:none">
            <input type="text" name="order_end_date" id="order_end_date" value="<?php echo esc_attr($formatted_end_date); ?>"
                style="display:none">
            <input type="text" name="guest_count" id="guest_count" value="<?php echo esc_html($guest_count); ?>"
                style="display:none">
            <input type="text" name="addon_price" id="addon_price" value="<?php echo esc_html($addon_price); ?>"
                style="display:none">
            <input type="text" name="addons_quantity" id="addons_quantity" value="<?php echo esc_html($addons_quantity); ?>"
                style="display:none">
            <input type="text" value="<?php echo esc_html($order_price); ?>" id="order_price" style="display:none;">
			<input type="text" value="<?php echo $featured_img_url; ?>" id="listing_thumnail" style="display:none;">
			<input type="text" value="<?php echo $order_id; ?>" id="order_number" style="display:none;">
            <div class="order--details-wrap-inner" id="customer-order-info">
                <div class="container--row page-1-container">
                    <div class="col col-first">
                        <div class="customer--order-info">
                            <div class="prd_lft_sngl_cl">
                                <div class="title--wrap">
                                    <h4 class="title--inner m-mb-0 bubble-tent-elzach">
                                        <?php echo esc_html($product_name); ?>
                                    </h4>
                                    <div class="tent--details-btn">
                                        <a href="<?php echo esc_url($product_link) ?>" target="_blank">
                                            <?php esc_html_e('View Click here to go to the location page', 'smoobu-calendar'); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="customer--info-box under_line">
                                    <div class="detail--wrap">
                                        <h6 class="detail--title">
                                            <?php esc_html_e('Order number', 'smoobu-calendar'); ?> :
                                            <span class="detail-title-number"><?php echo esc_html($order_id); ?></span>
                                        </h6>
                                        <p class="detail--content">
                                            <?php esc_html_e('Please always quote this order number.', 'smoobu-calendar'); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2025/07/marker-1.png" alt="Tent Address Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Address', 'smoobu-calendar'); ?> :</h6>
                                        <p class="detail--content"><?php echo esc_html($geo_address); ?></p>
                                        <div class="tent--details-btn">
                                            <a target="_blank" href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($latitude . ',' . $longitude); ?>">
                                                <?php esc_html_e('View directions now', 'smoobu-calendar'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard2-fill" viewBox="0 0 16 16">
                                            <path d="M9.5 0a.5.5 0 0 1 .5.5..."/>
                                        </svg>
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Check-in instructions', 'smoobu-calendar'); ?>:</h6>
                                        <p class="detail--content"><?php echo get_field('check_in_instructions', $listing->ID) ?></p>
                                    </div>
                                </div>

                                <div class="customer--info-box unter_box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2025/07/memo-pad-1.png" alt="Property Policies Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title m-mb-0"><?php esc_html_e('Accommodation rules', 'smoobu-calendar'); ?>:</h6>
                                        <div class="pp-btn">
                                            <button><?php esc_html_e('View all rules', 'smoobu-calendar'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="property-policy">
                                <div class="property-policy-inner">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 class="heading33">
                                                <?php echo esc_html_e('What else you should know', 'smoobu-calendar'); ?>
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="row middlesection abd-grid">
                                        <div class="col-md-4 col-sm-4 col-xs-12 mobileviewcenter abd-grid-card">
                                            <h4 class="desktopviewww"><?php echo esc_html($arrival_and_departure_heading_text); ?></h4>
                                            <?php if ($arrival_and_departure) { ?>
                                                <ul class="middlelist desktopviewww">
                                                    <?php foreach ($arrival_and_departure as $arrival) { ?>
                                                        <li><?php echo esc_html($arrival['arrival_and_departure_list_items']); ?></li>
                                                    <?php } ?>
                                                </ul>
                                                <button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal6">
                                                    <?php esc_html_e('learn more', 'smoobu-calendar'); ?>
                                                </button>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-4 col-sm-4 col-xs-12 centercolumcs mobileviewcenter abd-grid-card">
                                            <h4 class="desktopviewww"><?php echo esc_html($booking_cancellation_main_heading); ?></h4>
                                            <?php if ($_booking_and_cancellation) { ?>
                                                <ul class="middlelist desktopviewww">
                                                    <?php foreach ($_booking_and_cancellation as $booking) { ?>
                                                        <li><?php echo esc_html($booking['_booking_&_cancellation_list_item']); ?></li>
                                                    <?php } ?>
                                                </ul>
                                                <button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal5">
                                                    <?php esc_html_e('learn more', 'smoobu-calendar'); ?>
                                                </button>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-4 col-sm-4 col-xs-12 mobileviewcenter abd-grid-card">
                                            <h4 class="desktopviewww"><?php echo esc_html($house_rules_and_information_main_heading); ?></h4>
                                            <?php if ($house_rules_and_information_list) { ?>
                                                <ul class="middlelist desktopviewww">
                                                    <?php foreach ($house_rules_and_information_list as $rules) { ?>
                                                        <li><?php echo esc_html($rules['house_rules_&_information_list_item']); ?></li>
                                                    <?php } ?>
                                                </ul>
                                                <button type="button" class="readmorebtn desktopviewww" data-toggle="modal" data-target="#myModal4">
                                                    <?php esc_html_e('learn more', 'smoobu-calendar'); ?>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="on_pgr_wrpr">    
                                <h3 class="on_pgr_title">One Pager</h3>
                                <p><?php esc_html_e('In this PDF, you will find all the important information about your booking.', 'smoobu-calendar'); ?></p>
                                <div class="btn--wrap">
                                    <?php if ($listings) {
                                        foreach ($listings as $listing) {
                                            $pdf_datei_bestellen = get_field('pdf-datei_bestellen', $listing->ID); ?>
                                            <a href="<?php echo esc_url($pdf_datei_bestellen); ?>" download>
                                                <?php esc_html_e('Download now', 'smoobu-calendar'); ?>
                                            </a>
                                    <?php } } ?>
                                </div>
                            </div>
                        </div>

                        <div class="contact--us-box" id="kontaktformular">
                            <div class="title--wrap">
                                <h4 class="title--inner frages_title"><?php esc_html_e('Any questions? We`re here to help!', 'smoobu-calendar'); ?></h4>
                                <p class="sub--title frages_txt"><?php esc_html_e('Use this form to contact our support team directly.', 'smoobu-calendar'); ?></p>
                                <h4 class="betreff_title"><?php esc_html_e('Subject', 'smoobu-calendar'); ?></h4>
                                <div class="contact-detail" id="cnt-form-7">
                                    <?php
                                    // $form_id = '230899';
                                    // $lang    = apply_filters( 'wpml_current_language', null );
                                    // if ( function_exists('wpml_object_id') ) {
                                    //     $form_id = apply_filters( 'wpml_object_id', $form_id, 'wpcf7_contact_form', true, $lang );
                                    // }
                                    // echo do_shortcode( '[contact-form-7 id="' . intval($form_id) . '"]' );
                                    echo do_shortcode('[contact-form-7 id="695dcc9" title="Contact Detail Form"]'); 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="info_popup">
                            <a href="#" class="close_popup">X</a>
                            <div style="margin-top:30px;" class="title--wrap">
                                <?php echo $wichtige_informationen ?>
                            </div>
                        </div>
                    </div>

                    <div class="col col-second ">
                        <div class="booking--wrap">
							<div class = "single_side_bar">
                            <div class="title--wrap">
                                <h4 class="title--inner"><?php esc_html_e('Your booking', 'smoobu-calendar'); ?></h4>
                                <p class="sub--title">
                                    <?php echo esc_html($product_name); ?>
                                </p>
                            </div>
                            <?php
                            if ($check_in_date && $check_out_date) {
                                ?>
                                <div class="customer--info-box">
                                    <!--                                     <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Calendar-Icon.webp" alt="Calendar Icon">
                                    </div> -->
                                    <div class="detail--wrap check_dates">
										<div class = "check_in">
											<h4>Check - in</h4>
											<h6 class="detail--title">
												
                                        		<?php 
													$timestamp = strtotime($check_in_date);
													$date_format = 'l, j. F Y'; // Example: Dienstag, 15. Juli 2025
													echo esc_html( date_i18n($date_format, $timestamp) );
												?>
                                        	</h6>
										</div>
										<div class = "check_out">
											 <h4>Check - out</h4>
											 <h6 class="detail--title">
												<?php 
													$timestamp = strtotime($check_out_date);
													$date_format = 'l, j. F Y'; 
													echo esc_html( date_i18n($date_format, $timestamp) );
												 ?>
											</h6>
										</div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="icon--text-box">
                                <div class="icon--wrap">
                                        <!--                                     <img src="/wp-content/uploads/2024/12/Name-on-the-reservation-Icon.webp"
                                        alt="Name-on-the-reservation-Icon"> -->
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    </svg>
                                </div>
                                <div class="box--content">
                                    <p class="box--title">
                                        <?php echo esc_html($customer_name); ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                            if ($guest_count) {
                                ?>
                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Number-of-guest-Icon.webp" alt="Number-of-guest-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">
                                            <?php echo esc_html($guest_count); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            } ?>

                            <div class="icon--text-box">
                                <div class="icon--wrap">
                                    <img src="/wp-content/uploads/2024/12/Nachricht-an-den-Gastgeber-Icon.webp"
                                        alt="Nachricht-an-den-Gastgeber-Icon">
                                </div>
                                <div class="box--content">
                                    <p class="box--title">
                                        <?php 
                                        if((get_post_meta($order_id, 'message_to_landlord', true))){
                                            echo get_post_meta($order_id, 'message_to_landlord', true);
                                        }else{
                                            esc_html_e('*No message to the host*','smoobu-calendar');
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                            if ($addons) {
                                ?>
                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Add-on-list-Icon.webp" alt="Add-on-list-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">
                                            <?php echo esc_html($addons); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            } ?>
                            <div class="coupen_wrapr">

							<?php if ($coupon_used !== 'No coupon used'): ?>
                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2025/07/ticket-2.png" alt="Number-of-guest-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">
                                            <?php echo esc_html($coupon_used); ?>
                                        </p>
                                    </div>
                                </div>
							<?php else: ?>
								<p class = "coupen_textt">
									<?php esc_html_e('Forgot to enter your voucher code? No problem, just send us a message using the contact form. Subject', 'smoobu-calendar'); ?> <strong><a href="#kontaktformular"><?php esc_html_e('Voucher redemption', 'smoobu-calendar'); ?></a></strong>
								</p>
							<?php endif; ?>
                            </div>
								</div>
                                <!--                             <div class="icon--text-box">
                                <div class="icon--wrap">
                                    <img src="/wp-content/uploads/2024/12/Nachricht-an-den-Gastgeber-Icon.webp"
                                        alt="Nachricht-an-den-Gastgeber-Icon">
                                </div>
                                <div class="box--content">
                                    <p class="box--title">Cancellation policies</p>
                                    <div class="tent--details-btn"><button>View all policies</button></div>
                                </div>
                            </div> -->
                            <p class="cancel-policy">To receive a full refund, you must cancel your reservation at least 10 days
                                before arrival. If there are at least 7 days left until check-in, you will receive a 50% refund for
                                all nights booked. If there are less than 7 days left until check-in, you will receive a 10% refund
                                for all nights booked.</p>
                        </div>
                        <!-- Total Here -->
                        <div class="order--total-wrap order_smry">
                            <div class="title--wrap">
                                <h4 class="title--inner gesm_title"><?php esc_html_e('Total amount', 'smoobu-calendar'); ?></h4>
								<div class="title--wrap amount--wrap ammount_sumry">
									<h4 class="title--inner">
										<?php echo wc_price($order_total); ?>
									</h4>
									<p class="sub--title taxes_fees"><?php esc_html_e('Including taxes and fees', 'smoobu-calendar'); ?></p>
                            	</div>
								<?php
								$order = wc_get_order($order_id);
								$status_slug = $order->get_status();
								$status_name = wc_get_order_status_name($status_slug);

								?>
                                <p class="sub--title payment_status"><?php esc_html_e('Payment status','smoobu-calendar'); ?><?php echo '&nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;&nbsp; <span>' . $status_name .'</span>'; ?></p>
								<?php echo get_invoice_pdf_button_by_order_id($order_id);  ?>
                            </div>
                        </div>
                        <!-- Download Invoice -->
						  <div class="something--happened-wrap">
                            <div class="title--wrap">
                                <h4 class="title--inner-small white_card_title"><?php esc_html_e('Something came up?', 'smoobu-calendar'); ?></h4>
                                <p class="sub--title white_card_txt"><?php esc_html_e('Would you like to change your arrival date? No problem at all.', 'smoobu-calendar'); ?></p>
                            </div>
                            <div class="modify_green_btn">
                                <button id="modify_booking"><?php esc_html_e('Request change', 'smoobu-calendar'); ?></button>
                            </div>
                        </div>
						
                        <div class="add--booking-calendar-wrap">
                            <div class="add--booking-calendar-content">
                                <div class="title--wrap">
                                    <h4 class="title--inner-small"><?php esc_html_e('Add booking to calendar', 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Click here to add your booking directly to your calendar.', 'smoobu-calendar'); ?></p>
                                </div>
                            </div>
                            <div class="add_to_clndr_btn">
								<?php 
									$event_title = urlencode($product_name . ' Buchung');
									$location = urlencode($geo_address);
									$date_start = date_create($start_date);
									$date_end = date_create($end_date);
								?>
                                <a href="https://www.google.com/calendar/render?action=TEMPLATE&text=<?php echo $event_title ?>&dates=<?php echo date_format($date_start,"Ymd\THis\Z") ?>/<?php echo date_format($date_end,"Ymd\THis\Z"); ?>&details=<?php echo $event_title ?>&location=<?php echo $location ?>" target="_blank"><?php esc_html_e('Add', 'smoobu-calendar'); ?></a>
                            </div>
                        </div>

                        <div class="something--happened-wrap">
                            <div class="title--wrap">
                                <h4 class="title--inner-small white_card_title"><?php esc_html_e('All around', 'smoobu-calendar'); ?> <?= $product_name ?></h4>
                                <p class="sub--title white_card_txt"><?php esc_html_e('Here you will find restaurant recommendations, shopping opportunities, excursion destinations and further information about', 'smoobu-calendar'); ?> <?= $product_name ?></p>
                            </div>
                            <div class="modify_green_btn show_info">
                                <button id="show_info"><?php esc_html_e('View', 'smoobu-calendar'); ?></button>
                            </div>
                        </div>
                        <script>
                            jQuery(function($){
                                $('.wpcf7-select').on('change', function() {
                                    if ($(this).val() === 'Antrag auf Gutscheineinlösung' || $(this).val() === 'Voucher redemption request') {
                                        $('.coupon-field-cf7').show();
                                    } else {
                                        $('.coupon-field-cf7').hide();
                                    }
                                });
                            });
                            jQuery("#show_info").on("click", function () {
                                jQuery(".info_popup").show();
                                });
                            jQuery(".close_popup").on("click", function () {
                                jQuery(".info_popup").hide();
                            });
                        </script>
                    </div>
                </div>
                <div class="modal fade modelpopdesgn textalignother " id="myModal6" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?php if ($arrival_and_departure_heading_text) { ?>
                                        <?php echo $arrival_and_departure_heading_text; ?>
                                    <?php } ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php if ($arrival_and_departure) {
                                    $arivalsarray = $arrival_and_departure;
                                    ?>
                                    <?php
                                    foreach ($arivalsarray as $arrival) {
                                        ?>
                                        <h5><?php echo $arrival['arrival_and_departure_popup_heading']; ?></h5>
                                        <p class="textpopupparagraph">
                                            <?php echo $arrival['arrival_and_departure_popup_description']; ?>
                                        </p>
                                        <hr />
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade modelpopdesgn textalignother  " id="myModal5" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?php if ($booking_cancellation_main_heading) { ?>
                                        <?php echo $booking_cancellation_main_heading; ?>
                                    <?php } ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php if ($_booking_and_cancellation) {
                                    $bookingarray = $_booking_and_cancellation;
                                    ?>
                                    <?php
                                    foreach ($bookingarray as $booking) {
                                        ?>
                                        <h5><?php echo $booking['_booking_&_cancellation_popup_heading']; ?></h5>
                                        <p class="textpopupparagraph"><?php echo $booking['_booking_&_cancellation_popup_description']; ?>
                                        </p>
                                        <hr />
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade modelpopdesgn imgiconpop " id="myModal4" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?php if ($house_rules_and_information_main_heading) { ?>
                                        <?php echo $house_rules_and_information_main_heading; ?>
                                    <?php } ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php if ($house_rules_and_information_popup_icons) {
                                    $iconsarray = $house_rules_and_information_popup_icons;
                                    ?>
                                    <ul class="iconspoplist">
                                        <?php
                                        foreach ($iconsarray as $icons) {
                                            ?>
                                            <li>
                                                <?php if (has_post_thumbnail($icons->ID)) { ?>
                                                    <?php
                                                    $image = wp_get_attachment_url(get_post_thumbnail_id($icons->ID, 'full'));
                                                    ?>
                                                    <img class="iconpopssss" src="<?php echo $image; ?>" />
                                                <?php } ?>
                                                <span class="icontext">
                                                    <?php echo $icons->post_title;
                                                    ?>
                                                </span>
                                            </li>
                                            <?php
                                        }
                                }
                                ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $response = ob_get_clean();
            echo $response;
        } else {
            echo '<div class="order-not_found"><p>Order not found.</p><button class="no-back">Go Back</button></div>';
        }
    }
    wp_die();
}

add_action('wp_ajax_get_order_details', 'get_order_details'); // For logged-in users
add_action('wp_ajax_nopriv_get_order_details', 'get_order_details'); // For non-logged-in users

function process_form_data()
{
    error_log('Processing form data...'); // Logs to wp-content/debug.log
    $vendor_name = sanitize_text_field($_POST['vendor_name']);
    $vendor_email = sanitize_text_field($_POST['vendor_email']);
    $vendor_number = sanitize_text_field($_POST['vendor_number']);
    $first_name = sanitize_text_field($_POST['first_name']);
	$last_name = sanitize_text_field($_POST['last_name']);
	$get_date_created = sanitize_text_field($_POST['get_date_created']);
    $customer_phone = sanitize_text_field($_POST['customer_phone']);
    $billing_address = sanitize_text_field($_POST['billing_address']);
    $billing_country = sanitize_text_field($_POST['billing_country']);
    $billing_email = sanitize_text_field($_POST['billing_email']);
    $order_no = sanitize_text_field($_POST['order_no']);
    $order_password = sanitize_text_field($_POST['order_password']);
    $order_name = sanitize_text_field($_POST['order_name']);
    $arrival_date = sanitize_text_field($_POST['arrival_date']);
    $departure_date = sanitize_text_field($_POST['departure_date']);
    $checkin_time = sanitize_text_field($_POST['checkin_time']);
    $order_price = sanitize_textarea_field($_POST['order_price']);
    $special_req = sanitize_textarea_field($_POST['special_req']);

    $to = 'support@book-a-bubble.com,anfragen@book-a-bubble.dee' ;//$vendor_email . ', ' . get_option('admin_email');
    $subject = "Modify Request: Order #$order_no";
    $message = "
    <div style=\"background-color: #54775E; font-family: 'Google Sans';\">
        <div style=\" width: 635px; max-width: 640px; margin: 0 auto; \">
            <div style=\"background-color:#54775e;display:flex;padding: 20px 0px;\">
                <div style=\"width: 50%;text-align: left;s\">
                    <img src=\"https://dash.book-a-bubble.de/wp-content/uploads/2024/12/Rectangle-e1736171010611.png\">
                </div>
            </div>
        </div>
    </div>
    <div style=\"font-family: 'Google Sans';\">
        <div style=\" width: 635px; max-width: 640px; margin: 0 auto; \">
            <div>
                <h1 style=\"color: #000;padding:48px 0 0px 0; font-size: 30px;font-weight: 500;margin-bottom: 0px;\"><span>$first_name</span> has requested the following changes to the order.</h1>
            </div>
            <div style=\" padding-top: 0px; \">
                <h4 style=\" color: #54775E; font-weight: 600; font-size: 18px; padding-bottom: 5px; \">Booking Changes Details
                </h4>
                <div style=\" border: 1px solid #A6A6A6; \">
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\">Order Create Date</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\"><span>{$get_date_created}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\">Order No</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\"><span>{$order_no}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\">First Name</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\"><span>{$first_name}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\">Last Name</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\"><span>{$last_name}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000;text-align:left;\">Location</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000;text-align:left;\">{$order_name}</p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\">Checkin date</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; margin: 0; padding: 0; text-align:left;\"><span>{$arrival_date} {$checkin_time}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000; text-align:left;\">Checkout date</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000; text-align:left;\"><span>{$departure_date} {$checkin_time}</span></p>
                        </div>
                    </div>
                    <div style=\" padding: 10px; display: flex; align-items: center; gap: 20px; border-bottom: 1px solid
                        #A6A6A6; border: none; \">
                        <div style=\" width: 170px; \">
                            <p style=\" color: #000;text-align:left;\">Special Request</p>
                        </div>
                        <div style=\" width: 395px; \">
                            <p style=\" color: #000;text-align:left;\">{$special_req}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Book A Bubble <no-reply@book-a-bubble.de>',
    ];

    if (wp_mail($to, $subject, $message, $headers)) {
        wp_send_json_success(['message' => 'Your request has been submitted successfully!']);
    } else {
        error_log('Failed to send email.'); // Log failure
        wp_send_json_error(['message' => 'Failed to send email.']);
    }
}
add_action('wp_ajax_process_form_data', 'process_form_data');
add_action('wp_ajax_nopriv_process_form_data', 'process_form_data');

function process_refund_email()
{
    error_log('Processing form Email data...');
    
    // Sanitize input fields
    $vendor_name     = sanitize_text_field($_POST['vendor_name']);
    $vendor_email    = sanitize_text_field($_POST['vendor_email']);
    $vendor_number   = sanitize_text_field($_POST['vendor_number']);
    $first_name       = sanitize_text_field($_POST['first_name']);
	$last_name       = sanitize_text_field($_POST['last_name']);
	$get_date_created = sanitize_text_field($_POST['get_date_created']);
    $customer_phone  = sanitize_text_field($_POST['customer_phone']);
    $billing_address = sanitize_text_field($_POST['billing_address']);
    $billing_country = sanitize_text_field($_POST['billing_country']);
    $billing_email   = sanitize_text_field($_POST['billing_email']);
    $order_no        = sanitize_text_field($_POST['order_no']);
    $order_password  = sanitize_text_field($_POST['order_password']);
    $order_name      = sanitize_text_field($_POST['order_name']);
    $arrival_date    = sanitize_text_field($_POST['arrival_date']);
    $departure_date  = sanitize_text_field($_POST['departure_date']);
    $checkin_time    = sanitize_text_field($_POST['checkin_time']);
    $guest_count     = sanitize_textarea_field($_POST['guest_count']);
    $order_price     = floatval($_POST['order_price']);
    $addon_price     = sanitize_textarea_field($_POST['addon_price']);
    $total_price     = sanitize_textarea_field($_POST['totalPrice']);
    $addons_quantity = sanitize_textarea_field($_POST['addons_quantity']);
    $reason          = sanitize_textarea_field($_POST['reason']);
    $paymentDetails  = isset($_POST['paymentDetails']) ? sanitize_textarea_field($_POST['paymentDetails']) : 'Voucher';

    // Refund calculation
    $current_date     = new DateTime();
    $arrival          = new DateTime($arrival_date);
    $interval_10_days = clone $arrival;
    $interval_10_days->modify('-10 days');
    $interval_7_days  = clone $arrival;
    $interval_7_days->modify('-7 days');

    $refund_details = '';
    $refund_percentage = 0;
    $refund_amount = 0;

    if ($current_date < $interval_10_days) {
        $refund_percentage = 100;
        $refund_amount     = $order_price;
        $refund_details    = "Until {$interval_10_days->format('F jS')}: 100% refund (€" . number_format($refund_amount, 2) . ")";
    } elseif ($current_date >= $interval_10_days && $current_date < $interval_7_days) {
        $refund_percentage = 50;
        $refund_amount     = $order_price * 0.50;
        $refund_details    = "From {$interval_10_days->format('F jS')} until {$interval_7_days->format('F jS')}: 50% refund (€" . number_format($refund_amount, 2) . ")";
    } elseif ($current_date >= $interval_7_days && $current_date <= $arrival) {
        $refund_percentage = 10;
        $refund_amount     = $order_price * 0.10;
        $refund_details    = "From {$interval_7_days->format('F jS')}: 10% refund (€" . number_format($refund_amount, 2) . ")";
    } else {
        $refund_details = "No refund available after arrival date.";
    }

    // Email subject
    $subject = "Refund Request: Order #$order_no";

    // Email body (using simplified HTML template)
    $message = "<div style=\"font-family: 'Arial', sans-serif; max-width: 640px; margin: auto; padding: 20px; background-color: #f9f9f9; color: #000; border: 1px solid #ddd;\">";
    $message .= "<h2 style=\"color: #54775E;\">Refund Request from {$first_name}</h2>";
	$message .= "<p><strong>Order Create Date:</strong> {$get_date_created}</p>";
	$message .= "<p><strong>First Name:</strong> {$first_name}</p>";
	$message .= "<p><strong>Last Name:</strong> {$last_name}</p>";
    $message .= "<p><strong>Order Number:</strong> {$order_no}</p>";
    $message .= "<p><strong>Customer Email:</strong> {$billing_email}</p>";
    $message .= "<p><strong>Phone:</strong> {$customer_phone}</p>";
    $message .= "<p><strong>Booking Location:</strong> {$order_name}</p>";
    $message .= "<p><strong>Arrival Date:</strong> {$arrival_date} {$checkin_time}</p>";
    $message .= "<p><strong>Departure Date:</strong> {$departure_date} {$checkin_time}</p>";
    $message .= "<p><strong>Guests:</strong> {$guest_count}</p>";
    $message .= "<p><strong>Total Price:</strong> {$total_price}</p>";
    $message .= "<p><strong>Reason for Refund:</strong><br>" . nl2br($reason) . "</p>";
    $message .= "<p><strong>Payment Method:</strong> {$paymentDetails}</p>";
    $message .= "<hr>";
    $message .= "<p><strong>Refund Calculation:</strong><br>{$refund_details}</p>";
    $message .= "</div>";

    $to='';
    if($paymentDetails=='Voucher'){
	$to = 'support@book-a-bubble.com,rechnung@bubble-tent.net,anfragen@book-a-bubble.de';
	}else{
		$to = 'support@book-a-bubble.com,rechnung@bubble-tent.net,anfragen@book-a-bubble.de';
	}
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Book A Bubble <no-reply@book-a-bubble.de>',
    ];

    if (wp_mail($to, $subject, $message, $headers)) {
        wp_send_json_success('Email sent successfully.');
    } else {
        wp_send_json_error('Failed to send email.');
    }
}

add_action('wp_ajax_process_refund_email', 'process_refund_email');
add_action('wp_ajax_nopriv_process_refund_email', 'process_refund_email');


add_action('woocommerce_checkout_create_order', 'add_random_password_to_order_meta', 10, 2);
function add_random_password_to_order_meta($order, $data)
{
    $random_password = wp_generate_password(10, false);
    $order->update_meta_data('_random_password', $random_password);
    $order->save();
    
}



add_action('wp_ajax_get_calorder_details', 'get_calorder_details');
add_action('wp_ajax_nopriv_get_calorder_details', 'get_calorder_details');

function get_calorder_details()
{
    if (isset($_POST['calorder_id'])) {
        $odate = new DateTime($_POST['calorder_id']['dateString']);
        $fodate = date_format($odate, "d-m-Y");
       
        $args = [
            'post_type'   => 'shop_order',
            'post_status' => ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'],
            'meta_query'  => [
                [
                    'key'   => 'smoobu_calendar_start',
                    'value' => $fodate,
                    'compare' => '='
                ]
            ]
        ];
        
        $orders = get_posts($args);
        if(!$orders) {
            wp_send_json_success(['html' => '<p>No order found.</p>']);
        }
        $order_id =  isset($orders[0]) ? intval($orders[0]->ID) : 0;
        $order = wc_get_order($order_id);
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $booking_created_date = $order->get_date_created()->date('d/m/Y');
        $total_amount = $order->get_total();
        $status = ucfirst($order->get_status());
        $booking_start_date = get_post_meta($order_id, 'smoobu_calendar_start', true);
        $booking_end_date = get_post_meta($order_id, 'smoobu_calendar_end', true);
        $start_date = new DateTime($booking_start_date);
        $end_date = new DateTime($booking_end_date);
        $date_interval = $start_date->diff($end_date);
        $number_of_days = $date_interval->days;
        $number_of_nights = $number_of_days - 1;
        $number_of_adults = get_post_meta($order_id, '_number_of_adults', true);
        $number_of_kids = get_post_meta($order_id, '_number_of_kids', true);
        $customer_phone = $order->get_billing_phone();
        $billing_address = $order->get_billing_address_1() . ' ' . $order->get_billing_city();
        $billing_email = $order->get_billing_email();
        $guest_notes = $order->get_customer_note();
		
        $addons = '';
        foreach ($order->get_items('fee') as $addon_item) {
            $addons_quantity .= $addon_item->get_quantity();
            $addons .= $addon_item->get_name() . ' (x' . $addons_quantity . ')';
        }
        ;
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $product_price = $item->get_total();
        }
        ;
        $status = $order->get_status();
        $status_text = '';
        $status_badge = '';
        switch ($status) {
            case 'processing':
                $status_badge = 'bg-success-light';
                $status_text = 'Processing';
                break;
            case 'on-hold':
                $status_badge = 'bg-primary-light';
                $status_text = 'On Hold';
                break;
            case 'completed':
                $status_badge = 'bg-success-light';
                $status_text = 'Completed';
                break;
            case 'cancelled':
                $status_badge = 'bg-warning-light';
                $status_text = 'Cancelled';
                break;
            case 'failed':
                $status_badge = 'bg-danger-light';
                $status_text = 'Failed';
                break;
        }

        ?>
        <div class="order-book">
            <dvi class="client-name">
                <h2><?= esc_html($first_name . ' ' . $last_name); ?></h2>
            </dvi>
            <div class="order-box">
                <h2>Booking</h2>
                <div class="order-dates">
                    <h3>Check in date: <span><?= esc_html($booking_start_date); ?></span></h3>
                    <h3>Check out date: <span><?= esc_html($booking_end_date); ?></span></h3>
                </div>
                <div class="stay-length">
                    <h3>Length of Stay:
                        <span><?= ($number_of_nights) ? esc_html($number_of_nights) . ' Night' : esc_html($number_of_days) . ' Day' ?></span>
                    </h3>
                </div>
                <div class="order-tent">
                    <h3>Bubble Tent: <span><?= esc_html($product_name); ?></span></h3>
                </div>
                <div class="order-price">
                    <h3>Price: <span><?= wc_price($product_price); ?></span></h3>
                </div>
                <div class="order-guest">
                    <h3>N° of guests: <span><?= esc_html($number_of_adults); ?> adults, <?= esc_html($number_of_kids); ?>
                            kid</span></h3>
                </div>
                <div class="orderno">
                    <h3>Order n°: <span><?= esc_html($order_id); ?></span></h3>
                </div>
                <div class="add-item">
                    <h3>Additional items: <span><?= esc_html($addons); ?></span>
                    </h3>
                </div>
                <div class="ordertoal-amount">
                    <h3>Total amount: <span><?= wc_price($total_amount); ?></span></h3>
                </div>
                <div class="order-status">
                    <h3>Booking status: <div class="badge custom-badge <?php echo $status_badge; ?> rounded-pill">
                            <?= esc_html($status); ?></div>
                    </h3>
                </div>
            </div>
            <div class="guest-box">
                <div class="guestbox-header">
                    <h2>Guest</h2>
                    <!-- <div class="edit-guest">
                        <a href="">Edit <img src="/wp-content/uploads/2024/12/pen.png" alt=""></a>
                    </div> -->
                </div>
                <div class="guest-name">
                    <h3>Name: <span><?= esc_html($first_name . ' ' . $last_name); ?></span></h3>
                </div>
                <div class="guest-email">
                    <h3>Email: <span><a
                                href="mailto:<?= esc_html($billing_email); ?>"><?= esc_html($billing_email); ?></a></span></h3>
                </div>
                <div class="guest-phone">
                    <h3>Phone n°: <span><a
                                href="tel:<?= esc_html($customer_phone); ?>"><?= esc_html($customer_phone); ?></a></span></h3>
                </div>
                <div class="guest-address">
                    <h3>Address: <span><?= esc_html($billing_address); ?></span></h3>
                </div>
                <div class="guest-notes">
                    <h3>Notes: <span><?= esc_html($guest_notes); ?></span></h3>
                </div>
            </div>
            <!-- <div class="ordernote-box">
                <h2>Internal Notes</h2>
                <textarea name="" id="" class="order-notes"></textarea>
            </div> -->
        </div>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    } else {
        wp_send_json_error(['message' => 'No orders found for the selected date.']);
    }
    wp_die();
}

// Function to get all vendor orders and organize them by month and year
function getVendorOrdersByMonthAndYear($vendorId, $year)
{
    global $wpdb; // Access the WordPress database

    // Query to get all orders for the given vendor
    // $results = $wpdb->get_results(
    //     $wpdb->prepare(
    //         "SELECT * FROM {$wpdb->prefix}posts AS posts
    //         INNER JOIN {$wpdb->prefix}postmeta AS meta
    //         ON posts.ID = meta.post_id
    //         WHERE posts.post_type = 'shop_order'
    //         AND posts.post_status IN ('wc-completed', 'wc-processing')
    //         AND meta.meta_key = '_dokan_vendor_id'
    //         AND meta.meta_value = %d ORDER BY  `post_date` DESC",
    //         $vendorId
    //     ),
    //     ARRAY_A
    // );
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT posts.*, meta.meta_value AS vendor_id, YEAR(posts.post_date) AS year
            FROM {$wpdb->prefix}posts AS posts
            INNER JOIN {$wpdb->prefix}postmeta AS meta
            ON posts.ID = meta.post_id
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ('wc-completed', 'wc-processing')
            AND meta.meta_key = '_dokan_vendor_id'
            AND meta.meta_value = %d
            AND YEAR(posts.post_date) = %d
            ORDER BY posts.post_date DESC",
            $vendorId,
            $year
        ),
        ARRAY_A
    );

    // Initialize an array to store orders grouped by month and year
    $ordersByMonthYear = [];

    foreach ($results as $order) {
        // Get the order date
        $orderDate = $order['post_date'];
        $dateObj = new DateTime($orderDate);

        // Extract month and year
        $month = $dateObj->format('m');
        $year = $dateObj->format('Y');

        // Create a key for month and year
        $key = $year . '-' . $month;

        // Initialize the key if it does not exist
        if (!isset($ordersByMonthYear[$key])) {
            $ordersByMonthYear[$key] = [];
        }

        // Add order to the respective month and year key
        $ordersByMonthYear[$key][] = $order;
    }

    return $ordersByMonthYear;
}

add_action('wp_ajax_fetch_addons', 'fetch_addons');
add_action('wp_ajax_nopriv_fetch_addons', 'fetch_addons');

function fetch_addons() { 
    if (!isset($_POST['listing_id'])) {
        wp_send_json_error('Invalid Request');
    }

    $listing_id = intval($_POST['listing_id']);
    $product_id = get_post_meta($listing_id, 'product_id', true); // Fetch product ID from CMB2 custom field

    if (!$product_id) {
        wp_send_json_error('No product assigned to this listing.');
    }

    // Fetch addon items for the product
    $addons = get_addon_items_for_product($product_id); // Custom function to fetch addons
    $max_adults = get_post_meta($product_id, 'max_adults', true);
    $max_kids = get_post_meta($product_id, 'max_kids', true);
    // $_availability = get_post_meta($listing_id);
    // echo "<pre>";
    // print_r($_availability);
    // echo "</pre>";
    
    if (!empty($addons)) {
        //wp_send_json_error('No addons found for this product.');
        // Generate HTML for addons
        foreach ($addons as $addon) {
            ?>
            <div class="package-item d-flex">
                <input type="checkbox" name="addons[]" class="addon_checkbox" value="<?php echo esc_html($addon['charges']); ?>">
                <input type="text" name="upgrade_name[]"  value="<?php echo esc_html($addon['name']); ?>" style="display:none;">
                <input type="hidden" name="is_per_person[]" class="is_per_person"  value="<?php echo esc_html($addon['is_per_person']); ?>">
                <input type="hidden" name="include_kids[]" class="include_kids"  value="<?php echo esc_html($addon['include_kids']); ?>">
                <input type="hidden" name="is_per_night[]" class="is_per_night"  value="<?php echo esc_html($addon['is_per_night']); ?>">
                <p><span class="pkg-name"><?php echo esc_html($addon['name']); ?></span> <span class="pkg-price"><?php echo esc_html($addon['charges']); ?>€<?php echo ($addon['is_per_person']) ? '/person' : '/booking' ?></span></p>
            </div>
            <?php
        }
        $addons_html = ob_get_clean();
    } else {
        $addons_html = '<div> No upgrades available</div>';
    }


    $adults_options = '<option value="">Select</option>';
    for ($i = 1; $i <= $max_adults; $i++) {
        $adults_options .= "<option value=\"$i\">$i</option>";
    }

    $kids_options = '<option value="">Select</option>';
    for ($i = 1; $i <= $max_kids; $i++) {
        $kids_options .= "<option value=\"$i\">$i</option>";
    }

    $coupons = get_coupons_for_product($product_id);
    
    if (!empty($coupons)) {
        $coupons_options = "<option value=''>Select discount code</option>";
        foreach ($coupons as $coupon) {
            $coupons_options .= "<option value='". $coupon['code'] ."'>".$coupon['description']."</option>";
        }
    } else {
        $coupons_options = "<option value=''>No discount code available</option>";
    }

    //$blocked_dates = array('02/10/2025', '02/15/2025', '02/20/2025'); 
    $current_dates_array = [];
     // Fetch current availability
     $availability = get_post_meta($listing_id, '_availability', true);
     if ($availability) { 
        $current_dates_string = $availability['dates'] ?? 'Array';
        $current_dates_string = str_replace('Array', '', $current_dates_string);
        $current_dates_array = array_filter(explode('|', $current_dates_string));
     }

    wp_send_json_success([
        'addons_html' => $addons_html,
        'adults_options' => $adults_options,
        'kids_options' => $kids_options,
        'coupons_options' => $coupons_options,
        'blocked_dates' => json_encode($current_dates_array),
    ]);
}
add_action('wp_ajax_calculate_extra_guest_price', 'calculate_extra_guest_price');
add_action('wp_ajax_nopriv_calculate_extra_guest_price', 'calculate_extra_guest_price');


function get_addon_items_for_product($product_id) {
    // Fetch addons stored in product meta
    // $product = wc_get_product($product_id);
    // $meta_data = $product->get_meta_data();
    $addons = get_post_meta($product_id, 'add_ons', true);
    
    if (!is_array($addons) || empty($addons)) {
        return [];
    }
    return $addons;
}
add_action('wp_ajax_create_booking_order', 'create_booking_order');
add_action('wp_ajax_nopriv_create_booking_order', 'create_booking_order');

add_action('wp_ajax_get_average_price_vdash', 'get_average_price_vdash');
add_action('wp_ajax_nopriv_get_average_price_vdash', 'get_average_price_vdash');

function get_average_price_vdash() {
    
    $start_date = $_POST['star_date'];
    $end_date = $_POST['end_date'];
    $property_id = $_POST['property_id'];
    $product_id = get_post_meta($property_id, 'product_id', true); // Fetch product ID from CMB2 custom field

    if (!$product_id) {
        wp_send_json_error('No product assigned to this listing.');
        exit();
    }

    // Fetch addon items for the product
    // $max_adults = get_post_meta($product_id, 'max_adults', true);
    // $max_kids = get_post_meta($product_id, 'max_kids', true);
    $max_adults = $_POST['number_of_adults'];
    $max_kids = $_POST['number_of_kids'];
    $total_guests = $max_adults + $max_kids;
    $extra_starts_at = get_post_meta($product_id, 'extra_charges_starting_at', true);
    $extra_charges_per_guest = get_post_meta($product_id, 'extra_charges_per_guest', true);
    $guest_fee = 0;
    if (
        !empty($extra_charges_per_guest) &&
        !empty($extra_starts_at) &&
        $total_guests >= $extra_starts_at
    ) {

        $guest_fee = ($total_guests - $extra_starts_at + 1) * $extra_charges_per_guest;
        //$guest_key = __('Extra Guest Charges', 'smoobu-calendar');
    }

    $classNew = new Smoobu_Ajax();
    $average_price = $classNew->fetch_average_price(
        $start_date,
        $end_date,
        $property_id
    );
    $currency_symbol = get_woocommerce_currency_symbol();
    $earlier = new DateTime($start_date);
    $later = new DateTime($end_date);
    $abs_diff = $later->diff($earlier)->format("%a"); //3

    $discount = [];
    if(isset($_POST['discount_codes']) && !empty($_POST['discount_codes'])){
        $discountData = calculate_coupon_code_discount($_POST['discount_codes']);
        $discount = [
            'amount' => $discountData->get_amount(),
            'type' => $discountData->get_discount_type(),
        ];
    }
    
    $data = array(
        'data'           => __( 'Got the data.', 'smoobu-calendar' ),
        'averagePrice'   => $average_price,
        'guestFee'   => $guest_fee,
        'currencySymbol' => html_entity_decode($currency_symbol),
        'nights' => $abs_diff,
        'discount' => $discount,
    );
    wp_send_json_success( $data, 200 );
    exit();
    // wp_send_json_success($average_price);
}

function create_booking_order()
{
    global $wpdb;
    if (!isset($_POST['data'])) {
        wp_send_json_error('Invalid request');
    }
    parse_str($_POST['data'], $data);

    // Load WooCommerce Order class
    if (!class_exists('WC_Order')) {
        return;
    }
    
    $bubble_tent_listing_type = sanitize_text_field($data['bubble_tent_listing_type']);
    $bubble_tent_id = intval($data['bubble_tent_listing']);
    $product_id = get_post_meta($bubble_tent_id, 'product_id', true);
    $quantity = sanitize_text_field($data['product_qty']);
    $first_name = sanitize_text_field($data['billing_first_name']);
    $last_name = sanitize_text_field($data['billing_last_name']);
    $message_to_landlord = sanitize_text_field($data['message_to_landlord']);
    $amount_people = intval($data['_number_of_adults']);
    $amount_children = intval($data['_number_of_kids']);
    $checkin_date = sanitize_text_field($data['smoobu_calendar_start']);
    $checkout_date = sanitize_text_field($data['smoobu_calendar_end']);
    $email = sanitize_email($data['billing_email']);
    $phone = sanitize_text_field($data['billing_phone']);
    $addons_price = isset($data['addons']) ? array_map('sanitize_text_field', $data['addons']) : [];
    $addons_name = isset($data['upgrade_name']) ? array_map('sanitize_text_field', $data['upgrade_name']) : [];
    $total_amount = floatval($data['total_amount']);
    $custom_price = sanitize_text_field($data['product_price']);
    $extraGuestCharges = sanitize_text_field($data['product_price_guest']);
    $discount_code = sanitize_text_field($data['discount']);
    
    if($bubble_tent_listing_type == 'Direct booking') {
        $order = wc_create_order();
        
        // Get the product object
        $product = wc_get_product($product_id);

        if ($product) {
            // Create a new order item for the product
            $order_item = new WC_Order_Item_Product();
            $order_item->set_product($product);
            $order_item->set_quantity($quantity);
            $order_item->set_subtotal($custom_price * $quantity);
            $order_item->set_total($custom_price * $quantity);
            $order_item->set_name($product->get_name()); 
    
            // Add the item to the order
            $order->add_item($order_item);
        }
        
        // Add custom extra fee (optional)
        if(count($addons_price)) {
            foreach ($addons_price as $pkey => $pvalue) {
                $extra_fee_name = '1 x '.$addons_name[$pkey];
                $extra_fee_amount = $pvalue;
                $item_fee = new WC_Order_Item_Fee();
                $item_fee->set_name($extra_fee_name);
                $item_fee->set_amount($extra_fee_amount);
                $item_fee->set_total($extra_fee_amount);
                $order->add_item($item_fee);
            }
        }
    
        if(!empty($extraGuestCharges)) {
            $extra_fee_name = 'Extra Guest Charges';
            $extra_fee_amount = $extraGuestCharges;
            $item_fee = new WC_Order_Item_Fee();
            $item_fee->set_name($extra_fee_name);
            $item_fee->set_amount($extra_fee_amount);
            $item_fee->set_total($extra_fee_amount);
            $order->add_item($item_fee);
        }
        
        
        $order->update_meta_data('_number_of_adults', $amount_people);
        $order->update_meta_data('message_to_landlord', $message_to_landlord);
        $order->update_meta_data('_number_of_kids', $amount_children);
        $order->update_meta_data('smoobu_calendar_start', $checkin_date);
        $order->update_meta_data('smoobu_calendar_end', $checkout_date);
        $order->update_meta_data('_first_name', $first_name);
        $order->update_meta_data('_last_name', $last_name);
        $order->update_meta_data('_phone', $phone);
        $order->update_meta_data('_language', $_POST['language']);
        $order->update_meta_data('_discount', $_POST['discount']);
        $order->update_meta_data('_booking_status', 'Completed');
        // Set billing details
        $order->set_billing_first_name($first_name);
        $order->set_billing_last_name($last_name);
        $order->set_billing_email($email);
        $order->set_billing_phone($phone);
    
        // Set shipping details
        $order->set_shipping_first_name($first_name);
        $order->set_shipping_last_name($last_name);
        // $order->set_shipping_email($email);
        // $order->set_shipping_phone($phone);
    
       
        
        // Set the order status to completed
        $order->set_status( 'completed' );
        if (!empty($discount_code)) {
           
            $coupon = new WC_Coupon( $discount_code );

            $discount_total = $coupon->get_amount();
            if ($coupon->get_discount_type() != "fixed_cart") {
                $order_price = $custom_price * $quantity;
                $discount_total = ($order_price * $discount_total) / 100;
            } 
           
            $discount_per_item = bcdiv( $discount_total, $order->get_item_count(), 2 );
            // Discount remainder (we don't want to forget about 2 cents of discount, right? ;)
            $discount_remainder = $discount_total - $discount_per_item * $order->get_item_count();

            // loop through product order items
            foreach( $order->get_items() as $order_item ){

                $discount = $discount_per_item * $order_item->get_quantity() - $discount_remainder;
                // we are going to add discount remainder only in the first iteration
                $discount_remainder = 0;

                $order_item->set_total( $order_item->get_subtotal() - $discount );
                $order_item->save();

            }
            // $order->apply_coupon( $discount);
            $item = new WC_Order_Item_Coupon();
            $item->set_props( array( 'code' => $discount_code, 'discount' => $discount_total ) );
            $order->add_item( $item );
            $order->calculate_totals();

            $order->save();
        } else {
            $order->calculate_totals();
            $order->save();
        }

        $start_date_obj = DateTime::createFromFormat('d-m-Y', date('d-m-Y', strtotime($checkin_date)));
        $end_date_obj = DateTime::createFromFormat('d-m-Y', date('d-m-Y', strtotime($checkout_date)));
        if ($start_date_obj && $end_date_obj && $start_date_obj <= $end_date_obj) {
            $current_date = $start_date_obj;
            $dates_array = [];
            while ($current_date < $end_date_obj) {
                $dates_array[] = $current_date->format('d-m-Y');
                $current_date->modify('+1 day');
            }

            $dates_array = array_map(function($date) {
                $date_parts = explode('-', $date);
                $date_parts[0] = ltrim($date_parts[0], '0');
                return implode('-', $date_parts);
            }, $dates_array);

            // Fetch current availability
            $availability = get_post_meta($bubble_tent_id, '_availability', true);
            if (!$availability) {
                $availability = array(
                    'dates' => 'Array',
                    'price' => '{}',
                );
            }
            
            $current_dates_string = $availability['dates'] ?? 'Array';
            $current_dates_string = str_replace('Array', '', $current_dates_string);
            $current_dates_array = array_filter(explode('|', $current_dates_string));
            
            // Merge new dates and update
            $merged_dates = array_unique(array_merge($current_dates_array, $dates_array));
            sort($merged_dates);
            $updated_dates_string = 'Array' . implode('|', $merged_dates). '|';
            $availability['dates'] = $updated_dates_string;
            update_post_meta($bubble_tent_id, '_availability', $availability);

            //save records of bookings
            // Fetch booked dates meta
            $_booked_dates = get_post_meta($bubble_tent_id, '_booked_dates', true);

            // Ensure $_booked_dates is an array
            if (!is_array($_booked_dates)) {
                $_booked_dates = ['dates' => ''];
            }

            // Sanitize the booked dates string
            $_booked_dates_string = isset($_booked_dates['dates']) && is_string($_booked_dates['dates'])
                ? str_replace('Array', '', $_booked_dates['dates'])
                : '';

            // Convert string to array and filter empty entries
            $_booked_dates_array = array_filter(array_map('trim', explode('|', $_booked_dates_string)));

            // Ensure $dates_array is an array
            $dates_array = is_array($dates_array) ? $dates_array : [];

            // Merge existing and new dates
            $merged_booked_dates = array_unique(array_merge($_booked_dates_array, $dates_array));

            // Sort the result (optional if not needed by your logic)
            sort($merged_booked_dates);

            // Convert back to the string with 'Array' prefix
            $updated_booked_dates_string = 'Array' . implode('|', $merged_booked_dates) . '|';

            // Save the updated meta
            $_booked_dates['dates'] = $updated_booked_dates_string;
            update_post_meta($bubble_tent_id, '_booked_dates', $_booked_dates);

        }

        $user_id = get_current_user_id();
        $parent_vendor_id = get_user_meta($current_user->ID, 'parent_vendor_id', true);

        if (!empty($parent_vendor_id)) {
            $user_id = $parent_vendor_id;
        }
        $table_name = $wpdb->prefix . 'dokan_orders';
         // Data to insert
        $data = array(
            'order_id'    => $order->get_id(),
            'seller_id'   => $user_id,
            'order_total' => $order->get_total(),
            'net_amount'  => $order->get_total() * 0.90,
            'order_status'=> "wc-".$order->get_status()
        );

        $format = array('%d', '%d', '%f', '%f', '%s');

        // Insert the data into the database
        $wpdb->insert($table_name, $data, $format);

        create_ical_entry($order->get_id(), [], $order);
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        wp_send_json_success(array(
            'message' => __('Order Created successfully.', 'smoobu-calendar'),
            'order_id' => $order->get_id(),
        ));
        exit();

    } else {
        if ($checkin_date && $checkout_date) {
            // Generate the date range between check-in and check-out
            // $start_date = new DateTime($checkin_date);
            // $end_date = new DateTime($checkout_date);
            $start_date_obj = DateTime::createFromFormat('d-m-Y', date('d-m-Y', strtotime($checkin_date)));
            $end_date_obj = DateTime::createFromFormat('d-m-Y', date('d-m-Y', strtotime($checkout_date)));
            if ($start_date_obj && $end_date_obj && $start_date_obj <= $end_date_obj) {
                $current_date = $start_date_obj;
                $dates_array = [];
                while ($current_date < $end_date_obj) {
                    $dates_array[] = $current_date->format('d-m-Y');
                    $current_date->modify('+1 day');
                }

                $dates_array = array_map(function($date) {
                    $date_parts = explode('-', $date);
                    $date_parts[0] = ltrim($date_parts[0], '0');
                    return implode('-', $date_parts);
                }, $dates_array);
                
                // Fetch current availability
                $availability = get_post_meta($bubble_tent_id, '_availability', true);
                if (!$availability) {
                    $availability = array(
                        'dates' => 'Array',
                        'price' => '{}',
                    );
                }
                
                $current_dates_string = $availability['dates'] ?? 'Array';
                $current_dates_string = str_replace('Array', '', $current_dates_string);
                $current_dates_array = array_filter(explode('|', $current_dates_string));
                
                // Merge new dates and update
                $merged_dates = array_unique(array_merge($current_dates_array, $dates_array));
                sort($merged_dates);
                $updated_dates_string = 'Array' . implode('|', $merged_dates). '|';
                $availability['dates'] = $updated_dates_string;
            
                update_post_meta($bubble_tent_id, '_availability', $availability);

                //blocked dates in calender
                $blocked_checkin_dates = get_post_meta($bubble_tent_id, '_blocked_checkin_dates', true);
                if (!$blocked_checkin_dates) {
                    $blocked_checkin_dates = array(
                        'dates' => 'Array'
                    );
                }
                
                $current_blocked_dates_string = $blocked_checkin_dates['dates'] ?? 'Array';
                $current_blocked_dates_string = str_replace('Array', '', $current_blocked_dates_string);
                $current_blocked_dates_array = array_filter(explode('|', $current_blocked_dates_string));
                
                // Merge new dates and update
                $merged_blocked_dates = array_unique(array_merge($current_blocked_dates_array, $dates_array));
                sort($merged_blocked_dates);
                $updated_blocked_dates_string = 'Array' . implode('|', $merged_blocked_dates). '|';
                $blocked_checkin_dates['dates'] = $updated_blocked_dates_string;


                update_post_meta($bubble_tent_id, '_blocked_checkin_dates', $blocked_checkin_dates);

                reset($dates_array);
                $firstKey = key($dates_array);

                end($dates_array);
                $lastKey = key($dates_array);

                create_ical_entry_for_block_date($bubble_tent_id, 'waiting', $dates_array[$firstKey], $dates_array[$lastKey]);

            }

            while (ob_get_level()) {
                ob_end_clean();
            }

            wp_send_json_success(array(
                'message' => __('Date blocked successfully.', 'smoobu-calendar'),
                'dates' => $merged_blocked_dates,
            ));
            exit();
        }
    }
}

// Example usage (trigger this function when needed)
// add_action('init', function () {
//     if (isset($_GET['create_order'])) {
//         $order_id = create_booking_order();
//         if ($order_id) {
//             echo "Order created successfully with ID: " . $order_id;
//         } else {
//             echo "Failed to create order.";
//         }
//         exit;
//     }
// });


function get_coupons_for_product($product_id) {
    global $wpdb;

    // Query to fetch coupon IDs for the product
    $coupon_ids = $wpdb->get_col($wpdb->prepare(
        "
        SELECT p.ID
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm
            ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_coupon'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'product_ids'
            AND FIND_IN_SET(%d, pm.meta_value)
        ",
        $product_id
    ));

    // Fetch coupon details
    $coupons = [];

    if($coupon_ids) {
        foreach ($coupon_ids as $coupon_id) {
            $coupon = new WC_Coupon($coupon_id);
            $date_expires = $coupon->get_date_expires(); 
            if ($date_expires) {
                $expiration_timestamp = $date_expires->getTimestamp(); // Convert to timestamp
                $current_timestamp = current_time('timestamp'); // Get current timestamp
    
                if ($expiration_timestamp < $current_timestamp) {
                    continue;
                } 
            }
            $coupons[] = [
                'code' => $coupon->get_code(),
                'discount_type' => $coupon->get_discount_type(),
                'amount' => $coupon->get_amount(),
                'description' => $coupon->get_description(),
            ];
        }
    }

    return $coupons;
}
function update_user_personal_info() {

    $user_id = get_current_user_id();
    if ($user_id) {

        parse_str($_POST['form_data_pi'], $form_data);
        $first_name = sanitize_text_field($form_data['f-name']);
        $last_name = sanitize_text_field($form_data['l-name']);
        $street = sanitize_text_field($form_data['street']);
        $number = sanitize_text_field($form_data['number']);
        $plz = sanitize_text_field($form_data['plz']);
        $city = sanitize_text_field($form_data['city']);
        $tax_id = sanitize_text_field($form_data['tax-id']);

        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'billing_address_1', $street);
        update_user_meta($user_id, 'billing_phone', $number);
        update_user_meta($user_id, 'billing_postcode', $plz);
        update_user_meta($user_id, 'billing_city', $city);
        update_user_meta($user_id, 'tax_id', $tax_id);

        wp_send_json_success();
    } else {
       
        wp_send_json_error();
    }

    wp_die();
}
add_action('wp_ajax_update_user_personal_info', 'update_user_personal_info');
add_action('wp_ajax_nopriv_update_user_personal_info', 'handle_ajax_update_user_personal_info');

function update_user_company_info() {
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in.']);
    }

    $user_id = get_current_user_id();

    parse_str($_POST['form_data_ci'], $form_data);

    $company = sanitize_text_field($form_data['company']);
    $email = sanitize_email($form_data['e-mail']);
    $street = sanitize_text_field($form_data['street']);
    $number = sanitize_text_field($form_data['number']);
    $plz = sanitize_text_field($form_data['plz']);
    $city = sanitize_text_field($form_data['city']);
    $tax_id = sanitize_text_field($form_data['tax-id']);
    $iban_number = sanitize_text_field($form_data['iban_number']);

    update_user_meta($user_id, 'billing_company', $company);
    update_user_meta($user_id, 'billing_email', $email);
    update_user_meta($user_id, 'street', $street);
    update_user_meta($user_id, 'number', $number);
    update_user_meta($user_id, 'plz', $plz);
    update_user_meta($user_id, 'city', $city);
    update_user_meta($user_id, 'tax_id', $tax_id);
    update_user_meta($user_id, 'bank_iban', $iban_number);

    wp_send_json_success(['message' => 'Form updated successfully.']);
}
add_action('wp_ajax_update_user_company_info', 'update_user_company_info');
add_action('wp_ajax_nopriv_update_user_company_info', 'update_user_company_info');

add_action('wp_ajax_update_guest_info', 'update_guest_info');
function update_guest_info() {

    parse_str($_POST['guestformData'], $guestformData);
    // Get the order ID
    $order_id = intval($guestformData['guest_order_id']);

    if (!$order_id) {
        wp_send_json_error('Invalid order ID.');
    }

    // Get the order
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error('Order not found.');
    }

    // Update guest info in custom fields
    if (isset($guestformData['guest_fname'])) {
        update_post_meta($order_id, '_billing_first_name', sanitize_text_field($guestformData['guest_fname']));
    }
    if (isset($guestformData['guest_fname'])) {
        update_post_meta($order_id, '_shipping_first_name', sanitize_text_field($guestformData['guest_fname']));
    }
    if (isset($guestformData['guest_lname'])) {
        update_post_meta($order_id, '_billing_last_name', sanitize_text_field($guestformData['guest_lname']));
    }
    if (isset($guestformData['guest_lname'])) {
        update_post_meta($order_id, '_shipping_last_name', sanitize_text_field($guestformData['guest_lname']));
    }
    if (isset($guestformData['guest_phone'])) {
        update_post_meta($order_id, '_billing_phone', sanitize_text_field($guestformData['guest_phone']));
    }
    if (isset($guestformData['guest_phone'])) {
        update_post_meta($order_id, '_shipping_phone', sanitize_text_field($guestformData['guest_phone']));
    }
    if (isset($guestformData['guest_address'])) {
        update_post_meta($order_id, '_billing_address_1', sanitize_text_field($guestformData['guest_address']));
    }
    if (isset($guestformData['guest_city'])) {
        update_post_meta($order_id, '_billing_city', sanitize_text_field($guestformData['guest_city']));
    }
    if (isset($guestformData['guest_address'])) {
        update_post_meta($order_id, '_shipping_address_1', sanitize_text_field($guestformData['guest_address']));
    }
    if (isset($guestformData['guest_city'])) {
        update_post_meta($order_id, '_shipping_city', sanitize_text_field($guestformData['guest_city']));
    }
    if (isset($guestformData['guest_notes'])) {
        update_post_meta($order_id, 'message_to_landlord', sanitize_text_field($guestformData['guest_notes']));
    }

    wp_send_json_success('Guest info updated successfully.');
}
add_action('wp_ajax_update_order_notes', 'update_order_notes');
function update_order_notes() {

    parse_str($_POST['notesformData'], $notesformData);
    // Get the order ID
    $order_id = intval($notesformData['guest_order_id']);

    if (!$order_id) {
        wp_send_json_error('Invalid order ID.');
    }

    // Get the order
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error('Order not found.');
    }
    if (isset($notesformData['internal_notes'])) {
        $order->add_order_note(
            sanitize_textarea_field($notesformData['internal_notes']),
            false // Private note
        );
    }

    wp_send_json_success('Guest info updated successfully.');
}


function calculate_coupon_code_discount($coupon_code) {
    global $woocommerce;
    $coupon = new WC_Coupon($coupon_code);
    return $coupon;
}


// function get_blocked_dates() {
//     // Example: Fetch blocked dates from a custom database table or other source
//     $blocked_dates = array('02/10/2025', '02/15/2025', '02/20/2025');  // Replace with your logic

//     echo json_encode($blocked_dates);
//     wp_die(); // Always call wp_die() to end the AJAX request
// }

// add_action('wp_ajax_get_blocked_dates', 'get_blocked_dates');
// add_action('wp_ajax_nopriv_get_blocked_dates', 'get_blocked_dates');

add_action('wp_ajax_get_unblock_date', 'get_unblock_date');
add_action('wp_ajax_nopriv_get_unblock_date', 'get_unblock_date');

function get_unblock_date()
{
    if (isset($_POST['listingId']) && isset($_POST['date'])) {
        // $odate = new DateTime($_POST['date']['dateString']);
        // $fodate = date_format($odate, "d-m-Y");
        $current_dates_array = [];
        // Fetch current availability
        $availability = get_post_meta($_POST['listingId'], '_availability', true);
        if ($availability) { 
            $current_dates_string = $availability['dates'] ?? 'Array';
            $current_dates_string = str_replace('Array', '', $current_dates_string);
            $current_dates_array = array_filter(explode('|', $current_dates_string));
        }

        $blocked_checkin_dates = get_post_meta($_POST['listingId'], '_blocked_checkin_dates', true);
        if ($blocked_checkin_dates) { 
            $blocked_dates_string = $blocked_checkin_dates['dates'] ?? 'Array';
            $blocked_dates_string = str_replace('Array', '', $blocked_dates_string);
            $blocked_dates_array = array_filter(explode('|', $blocked_dates_string));
            foreach ($blocked_dates_array as $key => $value) {
                if ($value === $_POST['date']) {
                    unset($blocked_dates_array[$key]);
                }
            }
            $updated_blocked_dates_string = 'Array' . implode('|', $blocked_dates_array). '|';
            $blocked_checkin_dates['dates'] = $updated_blocked_dates_string;
            update_post_meta($_POST['listingId'], '_blocked_checkin_dates', $blocked_checkin_dates);
            
            if (count($current_dates_array) > 0) {
                foreach ($current_dates_array as $key => $value) {
                    if ($value === $_POST['date']) {
                        unset($current_dates_array[$key]);
                    }
                }
                $updated_dates_string = 'Array' . implode('|', $current_dates_array). '|';
                $availability['dates'] = $updated_dates_string;
                update_post_meta($_POST['listingId'], '_availability', $availability);
            }
        }
        delete_ical_entry($_POST['listingId'], $_POST['date']);

        wp_send_json_success(['message' => __('Date successfully unblocked.', 'smoobu-calendar')]);
    } else {
        wp_send_json_error(['message' => 'data missing.']);
    }
    wp_die();
}


add_action('woocommerce_order_status_cancelled', 'remove_entry_calander_and_ical_on_order_cancelled');

function remove_entry_calander_and_ical_on_order_cancelled($order_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'bookings_calendar';

    $start_date = get_post_meta($order_id, 'smoobu_calendar_start', true);
    $end_date = get_post_meta($order_id, 'smoobu_calendar_end', true);

    // Generate all_dates
    $period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), new DateTime($end_date));
    $all_dates = array_map(fn($date) => $date->format('j-m-Y'), iterator_to_array($period));

    // Fetch listing ID
    $listing_id = $wpdb->get_var(
        $wpdb->prepare("SELECT listing_id FROM $table_name WHERE order_id = %d LIMIT 1", $order_id)
    );

    // Get post meta
    $_availability = get_post_meta($listing_id, '_availability', true) ?: [];
    $_external_checkin_dates = get_post_meta($listing_id, '_external_checkin_dates', true) ?: [];
    $_booked_checkin_dates = get_post_meta($listing_id, '_booked_dates', true) ?: [];

    // Convert pipe-separated date strings to arrays
    $parse_dates = function($dates_string) {
        return !empty($dates_string) ? array_filter(explode('|', ltrim($dates_string, 'Array'))) : [];
    };

    $availability_array = $parse_dates($_availability['dates'] ?? '');
    $booked_array = $parse_dates($_booked_checkin_dates['dates'] ?? '');
    $external_array = $parse_dates($_external_checkin_dates['dates'] ?? '');

    // Filter availability: keep dates not in $all_dates or present in $external_array
    $_availability_new = array_values(array_filter(
        $availability_array,
        fn($date) => !(in_array($date, $all_dates) && !in_array($date, $external_array))
    ));

    // Filter booked: remove all matching all_dates
    $_booked_new = array_values(array_diff($booked_array, $all_dates));

    // Prepare new meta values
    $_availability['dates'] = !empty($_availability_new) ? 'Array' . implode('|', $_availability_new) . '|' : 'Array';
    $_booked_checkin_dates['dates'] = !empty($_booked_new) ? 'Array' . implode('|', $_booked_new) . '|' : 'Array';

    // Update post meta
    update_post_meta($listing_id, '_availability', $_availability);
    update_post_meta($listing_id, '_booked_dates', $_booked_checkin_dates);

    // Delete from calendar table
    $wpdb->delete($table_name, ['order_id' => $order_id], ['%d']);

    
}

add_action('wp_footer', function () {
    ?>
    <script>
		jQuery('#iban_radio').on('click',function(e){
			jQuery('.input-box div').hide()
			jQuery('.iban').show();
		})
		jQuery('#paypal_radio').on('click',function(e){
			jQuery('.input-box div').hide()
			jQuery('.paypal_email').show();
		})
		jQuery('#creditcard_radio').on('click',function(e){
			jQuery('.input-box div').hide()
			jQuery('.creditcard_number').show();
		})
			const reqButton = document.getElementById('req_button');
			// Handle custom button click (e.g., #req_button)
        if (reqButton) {
            jQuery('#req_button').on('click', function(e) {
                e.preventDefault();

                const currentUrl = window.location.href;
                const order_number = jQuery('#order_number').val();
                const customer_phone = jQuery('#customer_phone').val();
                const first_name = jQuery('#first_name').val();
                const last_name = jQuery('#last_name').val();
                const get_date_created = jQuery('#order_create_date').val();
                const cunstomer_email = jQuery('#billing_email').val();
                const smoobu_calendar_start = jQuery('#your-datepicker').val();
                const special_req = jQuery('#special_req').val();
                const start_date = jQuery('#start_date').val();
                const end_date = jQuery('#end_date').val();
                const isEnglish = currentUrl.includes('/en/');

                if (!smoobu_calendar_start) {
                    alert(isEnglish ? 'Please select a start date.' : 'Bitte wählen Sie ein Startdatum aus.');
                    return; // stop here, don’t send AJAX
                }
                if (!special_req) {
                    alert(isEnglish ? 'Please Enter a message' : 'Bitte geben Sie eine Nachricht ein');
                    return; // stop here, don’t send AJAX
                }
                
                // ✅ Continue only if date is selected
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'send_custom_form_email',
                        page_url: currentUrl,
                        order_number,
                        customer_phone,
                        first_name,
                        last_name,
                        get_date_created,
                        cunstomer_email,
                        smoobu_calendar_start,
                        special_req,
                        start_date,
                        end_date,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(isEnglish ? 'Enquiry successfully sent!' : 'Anfrage erfolgreich gesendet!');
                    } else {
                        alert(isEnglish ? 'Request failed.' : 'Anforderung fehlgeschlagen.');
                    }
                });
            });
        }
        function bindCustomFormSubmit() {
            const form = document.querySelector('#customer-order-info form.wpcf7-form');

            jQuery('#customer-order-info form.wpcf7-form').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const currentUrl = window.location.href;
                const order_number = jQuery('#order_number').val();
                const customer_phone = jQuery('#customer_phone').val();
                const first_name = jQuery('#first_name').val();
                const last_name = jQuery('#last_name').val();
                const get_date_created = jQuery('#order_create_date').val();
                const cunstomer_email = jQuery('#billing_email').val();

                // ✅ Detect language by URL
                const isEnglish = currentUrl.includes('/en/');

                // ✅ Required field validation
                if (!formData.get('select')) {
                    alert(isEnglish ? 'Please select an option.' : 'Bitte wählen Sie eine Option.');
                    return;
                }

                if (!formData.get('textarea-422')) {
                    alert(isEnglish ? 'Please enter a message.' : 'Bitte geben Sie eine Nachricht ein.');
                    return;
                }

                const selectedOption = formData.get('select');
                if (
                    selectedOption === 'Voucher redemption request' ||
                    selectedOption === 'Antrag auf Gutscheineinlösung'
                ) {
                    if (!formData.get('text-704')) {
                        alert(isEnglish ? 'Please enter the voucher code.' : 'Bitte geben Sie den Gutscheincode ein.');
                        return;
                    }
                }

                // ✅ Continue only if validation passes
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'send_custom_form_email',
                        select: formData.get('select'),
                        message: formData.get('textarea-422'),
                        coupon_req: formData.get('text-704'),
                        page_url: currentUrl,
                        order_number,
                        customer_phone,
                        first_name,
                        last_name,
                        get_date_created,
                        cunstomer_email,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(isEnglish ? 'Successfully submitted!' : 'Erfolgreich übermittelt!');
                        form.reset();
                    } else {
                        alert(isEnglish ? 'Something went wrong.' : 'Etwas ist schief gelaufen.');
                    }
                });
            });
        }


    // If form is added via AJAX, call this in your success handler:
    // Example:
    // success: function(response) {
    //     jQuery('#customer-order-info').html(response);
    //     bindCustomFormSubmit();
    // }
    </script>
    <?php
});

add_action('wp_ajax_nopriv_send_custom_form_email', 'send_custom_form_email');
add_action('wp_ajax_send_custom_form_email', 'send_custom_form_email');

function send_custom_form_email() {
    // Common fields
    $first_name   = sanitize_text_field($_POST['first_name']);
	$last_name   = sanitize_text_field($_POST['last_name']);
	$get_date_created   = sanitize_text_field($_POST['get_date_created']);
    $order_number    = sanitize_text_field($_POST['order_number']);
    $customer_email  = sanitize_email($_POST['cunstomer_email']);
    $customer_phone  = sanitize_text_field($_POST['customer_phone']);
    $page_url        = esc_url_raw($_POST['page_url']);

    // Detect which submission type
    $is_form_submit = isset($_POST['select']) && isset($_POST['message']);
    $is_req_button  = isset($_POST['smoobu_calendar_start']) && isset($_POST['special_req']);

    // Form submission fields
    $subject_field = $is_form_submit ? sanitize_text_field($_POST['select']) : '';
    $message       = $is_form_submit ? sanitize_textarea_field($_POST['message']) : '';
    $coupon_req       = $is_form_submit ? sanitize_textarea_field($_POST['coupon_req']) : '';

    // Req_button submission fields
    $already_start = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $already_end   = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $req_start     = isset($_POST['smoobu_calendar_start']) ? sanitize_text_field($_POST['smoobu_calendar_start']) : '';
    $req_end       = isset($_POST['smoobu_calendar_end']) ? sanitize_text_field($_POST['smoobu_calendar_end']) : '';
    $special_req   = isset($_POST['special_req']) ? sanitize_textarea_field($_POST['special_req']) : '';
	$email_to_send = '';

    // Subject line
    $subject = $is_form_submit 
        ? $subject_field 
        : 'Booking Modification Request';

    // Start HTML email body
    $body = "<div style=\"font-family: 'Arial', sans-serif; max-width: 640px; margin: auto; padding: 20px; background-color: #f9f9f9; color: #000; border: 1px solid #ddd;\">";

    // Header
    $body .= "<h2 style=\"color: #54775E;\">" . ($is_form_submit ? $subject_field : "Booking Modification Request") . "</h2>";
    $body .= "<p><strong>Submitted From:</strong> {$page_url}</p>";

    // Common Info
    $body .= "<p><strong>Order create Date:</strong> {$get_date_created}</p>";
    $body .= "<p><strong>First Name:</strong> {$first_name}</p>";
	$body .= "<p><strong>Last Name:</strong> {$last_name}</p>";
    $body .= "<p><strong>Email:</strong> {$customer_email}</p>";
    $body .= "<p><strong>Order Number:</strong> {$order_number}</p>";
    $body .= "<p><strong>Phone:</strong> {$customer_phone}</p>";

    if ($is_form_submit) {
        $body .= "<hr>";
        $body .= "<p><strong>Subject:</strong> {$subject_field}</p>";
        $body .= "<p><strong>Message:</strong><br>" . nl2br($message) . "</p>";
		if($subject_field == 'Antrag auf Gutscheineinlösung' || $subject_field == 'Voucher redemption request'){
            $body .= "<p><strong>Gutscheincode:</strong><br>" .$coupon_req. "</p>";
			$email_to_send = 'rechnung@bubble-tent.net,anfragen@book-a-bubble.de';
		}else{
			$email_to_send = 'support@book-a-bubble.de,anfragen@book-a-bubble.de';
		}
    }

    if ($is_req_button) {
		$email_to_send = 'support@book-a-bubble.de,anfragen@book-a-bubble.de';
        $body .= "<hr>";
        $body .= "<p><strong>Already Booked Dates:</strong> {$already_start} to {$already_end}</p>";
        $body .= "<p><strong>Requested Dates:</strong> {$req_start}</p>";
        $body .= "<p><strong>Message:</strong><br>" . nl2br($special_req) . "</p>";
    }

    $body .= "</div>";

    // Send the email
    $to = $email_to_send;
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $sent = wp_mail($to, $subject, $body, $headers);

    wp_send_json([
        'success' => $sent ? true : false
    ]);
}

// Enqueue flatpickr and inline JS
add_action('wp_enqueue_scripts', function() {
    // Enqueue flatpickr CSS & JS
    wp_enqueue_style('date-picker', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
	wp_enqueue_script('date-picker-script', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
    //wp_enqueue_script('date-picker-language', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js', [], null, true);

    // Inline JS for date picker
    add_action('wp_footer', function() {
        ?>
        <script>
			(function (global, factory) {
				typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
				typeof define === 'function' && define.amd ? define(['exports'], factory) :
				(global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.de = {}));
			}(this, (function (exports) { 'use strict';

										 var fp = typeof window !== "undefined" && window.flatpickr !== undefined
										 ? window.flatpickr
										 : {
											 l10ns: {},
										 };
										 var German = {
											 weekdays: {
												 shorthand: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
												 longhand: [
													 "Sonntag",
													 "Montag",
													 "Dienstag",
													 "Mittwoch",
													 "Donnerstag",
													 "Freitag",
													 "Samstag",
												 ],
											 },
											 months: {
												 shorthand: [
													 "Jan",
													 "Feb",
													 "Mär",
													 "Apr",
													 "Mai",
													 "Jun",
													 "Jul",
													 "Aug",
													 "Sep",
													 "Okt",
													 "Nov",
													 "Dez",
												 ],
												 longhand: [
													 "Januar",
													 "Februar",
													 "März",
													 "April",
													 "Mai",
													 "Juni",
													 "Juli",
													 "August",
													 "September",
													 "Oktober",
													 "November",
													 "Dezember",
												 ],
											 },
											 firstDayOfWeek: 1,
											 weekAbbreviation: "KW",
											 rangeSeparator: " bis ",
											 scrollTitle: "Zum Ändern scrollen",
											 toggleTitle: "Zum Umschalten klicken",
											 time_24hr: true,
										 };
										 fp.l10ns.de = German;
										 var de = fp.l10ns;

										 exports.German = German;
										 exports.default = de;

										 Object.defineProperty(exports, '__esModule', { value: true });

										})));
			jQuery(document).ready(function($) {
				const $input = $('#your-datepicker');
				const listingId = $('#your-listing-wrapper').data('listing-id');
                const currentUrl = window.location.href;
                const isEnglish = currentUrl.includes('/en/');
				if (!$input.length || !listingId) return;

				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					type: 'GET',
					data: {
						action: 'get_busy_dates',
						listing_id: listingId
					},
					success: function(response) {
						if (!response.success || !response.data) return;

						const unavailableDates = response.data.available_dates || [];

						const unavailableSet = new Set(unavailableDates);
						var maxDate = response.data.max_date || null;
						console.log(unavailableDates)
						flatpickr.localize(flatpickr.l10ns.de);
						const fp = $input.flatpickr({
							locale: 'de',
							mode: 'range',
							dateFormat: 'Y-m-d',
							maxDate: maxDate,
							minDate: 'today',
							showMonths: 1,
							disable: unavailableDates,
							onChange: function(selectedDates, dateStr, instance) {
								$input.off('click').on('click', function () {
									instance.clear();
									instance.open();
								});

								if (selectedDates.length === 1) {
									const start = selectedDates[0];
									const next = new Date(start);
									next.setDate(next.getDate() + 1);

									const y = next.getFullYear();
									const m = String(next.getMonth() + 1).padStart(2, '0');
									const d = String(next.getDate()).padStart(2, '0');
									const nextFormatted = `${y}-${m}-${d}`;

									// If the next date is unavailable
									if (unavailableSet.has(nextFormatted)) {
										// Format both dates to d-m-Y
										const sDay = String(start.getDate()).padStart(2, '0');
										const sMonth = String(start.getMonth() + 1).padStart(2, '0');
										const sYear = start.getFullYear();
										const startFormattedDMY = `${sDay}-${sMonth}-${sYear}`;

										const nDay = String(next.getDate()).padStart(2, '0');
										const nMonth = String(next.getMonth() + 1).padStart(2, '0');
										const nYear = next.getFullYear();
										const nextFormattedDMY = `${nDay}-${nMonth}-${nYear}`;

										// Set both dates as range (start → start+1)
										instance.setDate([start, next]);
										$input.val(`${startFormattedDMY} bis ${nextFormattedDMY}`);
										instance.close();
									}
								}

								if (selectedDates.length === 2) {
									const start = selectedDates[0];
									const end = selectedDates[1];
									let current = new Date(start);
									let isValid = true;

									while (current <= end) {
										const y = current.getFullYear();
										const m = String(current.getMonth() + 1).padStart(2, '0');
										const d = String(current.getDate()).padStart(2, '0');
										const formatted = `${y}-${m}-${d}`;

										if (unavailableSet.has(formatted)) {
											isValid = false;
											break;
										}

										current.setDate(current.getDate() + 1);
									}
									if (!isValid) {
                                        alert(isEnglish ? 'The selected period contains unavailable dates. Please select a valid period.' : 'Der ausgewählte Zeitraum enthält nicht verfügbare Termine. Bitte wählen Sie einen gültigen Zeitraum.');
										instance.clear();
									} else {
										const sDay = String(start.getDate()).padStart(2, '0');
										const sMonth = String(start.getMonth() + 1).padStart(2, '0');
										const sYear = start.getFullYear();
										const startFormatted = `${sDay}-${sMonth}-${sYear}`;

										const eDay = String(end.getDate()).padStart(2, '0');
										const eMonth = String(end.getMonth() + 1).padStart(2, '0');
										const eYear = end.getFullYear();
										const endFormatted = `${eDay}-${eMonth}-${eYear}`;

										$input.val(`${startFormatted} bis ${endFormatted}`);
									}
								}
							}

						});
					}
				});
			});



        </script>
        <?php
    }, 100);
});

add_action('wp_ajax_get_busy_dates', 'get_listing_available_dates');
add_action('wp_ajax_nopriv_get_busy_dates', 'get_listing_available_dates');

function get_listing_available_dates() {
    global $wpdb;

    $listing_id = intval($_GET['listing_id'] ?? 0);
    if (!$listing_id) {
        wp_send_json_error('Missing listing ID');
    }
	
	$product_id = get_post_meta($listing_id , 'product_id', true );
	$property_id = get_post_meta($product_id , 'custom_property_id_field', true );

    // Replace this with your actual available dates table and columns
    $table_name = $wpdb->prefix . 'smoobu_calendar_availability';
    $results = $wpdb->get_col($wpdb->prepare(
        "SELECT busy_dates FROM $table_name WHERE property_id = %d ORDER BY busy_dates ASC",
        $property_id
    ));
	
	if ($results) {
		$available_dates = json_decode($results[0], true); // Convert JSON string to array

		// Sort and get max date
		$max_date = !empty($available_dates) ? max($available_dates) : null;

		wp_send_json_success([
			'available_dates' => $available_dates,
			'max_date' => $max_date
		]);
	} else {
		wp_send_json_error('No dates found');
	}
}

add_action( 'woocommerce_before_checkout_form', 'remove_checkout_login_form', 4 );
function remove_checkout_login_form(){
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
}

add_filter('woocommerce_email_enabled_customer_on_hold_order', 'disable_yaymail_for_arrival', 10, 2);
add_filter('woocommerce_email_enabled_customer_processing_order', 'disable_yaymail_for_arrival', 10, 2);
add_filter('woocommerce_email_enabled_customer_completed_order', 'disable_yaymail_for_arrival', 10, 2);

function disable_yaymail_for_arrival($enabled, $order) {
    if (!$order instanceof WC_Order) return $enabled;
    $arrival_date = get_post_meta($order->get_id(), 'smoobu_calendar_start', true);
    return $arrival_date ? false : $enabled;
}


add_action('woocommerce_order_status_on-hold', 'send_custom_arrival_email', 20);
add_action('woocommerce_order_status_processing', 'send_custom_arrival_email', 20);
add_action('woocommerce_order_status_completed', 'send_custom_arrival_email', 20);

function send_custom_arrival_email($order_id , $from_reminder = false) {
    $order = wc_get_order($order_id);
    if (!$order) return;
	$has_listing_booking = false;

    if ( get_post_meta($order_id, '_created_via', true) == 'dokan' ) {
        return;
    }


    $arrival_date = get_post_meta($order_id, 'smoobu_calendar_start', true);

    $first_name = $order->get_billing_first_name();
    $last_name  = $order->get_billing_last_name();
    $user_name  = $first_name . ' ' . $last_name;
    $order_no   = $order->get_order_number();
    $vendor_id  = get_post_meta($order_id, '_dokan_vendor_id', true);
    $vendor     = get_user_by('id', $vendor_id);
    $vendor_email = isset($vendor->user_email) ? $vendor->user_email : '';
    $departure_date = get_post_meta($order_id, 'smoobu_calendar_end', true);
    $checkin_time   = get_post_meta($order_id, 'smoobu_checkin_time', true);
    $adults         = get_post_meta($order_id, '_number_of_adults', true) ?: 0;
    $kids           = get_post_meta($order_id, '_number_of_kids', true) ?: 0;
    $guest_count    = $adults + $kids;
    $order_price    = $order->get_total();
    $payment_method = $order->get_payment_method_title();
    $order_status   = $order->get_status();

    $arrival_fmt    = date_i18n('d.m.Y', strtotime($arrival_date));
    $departure_fmt  = date_i18n('d.m.Y', strtotime($departure_date));
    $nights         = (strtotime($departure_date) - strtotime($arrival_date)) / (60 * 60 * 24);
    $order_url      = $order->get_view_order_url();
    $payment_pending = strtolower($payment_method) === 'vorkasse' || strtolower($payment_method) === 'bank transfer';

    // Check for random password
    $rand_password = get_post_meta($order_id, '_random_password', true);
    if (empty($rand_password)) {
        $rand_password = wp_generate_password(10, false);
        update_post_meta($order_id, '_random_password', $rand_password);
    }
    $order_lang = get_post_meta($order_id, 'wpml_language', true);
    $gutscheincode =  get_order_coupon_code($order_id);
    $cats = get_order_product_categories($order_id);
    $products = get_order_products_details($order_id);
    $excluded_product_ids = [222556,222557,222558];
    $contains_excluded_product = false;

    foreach ($order->get_items() as $item) {
    $pid    = $item->get_product_id();
    $parent = wp_get_post_parent_id($pid);

    if (in_array((int)$pid, $excluded_product_ids, true) ||
        in_array((int)$parent, $excluded_product_ids, true)) {
        $contains_excluded_product = true;
        break;
    }


    }
    $enable_cart_upsell = get_option('christmas_enable_cart_upsell', 'no');
    ob_start();
	$template_path = get_stylesheet_directory() . '/email-template/';
    if ($from_reminder) {
        // Reminder templates
        if ($order_lang === 'en') {
            include $template_path . 'reminder-mail-en.php';
        } else {
            include $template_path . 'reminder-mail-de.php';
        }
    } else {
        // Normal email templates
        if ($contains_excluded_product) {
            if ( $contains_excluded_product && $order_status != 'completed' ) {
                return;
            }
            if ($enable_cart_upsell === 'yes') {
                include $template_path . ($order_lang === 'en'
                ? 'voucher-mail-en.php'
                : 'voucher-mail.php');
            }else{
                include $template_path . ($order_lang === 'en'
               ? 'christmas-email-en.php'
               : 'christmas-email.php');
            }
        } else {
            if ($arrival_date) {
                include $template_path . ($order_lang === 'en'
                    ? 'mail-en.php'
                    : 'mail.php');
            } else {
                include $template_path . ($order_lang === 'en'
                    ? 'voucher-mail-en.php'
                    : 'voucher-mail.php');
            }
        }
    }
    $message = ob_get_clean();

    if ($from_reminder) {
        // Reminder templates
        if ($order_lang === 'en') {
            $subject = 'Thank you for booking with Book a Bubble!';
        } else {
            $subject = 'Vielen Dank für deine Buchung bei Book a Bubble!';
        }
    } else {
        // Normal email templates
        if ($contains_excluded_product) {
            $subject = $order_lang === 'en' ? 'Enjoy your voucher!' : 'Viel Freude mit deinem Gutschein!';
        } else {
            if ($arrival_date) {
                if ($order_lang === 'en') {
                    $subject = 'Thank you for booking with Book a Bubble!';
                } else {
                    $subject = 'Vielen Dank für deine Buchung bei Book a Bubble!';
                }
            } else {
                $subject = $order_lang === 'en' ? 'Enjoy your voucher!' : 'Viel Freude mit deinem Gutschein!';
            }
        }
    }

    $to      = $order->get_billing_email();
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($to, $subject, $message, $headers);
}


function get_coupon_pdf_url_from_code( $coupon_code ) {
    global $wpdb;

    // 1. Find coupon post ID by code
    $coupon_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = 'shop_coupon' 
         AND post_title = %s 
         LIMIT 1",
        $coupon_code
    ));

    if ( ! $coupon_id ) {
        return false;
    }

    // 2. Get serialized data
    $coupon_data = get_post_meta( $coupon_id, '_fcpdf_coupon_data', true );

    if ( ! $coupon_data || ! is_array( $coupon_data ) ) {
        return false;
    }

    // 3. Return coupon_url if available
    return isset( $coupon_data['coupon_url'] ) ? $coupon_data['coupon_url'] : false;
}

function get_order_products_details($order_id) {
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return false;
    }
    
    $products = array();
    
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $products[] = array(
            'name' => $product ? $product->get_name() : $item->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
            'product_id' => $item->get_product_id(),
            'variation_id' => $item->get_variation_id()
        );
    }
    
    return $products;
}
function get_order_coupon_code($order_id) {
    global $wpdb;

    $meta_key = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_key 
            FROM {$wpdb->postmeta} 
            WHERE post_id = %d AND meta_key LIKE %s 
            LIMIT 1",
            $order_id,
            '%_coupon_code'
        )
    );

    if ($meta_key) {
        return get_post_meta($order_id, $meta_key, true);
    }

    return false;
}

function get_order_product_categories($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return [];

    $categories = [];

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product) {
            // If variation, get parent product
            $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

            // WPML: get product in default language
            if (function_exists('wpml_object_id')) {
                $default_lang = apply_filters('wpml_default_language', null);
                $original_product_id = apply_filters('wpml_object_id', $product_id, 'product', false, $default_lang);
            } else {
                $original_product_id = $product_id;
            }

            // Get categories
            $terms = get_the_terms($original_product_id, 'product_cat');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[] = $term->name;
                }
            }
        }
    }

    return array_unique($categories); // remove duplicates
}

add_action('admin_footer', function() {
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>';
});




// Admin menu page
add_action('admin_menu', function() {
    add_menu_page(
        'Order Status Email Tool',
        'Order Status Email Tool',
        'manage_woocommerce',
        'order-status-email-tool',
        'render_order_status_email_tool',
        'dashicons-email-alt2',
        56
    );
});

function render_order_status_email_tool() {
    ?>
    <div class="wrap">
        <h1>Order Status Email Tool</h1>
        <form method="post">
            <?php wp_nonce_field('fetch_orders_action', 'fetch_orders_nonce'); ?>
            
            <label for="listing_select">Select Location:</label>
            <select name="listing_id" id="listing_select">
                <option value="">Select Location</option>
                <?php
                $args = array(
                    'post_type'      => 'listing',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'suppress_filters' => false,
                );
                $listings = get_posts($args);

                foreach ($listings as $listing) {
                    // Only process original listing
                    $original_listing_id = apply_filters('wpml_object_id', $listing->ID, 'listing', false, wpml_get_default_language());
                    if ($original_listing_id !== $listing->ID) {
                        continue; // Skip duplicates (translations)
                    }

                    // Get all translations of the listing
                    $translated_listing_ids = apply_filters('wpml_get_element_translations', null, apply_filters('wpml_element_trid', null, $listing->ID, 'post_listing'), 'post_listing');
                    $listing_ids = array_keys($translated_listing_ids);

                    printf(
                        '<option value="%d">%s</option>',
                        esc_attr($original_listing_id),
                        esc_html(get_the_title($listing->ID))
                    );
                }
                ?>
            </select>
            
            <br><br>
            <label for="order_number">Order Number:</label>
            <input type="number" name="order_number">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date">
            <input type="submit" name="fetch_orders" class="button button-primary" value="Fetch Orders">
        </form>
        <?php
        if (isset($_POST['fetch_orders']) && check_admin_referer('fetch_orders_action', 'fetch_orders_nonce')) {
            $start = sanitize_text_field($_POST['start_date']);
            $end   = sanitize_text_field($_POST['end_date']);
            $product_id = intval($_POST['listing_id']);

            $product_ids = get_listing_translated_products($product_id);
            $order_id_single    = intval($_POST['order_number']);
            // If order number is provided, fetch only that order
            if (!empty($order_id_single)) {
                fetch_and_display_orders('', '', array(), $order_id_single);
            } else {
                $product_ids = get_listing_translated_products($product_id);
                fetch_and_display_orders($start, $end, $product_ids);
            }
        }
        ?>
    </div>
    <?php
}

// Get all translated product IDs from original product ID
function get_listing_translated_products($original_product_id) {
    $translations = apply_filters('wpml_get_element_translations', null, apply_filters('wpml_element_trid', null, $original_product_id, 'post_product'), 'post_product');

    $ids = [];
    if (!empty($translations) && is_array($translations)) {
        foreach ($translations as $t) {
            if (!empty($t->element_id)) {
                $ids[] = $t->element_id;
            }
        }
    }

    // Always include the original product ID
    if (!in_array($original_product_id, $ids)) {
        $ids[] = $original_product_id;
    }

    return $ids;
}


function fetch_and_display_orders($start_date, $end_date, $product_ids = array() , $order_id_single = null) {
    global $wpdb;
    if($order_id_single){
        echo "<table class='widefat striped'>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Email</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Create date</th>
                <th>Arrival Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";
        $order = wc_get_order($order_id_single);
        if ($order){
            $order_email    = $order->get_billing_email();
            $status   = wc_get_order_status_name($order->get_status());
            $arrival_raw  = trim($order->get_meta('smoobu_calendar_start'));
            $create_date  = $order->get_date_created();
            $create_date  = $create_date->date('d-m-Y');
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            $user_name = $first_name . ' ' . $last_name;
            // Normalize the date string
            $arrival_clean = str_replace(['/', '.'], '-', $arrival_raw);
            $arrival_parts = explode('-', $arrival_clean);

            if (count($arrival_parts) === 3) {
                // Guess format
                if (strlen($arrival_parts[0]) === 4) {
                    // Y-m-d
                    $arrival_ts = strtotime($arrival_clean);
                } else {
                    // d-m-Y
                    $arrival_ts = strtotime($arrival_parts[2] . '-' . $arrival_parts[1] . '-' . $arrival_parts[0]);
                }
            } else {
                $arrival_ts = strtotime($arrival_raw); // fallback
            }
            echo "<tr>
                <td>{$order_id_single}</td>
                <td>{$order_email}</td>
                <td>{$user_name}</td>
                <td>{$status}</td>
                <td>{$create_date}</td>
                <td>" . esc_html($arrival_raw) . "</td>
                <td><a href='#' class='button resend-email' data-order-id='{$order_id_single}'>Resend Email</a></td>
            </tr>";
        }else{
            echo 'No order found for the enter id';
        }
        echo "</tbody></table>";
    }else if( !empty($start_date) && !empty($end_date)){
        $today_ts = strtotime(date('Y-m-d'));
        $listing_ids = $product_ids;
        $product_ids_get = array();
        if (!empty($listing_ids) && is_array($listing_ids)) {
            foreach ($listing_ids as $listing_id) {
                $product_id = get_post_meta($listing_id, 'product_id', true);

                if (!empty($product_id)) {
                    $product_ids_get[] = $product_id;
                }
            }
        }
        $product_ids = $product_ids_get;
        $statuses = array('wc-processing', 'wc-on-hold', 'wc-completed');

        $start_dt = date('Y-m-d H:i:s', strtotime($start_date));
        $end_dt   = date('Y-m-d H:i:s', strtotime($end_date . ' 23:59:59'));

        $orders_with_products = array();

        if (!empty($product_ids)) {
            // Get orders that contain specific product IDs
            $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
            $query = $wpdb->prepare("
                SELECT DISTINCT o.ID
                FROM {$wpdb->prefix}posts o
                INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON o.ID = oi.order_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                WHERE o.post_type = 'shop_order'
                AND o.post_status IN ('" . implode("','", $statuses) . "')
                AND o.post_date BETWEEN %s AND %s
                AND oim.meta_key = '_product_id'
                AND oim.meta_value IN ($placeholders)
            ", array_merge(array($start_dt, $end_dt), $product_ids));

            $orders_with_products = $wpdb->get_col($query);
        }

        if (!empty($orders_with_products)) {
            $order_ids = $orders_with_products;
        } else {
            // fallback only by date range + status
            $query = $wpdb->prepare("
                SELECT ID
                FROM {$wpdb->prefix}posts
                WHERE post_type = 'shop_order'
                AND post_status IN ('" . implode("','", $statuses) . "')
                AND post_date BETWEEN %s AND %s
            ", $start_dt, $end_dt);

            $order_ids = $wpdb->get_col($query);
        }

        if (empty($order_ids)) {
            echo "<p>No orders found.</p>";
            return;
        }

        echo "<table class='widefat striped'>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Email</th>
                    <th>Customer Name</th>
                    <th>Status</th>
                    <th>Create date</th>
                    <th>Arrival Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;
            $order_email    = $order->get_billing_email();
            $status   = wc_get_order_status_name($order->get_status());
            $arrival_raw  = trim($order->get_meta('smoobu_calendar_start'));
            $create_date  = $order->get_date_created();
            $create_date  = $create_date->date('d-m-Y');
            $first_name = $order->get_billing_first_name();
            $last_name = $order->get_billing_last_name();
            $user_name = $first_name . ' ' . $last_name;

            if (!$arrival_raw) continue;

            // Normalize the date string
            $arrival_clean = str_replace(['/', '.'], '-', $arrival_raw);
            $arrival_parts = explode('-', $arrival_clean);

            if (count($arrival_parts) === 3) {
                // Guess format
                if (strlen($arrival_parts[0]) === 4) {
                    // Y-m-d
                    $arrival_ts = strtotime($arrival_clean);
                } else {
                    // d-m-Y
                    $arrival_ts = strtotime($arrival_parts[2] . '-' . $arrival_parts[1] . '-' . $arrival_parts[0]);
                }
            } else {
                $arrival_ts = strtotime($arrival_raw); // fallback
            }

            // If parsing failed or arrival date < today, skip
            if ($arrival_ts === false || $arrival_ts < $today_ts) continue;

            $order_ids[] = $order_id;

            echo "<tr>
                <td>{$order_id}</td>
                <td>{$order_email}</td>
                <td>{$user_name}</td>
                <td>{$status}</td>
                <td>{$create_date}</td>
                <td>" . esc_html($arrival_raw) . "</td>
                <td><a href='#' class='button resend-email' data-order-id='{$order_id}'>Resend Email</a></td>
            </tr>";
        }

        echo "</tbody></table>";

        // Batch send button
        if (!empty($order_ids)) {
            $nonce = wp_create_nonce('send_all_emails_ajax');
            echo "<br><div id='progress-container'><div id='progress-bar'>0%</div></div>";
            echo "<button id='send-all-emails' class='button button-primary' data-nonce='{$nonce}' data-orders='" . esc_attr(json_encode($order_ids)) . "'>Send All Emails in Batches</button>";
        }
    }else{
        echo "Please select date or enter order id";
    }
}


add_action('wp_ajax_send_all_emails', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'send_all_emails_ajax')) {
        wp_send_json_error(['message' => 'Invalid security token.']);
    }

    if (empty($_POST['order_ids']) || !is_array($_POST['order_ids'])) {
        wp_send_json_error(['message' => 'No orders received.']);
    }

    $order_ids = array_map('intval', $_POST['order_ids']);
    $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
    $batch_size = 50;

    $batch_orders = array_slice($order_ids, $batch * $batch_size, $batch_size);
    $sent_count = 0;

    foreach ($batch_orders as $order_id) {
        if (wc_get_order($order_id)) {
            send_custom_arrival_email($order_id); 
            $sent_count++;
        }
    }

    $has_more = (($batch + 1) * $batch_size) < count($order_ids);
    wp_send_json_success([
        'message'   => "Batch {$batch} - {$sent_count} emails sent.",
        'has_more'  => $has_more,
        'processed' => ($batch + 1) * $batch_size > count($order_ids) ? count($order_ids) : ($batch + 1) * $batch_size,
        'total'     => count($order_ids)
    ]);
});

// Resend single email
add_action('wp_ajax_resend_order_email', function () {
    if (!isset($_POST['order_id']) || !check_ajax_referer('resend_email_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid request');
    }

    $order_id = intval($_POST['order_id']);

    if (wc_get_order($order_id)) {
        send_custom_arrival_email($order_id);
        wp_send_json_success('Email sent');
    } else {
        wp_send_json_error('Order not found');
    }
});

// Admin footer JS
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'order-status-email-tool') {
        ?>
        <style>
            #progress-container {
                width: 100%;
                background: #eee;
                margin-top: 15px;
                display: none;
                margin-bottom:10px;
            }
            #progress-bar {
                width: 0%;
                height: 20px;
                background: #0073aa;
                color: #fff;
                text-align: center;
                line-height: 20px;
                font-size: 12px;
            }
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Batch send emails
            $('#send-all-emails').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let nonce = button.data('nonce');
                let orders = button.data('orders');
                let total = orders.length;
                let batch = 0;

                // Show progress bar
                $('#progress-container').show();
                $('#progress-bar').css('width', '0%').text('0%');

                button.prop('disabled', true).text('Sending batch 1...');

                function sendBatch() {
                    $.post(ajaxurl, {
                        action: 'send_all_emails',
                        nonce: nonce,
                        order_ids: orders,
                        batch: batch
                    }, function(response) {
                        if (response.success) {
                            console.log(response.data.message);

                            // Update progress
                            let processed = response.data.processed;
                            let percent = Math.round((processed / total) * 100);
                            $('#progress-bar').css('width', percent + '%').text(percent + '%');

                            if (response.data.has_more) {
                                batch++;
                                button.text('Sending batch ' + (batch + 1) + '...');
                                sendBatch();
                            } else {
                                $('#progress-bar').css('width', '100%').text('100%');
                                alert('All emails sent successfully!');
                                button.prop('disabled', false).text('Send All Emails in Batches');
                            }
                        } else {
                            alert('Error: ' + response.data.message);
                            button.prop('disabled', false).text('Send All Emails in Batches');
                        }
                    });
                }

                sendBatch();
            });

            // Resend single email
            $('.resend-email').on('click', function(e) {
                e.preventDefault();
                let button = $(this);
                let orderId = button.data('order-id');
                button.text('Sending...');

                $.post(ajaxurl, {
                    action: 'resend_order_email',
                    order_id: orderId,
                    nonce: '<?php echo wp_create_nonce("resend_email_nonce"); ?>'
                }, function(response) {
                    alert(response.data || 'Something went wrong');
                    button.text('Resend Email');
                });
            });
        });
        </script>
        <?php
    }
});

// Register a custom interval for cron
add_filter( 'cron_schedules', function( $schedules ) {
    if ( ! isset( $schedules['two_hours'] ) ) {
        $schedules['two_hours'] = array(
            'interval' => 2 * 3600,
            'display'  => __( 'Every 2 Hours' ),
        );
    }
    return $schedules;
});




/*********************************************************************************/

// Loaction page template Popup shortcodes
function smoobu_booking_shortcode($atts) {
    global $post;

    // Shortcode attributes (optional overrides)
    $atts = shortcode_atts([
    'product_id' => get_post_meta($post->ID, 'product_id', true),
    ], $atts, 'smoobu_booking');

    $woo_product_id = intval($atts['product_id']);
    
    // Get WPML translated product ID if WPML is active
    if (function_exists('icl_object_id') && function_exists('icl_get_language_code') && !empty($woo_product_id)) {
        $current_language = icl_get_language_code();
        
        // Get the translated product ID for current language
        $translated_product_id = apply_filters('wpml_object_id', $woo_product_id, 'product', false, $current_language);
        
        // Use translated ID if it exists, otherwise keep original
        if ($translated_product_id) {
            $woo_product_id = intval($translated_product_id);
        }
    }
    $listing_product = wc_get_product($woo_product_id);

    if (!empty($listing_product)) {
        $property_id = $listing_product->get_meta('custom_property_id_field');
        $base_price = get_post_meta($woo_product_id, 'sa_cfw_cog_amount', true);
    } else {
        $property_id = 0;
        $base_price = 0;
    }

    // Get WPML language
    if (function_exists('icl_get_language_code')) {
        $current_language = icl_get_language_code();
    } else {
        $current_language = '';
    }

    // Construct base URL
    $base_url = get_home_url(null);
    if ($current_language !== 'de' && !empty($current_language)) {
        $base_url .= '/' . $current_language;
    }

    // Disable booking for some listings
    //$disableLocIDs = [149074, 149392, 149393, 149391, 149070, 149390, 149076, 149443, 149442, 149078, 149448, 149447, 145731, 146191, 146190];
    $disableLocIDs = [];
    ob_start();
    ?>

    <div class="elementor-element elementor-element-61a8cc2 elementor-absolute elementor-view-default elementor-widget elementor-widget-icon" data-id="61a8cc2" data-element_type="widget" id="calendar_popup_close" data-settings="{&quot;_position&quot;:&quot;absolute&quot;}" data-widget_type="icon.default" produkt-id="<?php echo $woo_product_id; ?>">
        <div class="elementor-widget-container">
        <div class="elementor-icon-wrapper">
            <div class="elementor-icon">
                <svg aria-hidden="true" class="e-font-icon-svg e-fas-window-close" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"></path></svg></div>
            </div>
        </div>
    </div>

    <div class="sidebarnewmodeule">
        <?php if ( !in_array($post->ID, $disableLocIDs) ) : ?>
            <?php if ($property_id): ?>
            <?php
            $checkout_page_id = get_option('woocommerce_checkout_page_id');
            echo do_shortcode("[smoobu_calendar property_id='$property_id' layout='1x3' link='"
            . esc_url($base_url)
            . "?buy-now=$woo_product_id&qty=1&coupon=&ship-via=free_shipping&page=$checkout_page_id&with-cart=0&prices=$base_price']");
            ?>
        <?php else: ?>
        <p>
            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModa22"
            class="button book-now fullwidth margin-top-5 hash-custom-book-id">
                <span class="book-now-text"><?php esc_html_e('Jetzt buchen', 'listeo_core'); ?></span>
            </a>
        </p>
        <?php endif; ?>
        <?php else : ?>
            <?php
            $currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
            $formatted_price = sprintf(__('From %1$s%2$s / Night', 'smoobu-calendar'), $base_price, $currency_symbol);
            ?>
            <div class="smoobu-price-display-container"><?php echo $formatted_price; ?></div>
            <h4 class="text-center">
                <?php echo __('For bookings please contact us at: <a href="mailto:contact@reserve-ta-bulle.fr">contact@reserve-ta-bulle.fr</a>', 'listeo_core'); ?>
            </h4>
            <style>.container .nwesidedetail .pricelisting {display: none;}</style>
        <?php endif; ?>
    </div>


<script>
jQuery(document).ready(function ($) {
    $(".smoobu-calendar-button-container").on("click", function () {
        if ($(".smoobu-calendar").val() === "") {
            // Use jQuery to access shadow DOM safely
            var shadowHost = $(".easepick-wrapper").get(0);

            if (shadowHost && shadowHost.shadowRoot) {
                var $myDiv = $(shadowHost.shadowRoot).find("div").first();

            $myDiv.css({
                "z-index": "100",
                "top": "40px",
                "left": "-184.778px"
                }).addClass("show");
            }
        }
    });
});
</script>
<?php



return ob_get_clean();
}
add_shortcode('smoobu_booking', 'smoobu_booking_shortcode');

add_shortcode('mwew_product_popup', function ($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts);

    $product_id = intval($atts['id']);
    
    // Get WPML translated product ID if WPML is active
    if (function_exists('icl_object_id') && function_exists('icl_get_language_code') && !empty($product_id)) {
        $current_language = icl_get_language_code();
        
        // Get the translated product ID for current language
        $translated_product_id = apply_filters('wpml_object_id', $product_id, 'product', false, $current_language);
        
        // Use translated ID if it exists, otherwise keep original
        if ($translated_product_id) {
            $product_id = intval($translated_product_id);
        }
    }

    if (!$product_id) return '<p>No product ID provided.</p>';

    $product = wc_get_product($product_id);
    if (!$product) return '<p>Invalid product.</p>';

    $title       = $product->get_name();
    $price_html  = $product->get_price_html();
    $description = $product->get_description();
    $featured_img_url = wp_get_attachment_url($product->get_image_id());

    ob_start();
    
    ?>
    <div class="elementor-element elementor-element-61a8cc2 elementor-absolute elementor-view-default elementor-widget elementor-widget-icon" data-id="61a8cc2" data-element_type="widget" id="voucher_popup_close" data-settings="{&quot;_position&quot;:&quot;absolute&quot;}" data-widget_type="icon.default" vouher-id="<?php echo $product_id; ?>">
        <div class="elementor-widget-container">
            <div class="elementor-icon-wrapper">
                <div class="elementor-icon">
                    <svg aria-hidden="true" class="e-font-icon-svg e-fas-window-close" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div id="mwew-popup-<?php echo esc_attr($product_id); ?>" class="custom-product-popup" style="display:flex; gap:30px;">
        <div class="popup-image" style="flex:1; min-width:200px;max-width: 430px;">
            <?php if ($featured_img_url): ?>
            <div style="background-image: url(<?php echo esc_url($featured_img_url); ?>); width:100%; height: 100%; border-radius: 8px; background-size: cover; background-position: center;"></div>
        <?php endif; ?>
        </div>



        <div class="popup-details" style="flex:1;">
            <h2 class="popup-title" style="font-size:24px; margin-bottom:10px;"><?php echo esc_html($title); ?></h2>
            <div class="popup-price" style="font-weight:bold; font-size:18px; color:#3D6B50; margin-bottom:15px;">
                <?php echo wp_kses_post($price_html); ?>
            </div>

        <?php
        // 🔥 Force global product for WooCommerce templates
        global $product;
        $backup_product = $product;
        $product = wc_get_product($product_id);

        woocommerce_template_single_add_to_cart();

        // Restore global product
        $product = $backup_product;
        ?>
        </div>
    </div>
	<style>
		.quantity,.wgm-info.woocommerce_de_versandkosten{
			display:none !important;
		}
		.single_variation {
			padding:10px 16px;
		}
	</style>
<?php if (is_page_template('template-location-page.php')) { ?>
<?php } ?>
<?php



    return ob_get_clean();
});



// ✅ Load WooCommerce variation scripts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('wc-add-to-cart-variation');
});


function my_remove_cashier_side_cart_script() {
    if ( is_page_template( 'template-location-page.php' ) || is_page( 237948 ) || is_page( 238004 )) {
        wp_dequeue_script( 'sa-cfw-sidecart' );
        wp_deregister_script( 'sa-cfw-sidecart' );
    }
}
add_action( 'wp_enqueue_scripts', 'my_remove_cashier_side_cart_script', 100 );