<?php
namespace MWEW\Inc\Google_Tags;

class Google_Tags_Init {

    protected $gtm_script;
    protected $booking_tracker;
    protected $listing_tracker;
    protected $voucher_tracker;
    protected $session_tracker;

    public function __construct(){

        $this->gtm_script = new GTM_Script();
        $this->booking_tracker = new Booking_Tracker();
        $this->listing_tracker = new Listing_Tracker();
        $this->voucher_tracker = new Voucher_Tracker();
        $this->session_tracker = new Session_Tracker();

        add_action( 'wp_head', [ $this->gtm_script, 'inject_script' ], 1 );
        add_action( 'wp_body_open', [ $this->gtm_script, 'inject_noscript' ] );
        add_action( 'wp_footer', [ $this, 'inject_events' ], 100 );
    }


    public function inject_events() {
        if (!Utilities::is_tracking_enabled()) {
            return;
        }

        echo '<script>window.dataLayer = window.dataLayer || [];</script>';
        $this->booking_tracker->track();
        $this->listing_tracker->track();
        $this->voucher_tracker->track();
        $this->session_tracker->track();
    }
}