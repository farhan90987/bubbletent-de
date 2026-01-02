<?php
/*
Template Name: Location Page Template
*/

get_header();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lightgallery.min.css">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-zoom.min.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-thumbnail.min.css">
<style>
.wc-timeline-button-show-cart.right {
	right: 15px;
	bottom: 95px !important;
}
#elementor-lightbox-slideshow-single-img {
	display: none !important;
}

body.page-template-template-location-page div#header:first-of-type {
	background: rgba(0, 0, 0, 0.45) !important;
	box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
	backdrop-filter: blur(5px) !important;
}
body.page-template-template-location-page div#header:first-of-type #navigation.style-1 .jet-menu-title {
	color: #fff;
}
a.landing-booking-btn {
	font-family: "Manrope", Sans-serif;
	font-size: 14px;
	font-weight: 600;
	text-transform: uppercase;
	line-height: 24px;
	fill: #FFFFFF;
	color: #FFFFFF;
	background-color: #161D1B4D;
	border-radius: 50px 50px 50px 50px;
	padding: 12px 25px 12px 25px;
	backdrop-filter: blur(2px);
	box-shadow: -1px -1px 0px 0px rgba(255, 255, 255, .25) inset;
	transition: 0.5s;
}
a.landing-booking-btn:hover {
	box-shadow: 1px 1px 0px 0px rgba(255, 255, 255, .25) inset;
}
.progress-section {
    position: relative;
    width: 100%;
    padding: 30px 0;
}
.dotted-line {
    position: relative;
    width: 100%;
    height: 2px;
    background-image: linear-gradient(to right, #4a5f5a 40%, transparent 40%);
    background-size: 10px 2px;
    background-repeat: repeat-x;
}
.circle {
    position: absolute;
    width: 16px;
    height: 16px;
    background-color: rgba(37, 100, 75, 0.2);
    border-radius: 50%;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
    transition: left 0.15s ease-out;
    cursor: pointer;
    z-index: 10;
}

.gallery-popup-main.popup-gallery-show~body {
	margin: 0;
	height: 100%;
	overflow: hidden
}
.gallery-popup-main.popup-gallery-show {
	top: 0%;
	opacity: 1;
	z-index: 1010;
	height: 100vh;
}

.gallery-popup-main {
	width: 100%;
	height: 0vh;
	overflow: auto;
	position: fixed;
	top: 100%;
	transition: .3s;
	background-color: #fff;
	left: 0;
	opacity: 0;
	z-index: -1;
	scrollbar-width: none;
}

.gallery-popup-main .popup-gallery {
	display: grid;
	grid-template-columns: 1fr 1fr;
	grid-template-rows: repeat(auto-fill, minmax(200px, 500PX));
	grid-gap: 30px;
	overflow: auto;

	-ms-overflow-style: none;
	padding: 20px;
	max-width: 1440px;
	margin: auto;
	margin-bottom: 55px;
	grid-template-areas:
	"item1 item1"
	"item2 item3"
	"item4 item4"
	"item5 item6"
	"item7 item7";
}
.gallery-popup-main .popup-gallery a {
	display: flex;
	overflow: hidden;
	max-height: 500px;
}
.gallery-popup-main .popup-gallery img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}
.lg-backdrop {
	background-color: rgba(0, 0, 0, 0.8);
}
.gallery-popup-main .head {
	height: 75px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 10px;
	box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
	position: -webkit-sticky;
	/* For Safari */
	position: sticky;
	/* Standard */
	top: 0;
	z-index: 1000;
	background: #fff;
}
.gallery-popup-main .head a {
	color: #000;
	font-size: 28px;
}
.gallery-popup-main .head svg {
	width: 25px;
	height: 25px;
}
.gallery-popup-main .head span {
	display: flex;
	gap: 15px;
}

.points-wrap .elementor-icon-box-title::before {
	content: '';
	position: absolute;
	width: 22px;
	height: 22px;
	top: 1px;
	left: -31px;
	border-radius: 30px;
	font-family: inherit;
	color: #fff;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	background-image: linear-gradient(180deg, #678570 0%, #181F1A 100%);
	opacity: 0;
	transition: 0.5s;
}
.points-wrap .elementor-icon-box-title.actv::before {
	opacity: 1;
}

