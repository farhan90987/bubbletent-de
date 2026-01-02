<?php
/*
 * Template Name: Order Detail Page
 */
get_header();

$user_id = get_current_user_id();
$parent_vendor_id = get_user_meta($current_user->ID, 'parent_vendor_id', true);

if (!empty($parent_vendor_id)) {
    $user_id = $parent_vendor_id;
}
?>
<link rel='stylesheet' id='smoobu-calendar-css-easepick-css'
    href='https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/css/index.css?ver=1.2.2'
    type='text/css' media='all' />
<link rel='stylesheet' id='smoobu-calendar-easepick-hotel-css'
    href='https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/css/hotel-example.css?ver=1.2.2'
    type='text/css' media='all' />
<link rel='stylesheet' id='smoobu-calendar-css-main-css'
    href='https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/css/main.min.css?ver=1.2.2'
    type='text/css' media='all' />
<link rel='stylesheet' id='smoobu-calendar-checkout-css-css'
    href='https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/css/main-checkout.min.css?ver=1.2.2'
    type='text/css' media='all' />
<link rel='stylesheet' id='smoobu-calendar-css-theme-default-css'
    href='https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/css/default/theme.css?ver=1.2.2'
    type='text/css' media='all' />

<section class="order--login-wrap">
    <div class="container page--container">
        <div class="login--info-wrap" id="order-login">
			<h2 style="text-align:center;color:#fff">
				Loading...
			</h2>
            <div class="request-change" style="display:none;">
                <div class="col">
                    <h1 class="form--title"><?php esc_html_e('Bitte gebe die Bestellnummer und das Passwort aus der E-Mail ein', 'smoobu-calendar'); ?></h1>
                </div>
                <div class="col">
                    <form class="order--page-form" id="order-form">
                        <div class="form--group">
                            <label for="order-number"><?php esc_html_e('Order number', 'smoobu-calendar'); ?></label>
                            <input type="number" id="order-number" name="order-number" placeholder="<?php esc_html_e('Order number', 'smoobu-calendar'); ?>"
                                required>
                        </div>
                        <div class="form--group">
                            <label for="user-password"><?php esc_html_e('Password', 'smoobu-calendar'); ?></label>
                            <input type="password" name="user-password" id="user_password" placeholder="<?php esc_html_e('Password', 'smoobu-calendar'); ?>"
                                required>
                        </div>
                        <div class="btn--wrap">
                            <button type="submit" class="input--link white-text"><?php esc_html_e('Jetzt Buchung ansehen', 'smoobu-calendar'); ?></button>
                            <div class="ajax-loading">
                                <div class="loading-gif">
                                    <!-- <img src="/wp-content/uploads/2024/12/ajax-loading.gif" alt=""> -->
                                    <img src="/wp-content/uploads/2024/12/loading-gif-new.webp" alt="">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="order--details-wrap">
            <div id="order-main">
