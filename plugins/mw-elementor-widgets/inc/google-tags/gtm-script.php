<?php 

namespace MWEW\Inc\Google_Tags;

class GTM_Script {
    public function inject_script() {
        if (!Utilities::is_tracking_enabled()) return;
        $settings = get_option('mwew_gtm_ga4_settings', []);
        $gtm_id = $settings['gtm_container_id'] ?? '';

        if ($gtm_id) {
            echo "<!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','".esc_js($gtm_id)."');</script>
            <!-- End Google Tag Manager -->";
        }
    }

    public function inject_noscript() {
        if (!Utilities::is_tracking_enabled()) return;
        $settings = get_option('mwew_gtm_ga4_settings', []);
        $gtm_id = $settings['gtm_container_id'] ?? '';

        if ($gtm_id) {
            echo '<!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.esc_attr($gtm_id).'"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->';
        }
    }
}