.points-wrap > .elementor-element.e-child:nth-child(1) .elementor-icon-box-title::before {
	content: '1';
}
.points-wrap > .elementor-element.e-child:nth-child(2) .elementor-icon-box-title::before {
	content: '2';
}
.points-wrap > .elementor-element.e-child:nth-child(3) .elementor-icon-box-title::before {
	content: '3';
}
.points-wrap > .elementor-element.e-child:nth-child(4) .elementor-icon-box-title::before {
	content: '4';
}
.points-wrap > .elementor-element.e-child:nth-child(5) .elementor-icon-box-title::before {
	content: '5';
}

body:not(.elementor-editor-active) #folding-wrap {
	row-gap: 45px;
}
#page-menu .elementor-icon-list-text.actv {
	color: #25644B !important;
}

.equipments ul li.elementor-icon-list-item:nth-child(n + 6) {
	display: none;
} 
.equipment-btn {
	cursor: pointer;
}
.equipment-btn .elementor-widget-image img {
	transition: .5s;
}









@media (min-width: 768px) {
	body:not(.elementor-editor-active) #folding-wrap {
		/*display: none;*/
	}
}

@media (max-width: 1023px) {
	body:not(.elementor-editor-active) #folding-wrap {
		row-gap: 25px;
	}
	a.landing-booking-btn {
		display: none !important;
	}
}

@media (max-width: 700px) {
	.gallery-popup-main .popup-gallery {
		display: grid;
		grid-template-columns: 1fr 1fr;
		grid-template-rows: repeat(auto-fill, minmax(200px, 1fr));
		grid-gap: 10px;
		padding: 0px;
		max-width: 1440px;
		margin: auto;
		margin-bottom: 35px;
	}
	.gallery-popup-main .head {
		height: 55px;
		box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
	}
	#faqs .e-n-tabs-content .elementor-widget-icon-box .elementor-icon-box-title {
		font-size: 15px !important;
		font-weight: 800 !important;
	}
}

@media (max-width: 400px) {
	.gallery-popup-main .popup-gallery a {
		max-height: 491px;
	}
}

/*.gallery-popup-main .popup-gallery a:nth-child(1) {grid-area: item1;}
.gallery-popup-main .popup-gallery a:nth-child(2) {grid-area: item2;}
.gallery-popup-main .popup-gallery a:nth-child(3) {grid-area: item3;}
.gallery-popup-main .popup-gallery a:nth-child(4) {grid-area: item4;}
.gallery-popup-main .popup-gallery a:nth-child(5) {grid-area: item5;}
.gallery-popup-main .popup-gallery a:nth-child(6) {grid-area: item6;}*/