<!--                 <div  class="go-back">
                    <button>
                        <i class="fas fa-long-arrow-alt-left"></i>
                        <?php // esc_html_e('Go Back', 'smoobu-calendar'); ?>
                    </button>
                </div> -->
                <?php
                    if (isset($_GET['order_id'])) {
                        $order_id = intval($_GET['order_id']);
                        $order = wc_get_order($order_id);
                        if ($order) {
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
                                        ?>
                                    <style>
                                        .gallery {
                                            display: flex;
                                            gap: 1rem;
                                            max-width: 100%;
                                            margin: auto;
                                        }

                                        .gallery img {
                                            width: 100%;
                                            height: 100%;
                                            object-fit: cover;
                                            border-radius: 16px;
                                        }

                                        .img-1 {
                                            grid-row: span 2;
                                        }

                                        .img-4,
                                        .img-5 {
                                            height: 100%;
                                        }

                                        .grid-right {
                                            display: grid;
                                            grid-template-rows: 1fr 1fr;
                                            gap: 1rem;
                                        }

                                        .grid-bottom {
                                            display: grid;
                                            grid-template-columns: 1fr 1fr;
                                            gap: 1rem;
                                        }
                                    </style>
                                    <div class="gallery">
                                        <div class="img-1"><img src="<?php echo get_field('main_listing_image_1' , get_the_ID()) ?>" alt=""></div>
                                        <div class="grid-right">
                                            <div><img src="<?php echo get_field('main_listing_image_2' , get_the_ID()) ?>" alt=""></div>
                                            <div class="grid-bottom">
                                            <div><img src="<?php echo get_field('main_listing_image_3' , get_the_ID()) ?>" alt=""></div>
                                            <div><img src="<?php echo get_field('main_listing_image_4' , get_the_ID()) ?>" alt=""></div>
                                            </div>
                                        </div>
                                    </div>
                                        <?php
                                    }
                                }
                                wp_reset_postdata();
                            }
                        }
                    }			
            ?>
                <div class="order--details-wrap-inner" id="customer-order-info">
                    <div class="container--row">
                        <div class="col">
                            <div class="order--number">
                                <p><?php esc_html_e('Order Number:', 'smoobu-calendar'); ?> <span id="display-order-number">************</span></p>
                            </div>

                            <div class="customer--order-info">
                                <div class="title--wrap">
                                    <h4 class="title--inner"><?php esc_html_e('Bubble Tent (Name)', 'smoobu-calendar'); ?></h4>
                                    <div class="tent--details-btn"><a href="#"><?php esc_html_e("View bubble tent’s details", 'smoobu-calendar'); ?></a></div>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Calendar-Icon.webp" alt="Calendar Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Friday 20 Sep 2024 - Sunday 22 Sep 2024', 'smoobu-calendar'); ?></h6>
                                    </div>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Check-in-Instructions.webp"
                                            alt="Check-in Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Check-in instructions', 'smoobu-calendar'); ?></h6>
                                        <p class="detail--content"><?php esc_html_e('Fill in Check-in instructions for each bubble Tent', 'smoobu-calendar'); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Tent-Address-Icon.webp"
                                            alt="Tent Address Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Bubble Tent address', 'smoobu-calendar'); ?></h6>
                                        <p class="detail--content"><?php esc_html_e('Fill in the address for each bubble Tent', 'smoobu-calendar'); ?></p>
                                        <div class="tent--details-btn"><a href="#"><?php esc_html_e('Get directions', 'smoobu-calendar'); ?></a></div>
                                    </div>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Property-Policies-Icon.webp"
                                            alt="Property Policies Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Property policies', 'smoobu-calendar'); ?></h6>
                                        <div class="tent--details-btn"><a href="#"><?php esc_html_e('View all policies', 'smoobu-calendar'); ?></a></div>
                                    </div>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Contact-Owner-Icon.webp"
                                            alt="Contact the owner Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Contact the owner', 'smoobu-calendar'); ?></h6>
                                        <div class="tent--details-btn"><a href="#">+49 <span>************</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="contact--us-box" id="kontakformular">
                                <div class="title--wrap">
                                    <h4 class="title--inner"><?php esc_html_e('Contact us', 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Discuss changes to your booking or find out more about your stay.', 'smoobu-calendar'); ?>
                                    </p>
                                </div>
                                <div class="customer--info-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Email-Icon.webp" alt="Email Icon">
                                    </div>
                                    <div class="detail--wrap">
                                        <h6 class="detail--title"><?php esc_html_e('Email', 'smoobu-calendar'); ?></h6>
                                        <div class="tent--details-btn"><a
                                                href="mailto:support@book-a-bubble.de">support@book-a-bubble.de</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="title--wrap">
                                    <p class="sub--title"><?php esc_html_e('Support is available every day from 9 a.m. to 1 p.m. to answer your questions.', 'smoobu-calendar'); ?></p>
                                </div>
                            </div>
                        </div>


                        <div class="col">
                            <div class="booking--wrap">
                                <div class="title--wrap">
                                    <h4 class="title--inner"><?php esc_html_e('Your booking', 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Bubble Tent', 'smoobu-calendar'); ?> <span>****</span></p>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Name-on-the-reservation-Icon.webp"
                                            alt="Name-on-the-reservation-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">*<?php esc_html_e('Name on the reservation', 'smoobu-calendar'); ?>*</p>
                                    </div>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Number-of-guest-Icon.webp"
                                            alt="Number-of-guest-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">*<?php esc_html_e('Number of guest', 'smoobu-calendar'); ?>*</p>
                                    </div>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Coupon-voucher-used-Icon.webp"
                                            alt="Coupon-voucher-used-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">*<?php esc_html_e('Coupon/voucher used', 'smoobu-calendar'); ?>*</p>
                                    </div>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Add-on-list-Icon.webp"
                                            alt="Add-on-list-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">*<?php esc_html_e('Add on list', 'smoobu-calendar'); ?>*</p>
                                    </div>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Nachricht-an-den-Gastgeber-Icon.webp"
                                            alt="Nachricht-an-den-Gastgeber-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title">*<?php esc_html_e('Nachricht an den Gastgeber', 'smoobu-calendar'); ?>*</p>
                                    </div>
                                </div>

                                <div class="icon--text-box">
                                    <div class="icon--wrap">
                                        <img src="/wp-content/uploads/2024/12/Nachricht-an-den-Gastgeber-Icon.webp"
                                            alt="Nachricht-an-den-Gastgeber-Icon">
                                    </div>
                                    <div class="box--content">
                                        <p class="box--title"><?php esc_html_e('Cancellation policies', 'smoobu-calendar'); ?></p>
                                        <div class="tent--details-btn"><a href="#"><?php esc_html_e('View all policies', 'smoobu-calendar'); ?></a></div>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="order--total-wrap">
                                <div class="title--wrap">
                                    <h4 class="title--inner"><?php esc_html_e('Total', 'smoobu-calendar'); ?></h4>
                                </div>
                                <div class="title--wrap amount--wrap">
                                    <h4 class="title--inner"><span class="currency">€</span>195,00</h4>
                                    <p class="sub--title"><?php esc_html_e('Includes taxes and fees', 'smoobu-calendar'); ?></p>
                                </div>
                            </div>

                            <div class="add--booking-calendar-wrap">
                                <div class="add--booking-calendar-content">
                                    <div class="title--wrap">
                                        <h4 class="title--inner-small"><?php esc_html_e('Add your booking to your calendar', 'smoobu-calendar'); ?></h4>
                                        <p class="sub--title"><?php esc_html_e('You can click here and add your booking to your calendar so there is no chance you will forget your stay in the', 'smoobu-calendar'); ?> <strong><?php esc_html_e('Bubble Tent.', 'smoobu-calendar'); ?></strong></p>
                                    </div>
                                </div>
                                <div class="btn--wrap">
                                    <a href="#"><?php esc_html_e('Download', 'smoobu-calendar'); ?></a>
                                </div>
                            </div>

                            <div class="something--happened-wrap">
                                <div class="title--wrap">
                                    <h4 class="title--inner-small"><?php esc_html_e('Something happened?', 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Something happened and you need to change the date of your stay in the Bubble Tent ... ? No problem we are here to help you.', 'smoobu-calendar'); ?></p>
                                </div>
                                <div class="btn--wrap">
                                    <button id="modify_booking"><?php esc_html_e('Modify your booking', 'smoobu-calendar'); ?></button>
                                </div>
                            </div>
                            <div class="something--happened-wrap">
                                <div class="title--wrap">
                                    <h4 class="title--inner-small white_card_title">Rund um Elzach</h4>
                                    <p class="sub--title white_card_txt">Hier findest du Restaurantempfehlungen, Einkaufsmöglichkeiten, Ausflugsziele und weitere Informationen rund um Elzach.</p>
                                </div>
                                <div class="modify_green_btn show_info">
                                    <button id="show_info">Anschauen</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modify--your-booking-wrap" id="booking_sec">
                <?php
                    if (isset($_GET['order_id'])) {
                        $order_id = intval($_GET['order_id']);
                        $order = wc_get_order($order_id);
                        if ($order) {
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
                                        ?>
                                    <div class="gallery">
                                        <div class="img-1"><img src="<?php echo get_field('main_listing_image_1' , get_the_ID()) ?>" alt=""></div>
                                        <div class="grid-right">
                                            <div><img src="<?php echo get_field('main_listing_image_2' , get_the_ID()) ?>" alt=""></div>
                                            <div class="grid-bottom">
                                            <div><img src="<?php echo get_field('main_listing_image_3' , get_the_ID()) ?>" alt=""></div>
                                            <div><img src="<?php echo get_field('main_listing_image_4' , get_the_ID()) ?>" alt=""></div>
                                            </div>
                                        </div>
                                    </div>
                                        <?php
                                    }
                                }
                                wp_reset_postdata();
                            }
                        }
                    }			
                ?>
                <div class="content--fields">
					<a class="order_back" href="javascript:void(0)"><?php esc_html_e('Back','smoobu-calendar') ?></a>
                    <div class="container--row step-two">
                        <div class="col first-col">
							<div class="step2-main-heading">
								<h2 class="step-2-heading"><?php esc_html_e('Would you like to change your booking?','smoobu-calendar') ?></h2>
							</div>
                            <div class="content--wrap">
                                <p><?php esc_html_e('Something came up? No problem! You can simply choose a new date for your stay at the', 'smoobu-calendar'); ?> <span><?php esc_html_e('Bubble Tent', 'smoobu-calendar'); ?></span><?php esc_html_e('Select or send us a special request.', 'smoobu-calendar'); ?></p>
                            </div>
                        </div>
                        <div class="col second-col">
                            <div class="fields--wrap">
								<?php
								$order_id = intval($_GET['order_id']);
								$listings = get_listing_by_order_id_2($order_id);
								function get_listing_by_order_id_2($order_id) {
									
									$order = wc_get_order($order_id);

									if (!$order) return null;

									$matched_listings = [];

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
								?>
								<div id="your-listing-wrapper" class="mb-3" data-listing-id="<?php echo $listings[0]->ID ?>">
									<label class="date-picker-label abd-label"><?php esc_html_e('New desired date','smoobu-calendar') ?>:</label>
									<input type="text" id="your-datepicker" placeholder="tt.mm.jjjj">
								</div>
								<label class="req-submit-label abd-label"><?php esc_html_e('Special requests or comments','smoobu-calendar') ?>:</label>
                                <form id="req_submit">
<!--                                     <div class="smoobu-dates-selection-box">
                                        <div class="smoobu-date-entry-box">
                                            <label class="smoobu-date-entry-label" for="smoobu_calendar_start">
                                                <?php // esc_html_e('Check-In', 'smoobu-calendar'); ?>					</label>
                                            <input class="smoobu-calendar" type="text" id="smoobu_calendar_start" name="smoobu_calendar_start" placeholder="<?php // esc_html_e('Check-In', 'smoobu-calendar'); ?>" value="10-01-2025" readonly="">
                                            <span class="easepick-wrapper" style="position: absolute; pointer-events: none;"></span>
                                        </div>
                                        <div class="smoobu-date-entry-box">
                                            <label class="smoobu-date-entry-label" for="smoobu_calendar_start">
                                                <?php // esc_html_e('Check-Out', 'smoobu-calendar'); ?>					</label>
                                            <input class="smoobu-calendar" type="text" id="smoobu_calendar_end" name="smoobu_calendar_end" placeholder="<?php // esc_html_e('Check-Out', 'smoobu-calendar'); ?>" value="12-01-2025" readonly="">
                                        </div>
                                    </div> -->
<!--                                     <div class="form--group">
										<input type="date" name="arrival-date" id="arrival_date"
											   placeholder="<?php //esc_html_e('Arrival Date', 'smoobu-calendar'); ?>" required>
                                    </div>
									<div class="form--group">
										<input type="date" name="departure-date" id="departure_date"
											   placeholder="<?php // esc_html_e('Departure Date', 'smoobu-calendar'); ?>" required>
									</div> -->
                                    <div class="form--group">
                                        <textarea name="textarea" id="special_req"
                                            placeholder="<?php esc_html_e('Do you have any special requests or would you like to tell us something?', 'smoobu-calendar'); ?>"></textarea>
                                    </div>
                                </form>
								<div class="btn--wrap green--round">
									<button class="input--link" id="req_button"><?php esc_html_e('Request changes', 'smoobu-calendar'); ?></button>
									<div class="ajax-loading">
										<div class="loading-gif">
											<!-- <img src="/wp-content/uploads/2024/12/ajax-loading.gif" alt=""> -->
											<img src="/wp-content/uploads/2024/12/loading-gif-new.webp" alt="">
										</div>
									</div>
								</div>
                            </div>

                            <div class="content--wrap mt--25">
                                <p><?php esc_html_e("Don't want to choose a different date? No problem – you can cancel your booking here:", 'smoobu-calendar'); ?></p>
                            </div>

                            <div class="btn--wrap green--round">
                                <button id="booking_cancel"><?php esc_html_e('Cancel booking', 'smoobu-calendar'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cancel--your-booking-wrap" id="cancel_sec">
                <!-- <div class="go-back">
                    <button>
                        <i class="fas fa-long-arrow-alt-left"></i>
                        Go Back
                    </button>
                </div> -->
                                <?php
                    if (isset($_GET['order_id'])) {
                        $order_id = intval($_GET['order_id']);
                        $order = wc_get_order($order_id);
                        if ($order) {
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
                                        ?>
                                    <div class="gallery">
                                        <div class="img-1"><img src="<?php echo get_field('main_listing_image_1' , get_the_ID()) ?>" alt=""></div>
                                        <div class="grid-right">
                                            <div><img src="<?php echo get_field('main_listing_image_2' , get_the_ID()) ?>" alt=""></div>
                                            <div class="grid-bottom">
                                            <div><img src="<?php echo get_field('main_listing_image_3' , get_the_ID()) ?>" alt=""></div>
                                            <div><img src="<?php echo get_field('main_listing_image_4' , get_the_ID()) ?>" alt=""></div>
                                            </div>
                                        </div>
                                    </div>
                                        <?php
                                    }
                                }
                                wp_reset_postdata();
                            }
                        }
                    }			
                ?>
                <div class="text--content-wrap">
                    <div class="fields--wrap no--icons">
                        <form id="cancel_order">
                            <h5 class="title--small-heading"><?php esc_html_e('Reason for your cancellation', 'smoobu-calendar'); ?></h5>
                            <div class="form--group">
                                <textarea name="textarea" placeholder="<?php esc_html_e('Please state the reason for your cancellation', 'smoobu-calendar'); ?> ..." id="cancel_reason"
                                    required></textarea>
                            </div>
                            <h5 class="title--small-heading"><?php esc_html_e('How would you like to get your money back?', 'smoobu-calendar'); ?></h5>
                            <p class="error_msg"><?php esc_html_e('Please select a refund method.', 'smoobu-calendar'); ?></p>
                            <div class="form--group radio--box">
                                <div class="label--box">
                                    <input name="same" type="radio" id="voucher" />
                                    <label class="radio-custom-label"><h6 class="title"><?php esc_html_e('Voucher', 'smoobu-calendar'); ?></h6></label>
                                </div>
                                <div class="form--field-content">
                                    <p>(<?php esc_html_e('Valid for 3 years at all locations', 'smoobu-calendar'); ?>)</p>
                                </div>
                            </div>
                            <div class="form--group radio--box">
                                <div class="label--box">
                                    <input name="same" type="radio" id="bank_payment" />
                                    <label class="radio-custom-label"><h6 class="title"><?php esc_html_e('Original payment method', 'smoobu-calendar'); ?></h6></label>
                                </div>
                                <div class="form--field-content">
                                    <p>(<?php esc_html_e('We will ask for your payment method in the next step. If you paid by voucher, a refund is only possible by voucher.', 'smoobu-calendar'); ?>)</p>
                                </div>
                            </div>
                            <div class="btn--wrap green--round">
                                <button type="submit" class="input--link"><?php esc_html_e('Proceed to cancellation', 'smoobu-calendar'); ?></button>
                                <div class="ajax-loading">
                                    <div class="loading-gif">
                                        <!-- <img src="/wp-content/uploads/2024/12/ajax-loading.gif" alt=""> -->
                                        <img src="/wp-content/uploads/2024/12/loading-gif-new.webp" alt="">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="cancel_popup">
                <div class="d-flex">
                    <div class="cancel--your-booking-screen-two add--booking-calendar-wrap">
						
                        <div class="voucher-detail">
                            <div class="add--booking-calendar-content">
								
                                <div class="title--wrap">
									<div class="emoji">😔</div>
                                    <h4 class="title--inner-small"><?php esc_html_e('We are sorry that you cannot come.', 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Your booking is', 'smoobu-calendar'); ?> <span class="booking-day">**</span> <?php esc_html_e('Days away. Your refund amounts to', 'smoobu-calendar'); ?> <span class="refund-price">***€</span>. <?php esc_html_e('and will be credited within 3–5 days.', 'smoobu-calendar'); ?></p>
                                </div>
                            </div>
                            <div class="btn--wrap">
                                <button class="input--link white-text" id="confirm_refund"><?php esc_html_e('Confirm reimbursement', 'smoobu-calendar'); ?></button>
                            </div>
                        </div>
                        <div class="email-sucess">
							<div class="emoji check-emoji">✅</div>
                            <p><?php esc_html_e("Your enquiry has been successfully submitted. We will get back to you shortly.", 'smoobu-calendar'); ?></p>
                            <div class="btn--wrap">
                                <a href="#" class="reload-page input--link white-text"><?php esc_html_e('Back to main page', 'smoobu-calendar'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="payment_popup">
                <div class="d-flex">
                    <div class="bank--transfer-wrap add--booking-calendar-wrap">
						<div class="emoji card-emoji">💳</div>
                        <div class="title--wrap">
                            <h4 class="title--inner-small"><?php esc_html_e('How should we refund the money?', 'smoobu-calendar'); ?></h4>
                        </div>
                        <div class="fields--wrap no--icons">
                            <form id="bank_form">
                                <p class="error_msg"><?php esc_html_e('Please select a payment method.', 'smoobu-calendar'); ?></p>
                                <div class="form--group radio--box">
                                    <input name="same" type="radio" id="iban_radio" />
                                    <label class="radio-custom-label"></label>
                                    <div class="form--field-content">
                                        <h6 class="title"><?php esc_html_e('Bank transfer', 'smoobu-calendar'); ?></h6>
                                    </div>
                                </div>
                                <div class="form--group radio--box">
                                    <input name="same" type="radio" id="paypal_radio" />
                                    <label class="radio-custom-label"></label>
                                    <div class="form--field-content">
                                        <h6 class="title"><?php esc_html_e('Paypal', 'smoobu-calendar'); ?></h6>
                                    </div>
                                </div>
                                <div class="form--group radio--box">
                                    <input name="same" type="radio" id="creditcard_radio" />
                                    <label class="radio-custom-label"></label>
                                    <div class="form--field-content">
                                        <h6 class="title"><?php esc_html_e('Credit card', 'smoobu-calendar'); ?></h6>
                                    </div>
                                </div>
								<div class="input-box">
                                    <div class="iban">
                                        <p>IBAN:</p>
                                        <input type="text" placeholder="<?php esc_html_e('DE...', 'smoobu-calendar'); ?>" id="iban">
                                    </div>
									<div class="paypal_email">
                                        <p>PayPal-E-Mail:</p>
                                        <input type="email" placeholder="<?php esc_html_e('yourname@mail.de', 'smoobu-calendar'); ?>" id="paypal_email">
                                    </div>
									<div class="creditcard_number">
                                        <p><?php esc_html_e('Last 4 digits of the card', 'smoobu-calendar'); ?>:</p>
                                        <input type="number" placeholder="4 <?php esc_html_e('1234', 'smoobu-calendar'); ?>" id="creditcard_number">
                                    </div>
									
									
								</div>
                                <div class="btn--wrap">
                                    <button class="input--link white-text" type="submit" id="confirm_payment"><?php esc_html_e('Confirm reimbursement', 'smoobu-calendar'); ?></button>
                                    <div class="ajax-loading">
                                        <div class="loading-gif">
                                            <!-- <img src="/wp-content/uploads/2024/12/ajax-loading.gif" alt=""> -->
                                            <img src="/wp-content/uploads/2024/12/loading-gif-new.webp" alt="">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="transfered_popup">
                <div class="d-flex">
                    <div class="cancel--your-booking-screen-two add--booking-calendar-wrap">
                        <div class="payment-detail">
							
                            <div class="add--booking-calendar-content">
								<div class="emoji check-emoji">😔</div>
                                <div class="title--wrap">
                                    <h4 class="title--inner-small"><?php esc_html_e("We are sorry that you cannot come...", 'smoobu-calendar'); ?></h4>
                                    <p class="sub--title"><?php esc_html_e('Your booking is', 'smoobu-calendar'); ?> <span class="booking-day">**</span> <?php esc_html_e('Days away.', 'smoobu-calendar'); ?><br><?php esc_html_e('Your refund amounts to', 'smoobu-calendar'); ?> <span class="refund-price">***€</span>. <?php esc_html_e('and will be credited within 3–5 days.', 'smoobu-calendar'); ?></p>
                                </div>
                            </div>
                            <div class="btn--wrap">
                                <button class="input--link white-text" id="confirm_bank_payment"><?php esc_html_e('Confirm reimbursement', 'smoobu-calendar'); ?></button>
                            </div>
                        </div>
                        <div class="email-sucess">
							<div class="emoji check-emoji">✅</div>
                            <p><?php esc_html_e("Your enquiry has been successfully submitted. We will get back to you shortly.", 'smoobu-calendar'); ?></p>
                            <div class="btn--wrap">
                                <a href="#" class="reload-page input--link white-text"><?php esc_html_e('Back to main page', 'smoobu-calendar'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="success_popup">
                <div class="d-flex">
                    <div class="cancel--your-booking-screen-two add--booking-calendar-wrap">
                        <div class="payment-detail">
                            <div class="add--booking-calendar-content">
                                <div class="title--wrap">
                                    <p class="sub--title"><?php esc_html_e("You're request is submitted successfully we will get back to
                                        you soon.", 'smoobu-calendar'); ?></p>
                                </div>
                            </div>
                            <div class="btn--wrap">
                                <a href="#" class="reload-page input--link white-text"><?php esc_html_e('Back to main', 'smoobu-calendar'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<script>
jQuery('.reload-page').on('click', function() {
    location.reload(true);
})
</script>
<script type="text/javascript" src="https://dash.book-a-bubble.de/wp-content/plugins/listeo-core/assets/js/fullcalendar.min.js?ver=1" id="listeo-core-fullcalendar-js"></script>
<script type="text/javascript"
src="https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/js/index.umd.js?ver=1.2.2"
id="smoobu-calendar-easepick-js-js"></script>
<script type="text/javascript"
    src="https://dash.book-a-bubble.de/wp-content/plugins/smoobu-sync-wp/assets/js/main-checkout.min.js?ver=1.2.2"
    id="smoobu-calendar-checkout-js-js"></script>
<?php get_footer(); ?>