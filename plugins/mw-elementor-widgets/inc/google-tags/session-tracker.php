<?php 

namespace MWEW\Inc\Google_Tags;

class Session_Tracker {
    public function track() {
        if (!Utilities::is_tracking_enabled() || $this->is_booking_completed()) return;

        ?>
        <script>
        setTimeout(function() {
            dataLayer.push({ event: 'long_session_no_conversion' });
        }, 120000);
        </script>
        <?php
    }

    private function is_booking_completed() {
        return isset($_SESSION['mwew_booking_completed']);
    }
}