.btn-infos {
	width: 61px;
	height: 61px;
	background-color: transparent;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	position: fixed;
	right: 14px;
	bottom: 170px;
	z-index: 90;
}
.btn-infos .info-main{
	position: absolute;
	right: calc(100% - 10px);
	gap: 10px;
	display: flex;
	flex-direction: column;
	transition: .3s;
	transform: translateX(49px);
	opacity: 0;
}
.btn-infos .image{
	position: relative;
	z-index: 10;
	width: 61px;
	height: 61px;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: transparent;
	background-image: linear-gradient(180deg, #678570 0%, #181F1A 100%);
	/*border: 2px solid #C10230;*/
	border-radius: 50%;
}
.btn-infos a img{
	width: 37px;
	object-fit: contain;
}
.btn-infos .info-main a p{
	margin-bottom: 0;
	margin-bottom: 0;
	transform: translateY(2px);
}
.btn-infos .info-main a{
	font-family: "Manrope", Sans-serif;
	background-color: transparent;
	background-image: linear-gradient(180deg, #678570 0%, #181F1A 100%);
	justify-content: space-between;
	padding: 0px;
	border-radius: 40px;
	/* gap: 15px; */
	display: flex;
	align-items: center;
	color: #fff;
	font-size: 14px;
	line-height: 24px;
	font-weight: 700;
	transition: .3s;
	overflow: hidden;
	height: 48px;
	width: 100px;
	text-decoration: none;
	text-transform: uppercase;
}
.btn-infos .info-main a:nth-child(1){
	position: absolute;
	bottom: -25px;
	right: 0;
}
.btn-infos .info-main a:nth-child(2){
	position: absolute;
	bottom: 0;
	top: 0;
	margin: auto;
	right: 0;
}
.btn-infos .info-main a:nth-child(3){
	position: absolute;
	top: -25px;
	right: 0;
}
.btn-infos.btn-infos-active a:nth-child(1){
	animation: info-btn1 1 .5s linear;
	right: 70px;
	bottom: 5px;
	width: 180px;
	width: unset;
	padding: 0 15px;
}
.btn-infos.btn-infos-active a:nth-child(2){
	animation: info-btn2 1 .5s linear;
	right: 70px;
	bottom: -70px;
	width: 200px;
	padding: 0 15px;
}
.btn-infos.btn-infos-active a:nth-child(3){
	animation: info-btn3 1 .5s linear;
	top: 50px;
	right: 30px;
	width: 180px;
	padding: 0 20px;
}
.btn-infos.btn-infos-active .info-main{
	opacity: 1;
}

/********************************************************/

.page-template-template-location-page .smoobu-price-display-container{
	font-family: Montserrat !important;
	font-size: 34px !important;
	font-weight: 500 !important;
	color: #647867 !important;
	padding:0; justify-content:center;
	margin-bottom:20px;
}

.page-template-template-location-page .smoobu-calendar-estimate {
	margin-top:1vw;
	margin-bottom:1vw;
}

.page-template-template-location-page .smobuutext img {
	width:30px;
	margin-right:5px;
}
.page-template-template-location-page .smobuutext.secondparasobu img { margin-right:10px; }
.page-template-template-location-page .smoobu-calendar-button-container a{ text-align:center; }
.page-template-template-location-page input.smoobu-calendar::placeholder { color: #fff; }
.page-template-template-location-page input.smoobu-calendar { background: #54775E !important; color:#fff !important;}
#calendar_popup_close svg, #voucher_popup_close svg{ width:26px; height: 26px; cursor: pointer;}
#calendar_popup_close , #voucher_popup_close{ position:absolute; top:10px; right:10px; }
.single_variation {padding: 10px 20px;}
input#flexible_coupon_recipient_name {height: 44px; line-height: 44px;}
textarea#flexible_coupon_recipient_message {min-height: 100px;}

.page-template-template-location-page #smoobu-check-availability {
	column-gap:2vw !important;
	padding: 0;
}

@media (max-width: 767px) {
	.btn-infos {
		width: 48px;
		height: 48px;
		right: 20px;
		top: 80px;
		bottom: unset;
	}
	.page-template-template-location-page input.smoobu-calendar {
		margin-top:16px;
	}
	.btn-infos .image {
		width: 48px;
		height: 48px;
	}
	.btn-infos .info-main a {
		height: 40px;
	}
	.btn-infos.btn-infos-active a:nth-child(1){
		right: 60px;
		bottom: 0px;
	}
	.btn-infos.btn-infos-active a:nth-child(2){
		right: 60px;
		bottom: -60px;
	}
	.custom-product-popup{
		flex-direction:column;
		gap:10px;
	}
	.single_variation ,.variations_form P{
		margin-bottom:10px;
	}
	.popup-image div{
		height:300px !important;
	}
	.page-template-template-location-page .smoobu-price-display-container{
		font-size:20px !important;
	}
	.elementor-widget-container:has(.custom-product-popup) {
    	height: 80vh !important;
    	overflow: auto;
	}
	.custom-product-popup {
		row-gap: 10px !important;
	}
	div[data-elementor-type="wp-page"] > .elementor-element:first-of-type::before {
		top: 2px !important;
	}
	.lg-backdrop {
		/*pointer-events: auto !important;
		z-index: 999999 !important;*/
	}
	.lg-toolbar {
		background-color: rgba(0,0,0,.45);
	}
	#calendar_popup_close {
		/*top: -5px;
		right: 10px;*/
	}
	#voucher_popup_close {
		top: 8px;
		right: 8px;
	}
	#voucher_popup_close svg {
		width: 28px;
		height: 28px;
		background-color: #ffffff87;
		border-radius: 5px;
	}
	.calendar_popup .elementor-widget-shortcode > .elementor-widget-container,
	.voucher_popup .elementor-widget-shortcode > .elementor-widget-container {
		border-radius: 13px;
	}
}


@media (min-width: 1024px){
	.page-menu > .e-con-inner::before,
	.infos > .e-con-inner::before,
	.checkins > .e-con-inner::before,
	.ausstattung > .e-con-inner::before,
	.lageplan > .e-con-inner::before,
	.parken > .e-con-inner::before {
		content: '';
		position: absolute;
	    left: calc((100% - 1330px) / 2);
	    top: 0;
	    width: 2px;
	    height: 100%;
	    border-right: 1px dashed rgba(26, 68, 61, 0.8);
	}
	.infos > .e-con-inner::after,
	.checkins > .e-con-inner::after,
	.ausstattung > .e-con-inner::after,
	.lageplan > .e-con-inner::after,
	.parken > .e-con-inner::after,
	.reviews-wrap::after {
		content: '';
		writing-mode: vertical-rl;
		position: absolute;
		left: calc((100% - 1355px) / 2);
		backdrop-filter: blur(5px);
		padding: 10px 0px;
		transform: rotate(180deg);
	}
	
	.infos > .e-con-inner::after {
		content: 'Allgemeine Infos zum Standort';
	}
	html[lang="en-US"] .infos > .e-con-inner::after { content: 'General information'; }
	
	.reviews-wrap::after {
		content: 'Bewertungen';
		top: 20%;
	}
	html[lang="en-US"] .reviews-wrap::after { content: 'Reviews'; }
	
	.checkins > .e-con-inner::after {
		content: 'Check-in & Ablauf';
		top: 70%;
	}
	html[lang="en-US"] .checkins > .e-con-inner::after { content: 'Check-in & Procedure'; }
	
	.ausstattung > .e-con-inner::after {
		content: 'Ausstattung';
		top: 65px;
	}
	html[lang="en-US"] .ausstattung > .e-con-inner::after { content: 'Equipment'; }

	.lageplan > .e-con-inner::after {
		content: 'Lageplan & Anreise';
		top: 11%;
	}
	html[lang="en-US"] .lageplan > .e-con-inner::after { content: 'Site plan'; }
	
	.parken > .e-con-inner::after {
		content: 'FAQ';
		top: 590px;
	}
	html[lang="en-US"] .parken > .e-con-inner::after { content: 'FAQs'; }

	#header-container .left-side {
		display: flex;
		justify-content: space-between;
	}
	.voucher_popup > .e-con-inner {
		max-width: 1000px;
	}
}

</style>

<?php
while ( have_posts() ) : the_post();

	the_content();

endwhile; // End of the loop.

?>

<!-- single listing slider html code -->
<div class="gallery-popup-main">
	<?php
	$get_listing_id = get_field('getting_listing_id');
	$listing_gallery = get_post_meta($get_listing_id, '_gallery', true);

	$page_gallery = get_field('standortbilder'); ?>

	<div class="head">
		<a href="#" class="show-popup"><svg aria-hidden="true" class="e-font-icon-svg e-fas-chevron-left" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg"><path d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"></path></svg></a>
	</div>
	<div class="popup-gallery">
		<?php if (!empty($page_gallery)){ 
			
			foreach( $page_gallery as $imge ) { ?>
				<a href="<?php echo esc_url($imge) ?>">
					<img src="<?php echo esc_url($imge); ?>" alt="">
				</a>
			<?php } ?>

		<?php } else if (!empty($listing_gallery)){

			foreach ((array) $listing_gallery as $attachment_id => $attachment_url) {
				$gallery_image = wp_get_attachment_image_src($attachment_id, 'listeo-gallery'); ?>
				<a href="<?= esc_url($gallery_image[0]) ?>">
					<img src="<?= esc_url($gallery_image[0]) ?>" alt="">
				</a>
			<?php } ?>

		<?php } ?>
	</div>
</div>

<div class="btn-infos">
	<div class="image">
		<img src="https://book-a-bubble.de/wp-content/uploads/2025/10/book-icon.png" alt="" loading="lazy">
	</div>
	<div class="info-main">
		<a href="javascript:void(0);" class="calendar_popup_show" id=""><?php esc_html_e('BUCHEN', 'listeo_core'); ?></a>
		<a href="javascript:void(0);" class="voucher_popup_show" id=""><?php esc_html_e('Gutschein bestellen', 'listeo_core'); ?></a>
	</div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/lightgallery.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/zoom/lg-zoom.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/thumbnail/lg-thumbnail.min.js"></script>
<script>
	// lightGallery(document.querySelector('.popup-gallery'), {

	const galleryElement = document.querySelector('.popup-gallery');

	const gallery = lightGallery(galleryElement, {
		selector: 'a',
  		plugins: [lgThumbnail],
		closable: true,
		controls: true,
		backdropDuration: 300,
		download: false,
		showCloseIcon: true,
		mobileSettings: {
		    controls: true,
		    showCloseIcon: true,
		    download: false,
		},
	});

</script>
<script>
    jQuery(document).ready(function() {

		// jQuery(document).on('click touchend', '.lg-backdrop', function() {
		// 	window.lgData[jQuery('.popup-gallery').get(0)].destroy(true);
		// });

		jQuery('.menu-list a').on('click', function(e) {
		    e.preventDefault();

		    setTimeout(function() {
	    		jQuery('.menu_popup').hide(500);
			}, 500);
		});

		jQuery('.equipments-btn').on('click', function(e) {
		    e.preventDefault();
		    
		    var $btn = jQuery(this);
		    var $btnText = $btn.find('.elementor-button-text');
		    var $equipments = jQuery('.equipments');
		    
		    // Define text for different languages
		    var texts = {
		        'de': {
		            'show': 'Weitere Ausstattung ansehen',
		            'hide': 'Weniger anzeigen'
		        },
		        'en': {
		            'show': 'View More Equipment',
		            'hide': 'Show Less'
		        },
		        'fr': {
		            'show': 'Voir plus d\'équipement',
		            'hide': 'Voir moins'
		        }
		    };
		    
		    var currentLang = 'de';
		    
		    if (jQuery('html').attr('lang')) {
		        currentLang = jQuery('html').attr('lang').substring(0, 2);
		    } else if (jQuery('body').attr('class')) {
		        var bodyClasses = jQuery('body').attr('class');
		        if (bodyClasses.indexOf('lang-en') !== -1) {
		            currentLang = 'en';
		        } else if (bodyClasses.indexOf('lang-de') !== -1) {
		            currentLang = 'de';
		        } else if (bodyClasses.indexOf('lang-fr') !== -1) {
		            currentLang = 'fr';
		        }
		    } else {
		        var path = window.location.pathname;
		        if (path.indexOf('/en/') !== -1 || path.startsWith('/en')) {
		            currentLang = 'en';
		        } else if (path.indexOf('/fr/') !== -1 || path.startsWith('/fr')) {
		            currentLang = 'fr';
		        } else if (path.indexOf('/de/') !== -1 || path.startsWith('/de')) {
		            currentLang = 'de';
		        }
		    }
		    
		    // Fallback to default if language not found in texts object
		    if (!texts[currentLang]) {
		        currentLang = 'de';
		    }
		    

		    var isHidden = $equipments.find('ul li:nth-child(6)').css('display') === 'none';
		    
		    if (isHidden) {
		        $equipments.find('ul li').css('display', 'flex');
        		$btnText.text(texts[currentLang].hide);
		    } else {
		        $equipments.find('ul li:nth-child(n + 6)').css('display', 'none');
        		$btnText.text(texts[currentLang].show);
		    }
		});



		jQuery('.equipment-btn').on('click', function(e) {
		    e.preventDefault();

		    var $equipment_btn = jQuery(this);
		    var $equipment = jQuery(this).siblings('.equipments')

		    var isHidden = $equipment.find('ul li:nth-child(6)').css('display') === 'none';

			if (isHidden) {
		        $equipment.find('ul li').css('display', 'flex');
		        $equipment_btn.find('.elementor-widget-image img').css('transform', 'rotate(180deg)');
		    } else {
		        $equipment.find('ul li:nth-child(n + 6)').css('display', 'none');
		        $equipment_btn.find('.elementor-widget-image img').css('transform', 'rotate(0deg)');
		    }
		});

		/*******************************************************************/

    	// jQuery("#folding-btn").click(function(e){
    	// 	e.preventDefault();

    	// 	jQuery(this).hide();

    	// 	jQuery("#folding-wrap").slideDown(400);
    	// });

    	/*******************************************************************/

    	jQuery(".btn-infos").click(function(){
			jQuery(".btn-infos").toggleClass("btn-infos-active");
		});

		

    	/*******************************************************************/

		jQuery("#bilder").on("click", function (e) {
			e.preventDefault();
			jQuery(".gallery-popup-main").toggleClass("popup-gallery-show");
		});

		jQuery(".show-popup").on("click", function (e) {
			e.preventDefault();
		jQuery('html, body').animate({
				scrollTop: jQuery('#header-container').offset().top
			}, 100);
			jQuery(".gallery-popup-main").toggleClass("popup-gallery-show");
		});

		/*******************************************************************/

        var $container = jQuery('.procedure-wrap');
        var $circle = jQuery('.circle');
        var $dottedLine = jQuery('.dotted-line');

        // Track mouse movement on entire body/container
        $container.on('mousemove', function(e) {
            // Get the bounding rect of the dotted line
            var lineRect = $dottedLine[0].getBoundingClientRect();
            
            // Calculate mouse position relative to the line
            var mouseX = e.clientX - lineRect.left;
            
            // Constrain the circle within the line bounds
            var minX = 0;
            var maxX = lineRect.width;
            var constrainedX = Math.max(minX, Math.min(mouseX, maxX));
            
            // Calculate percentage position
            var percentX = (constrainedX / lineRect.width) * 100;
            
            // Update circle position
            $circle.css('left', percentX + '%');
        });

        // Optional: Reset to start when mouse leaves the page
        $container.on('mouseleave', function() {
            $circle.css('left', '0%');
        });


		/*******************************************************************/

		// Function to check if element is in middle of viewport
		function isInMiddle(element) {
			var rect = element.getBoundingClientRect();
			var windowHeight = window.innerHeight || document.documentElement.clientHeight;
			var elementMiddle = rect.top + (rect.height / 2);
			var viewportMiddle = windowHeight / 2;

			// Check if element's middle is close to viewport middle (within 100px tolerance)
			return Math.abs(elementMiddle - viewportMiddle) < 100;
		}


		function handleScrollActivation() {

			if (jQuery(window).width() >= 768) {

				jQuery('.checkin-point').find('.elementor-icon-box-title').removeClass('actv');
				return;
			}

			var $checkinPoints = jQuery('.checkin-point');
			var closestIndex = -1;
			var closestDistance = Infinity;

			// Find which element is closest to middle
			$checkinPoints.each(function(index) {
				var rect = this.getBoundingClientRect();
				var windowHeight = window.innerHeight || document.documentElement.clientHeight;
				var elementMiddle = rect.top + (rect.height / 2);
				var viewportMiddle = windowHeight / 2;
				var distance = Math.abs(elementMiddle - viewportMiddle);

				if (distance < closestDistance) {
					closestDistance = distance;
					closestIndex = index;
				}
			});

			$checkinPoints.find('.elementor-icon-box-title').removeClass('actv');

			if (closestIndex !== -1) {
				$checkinPoints.eq(closestIndex).find('.elementor-icon-box-title').addClass('actv');
			}
		}


		jQuery(window).on('scroll', handleScrollActivation);
		jQuery(window).on('resize', handleScrollActivation);

		if (jQuery(window).width() < 768) {
			jQuery('.checkin-point').first().find('.elementor-icon-box-title').addClass('actv');
		}

		handleScrollActivation();


		/*******************************************************************/


		// jQuery('#page-menu a[href="#rund-um"]').on('click', function(e) {
		// 	e.preventDefault();

		// 	var targetSection = jQuery('#rund-um');
		// 	var parentSection = jQuery('#folding-wrap');

		// 	if (parentSection.is(':hidden')) {
		// 		parentSection.slideDown(400, function() {
		// 			jQuery('html, body').animate({
		// 				scrollTop: targetSection.offset().top
		// 			}, 600);
		// 		});
	    // 		jQuery('#folding-btn').hide();
		// 	} else {
		// 		jQuery('html, body').animate({
		// 			scrollTop: targetSection.offset().top
		// 		}, 600);
		// 	}
		// });


		// Add 'actv' class to first menu item by default
		jQuery('#page-menu li:first-child .elementor-icon-list-text').addClass('actv');


		function updateActiveMenu() {
			var scrollPos = jQuery(window).scrollTop() + 250; // Offset for better detection
			var found = false;

			jQuery('#page-menu a').each(function() {
				var currLink = jQuery(this);
				var sectionId = currLink.attr('href');
				var refElement = jQuery(sectionId);

				if (refElement.length) {
					var sectionTop = refElement.offset().top;
					var sectionBottom = sectionTop + refElement.outerHeight();

					if (sectionTop <= scrollPos && sectionBottom > scrollPos) {
						if (!found) {

							jQuery('#page-menu .elementor-icon-list-text').removeClass('actv');

							currLink.find('.elementor-icon-list-text').addClass('actv');
							found = true;
						}
					}
				}
			});

			// If no section is in viewport, keep first item active
			// if (!found) {
			// 	jQuery('#page-menu .elementor-icon-list-text').removeClass('actv');
			// 	jQuery('#page-menu li:first-child .elementor-icon-list-text').addClass('actv');
			// }
		}

		var scrollTimeout;
		jQuery(window).on('scroll', function() {
			clearTimeout(scrollTimeout);
			scrollTimeout = setTimeout(function() {
				updateActiveMenu();
			}, 50);
		});

		setTimeout(function() {
			updateActiveMenu();
		}, 100);

		/*******************************************************/

		if ( jQuery(window).width() < 768  && jQuery('.equipments-wrapper').length > 0 ) {
	        jQuery('.equipments-wrapper').addClass('owl-carousel owl-theme');
	        jQuery('.equipments-wrapper').owlCarousel({
	            loop: false,
	            margin: 10,
	            nav: false,
	            dots: true,
	            items: 1,
	            stagePadding: 25,
	            autoHeight: false
	        });
	    }


	    if ( jQuery(window).width() < 768  && jQuery('.rund-ums').length > 0 ) {
	        jQuery('.rund-ums').addClass('owl-carousel owl-theme');
	        jQuery('.rund-ums').owlCarousel({
	            loop: true,
	            margin: 10,
	            nav: false,
	            dots: true,
	            items: 1,
	            stagePadding: 25,
	            autoHeight: false
	        });
	    }



	    jQuery('.calendar_popup_show').on('click',function(e){
	    	e.preventDefault();
			jQuery('.voucher_popup').fadeOut();
	    	jQuery('.calendar_popup').fadeIn();
			jQuery('body').css({"overflow-y": "hidden","height": "92vh",})
	    });

	    jQuery('.calendar_popup').on('click', function(e) {
			if (!jQuery(e.target).closest('.elementor-widget-shortcode').length) {
				jQuery('.calendar_popup').fadeOut();
				jQuery('body').removeAttr('style')
			}
		});

	    jQuery('#calendar_popup_close').on('click',function(){
	    	jQuery('.calendar_popup').fadeOut();
			jQuery('body').removeAttr('style')
	    });

		jQuery('.voucher_popup_show').on('click', function (e) {
			e.preventDefault();

			let cartUrl = '/warenkorb/'; // default DE

			if (window.location.pathname.startsWith('/en/')) {
				cartUrl = '/en/shopping-cart/';
			} else if (window.location.pathname.startsWith('/fr/')) {
				cartUrl = '/fr/panier-dachat/';
			}

			jQuery('.custom-product-popup form.variations_form')
				.attr('action', cartUrl);

			jQuery('.calendar_popup').fadeOut();
			jQuery('.voucher_popup').fadeIn();

			jQuery('body').css({
				"overflow-y": "hidden",
				"height": "90vh"
			});
		});


	    jQuery('.voucher_popup').on('click', function(e) {
			if (!jQuery(e.target).closest('.elementor-widget-shortcode').length) {
				jQuery('.voucher_popup').fadeOut();
				jQuery('body').removeAttr('style')
			}
		});

	    jQuery('#voucher_popup_close').on('click',function(){
	    	jQuery('.voucher_popup').fadeOut();
			jQuery('body').removeAttr('style')
	    });



    });
</script>




<?php
get_footer();
