<?php
namespace MWEW\Inc\Admin;

class GTM_GA4_Settings {

    private $option_group = 'mwew_gtm_ga4_options';
    private $option_name = 'mwew_gtm_ga4_settings';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings_page() {
        add_options_page(
            __( 'GTM + GA4 Settings', 'mwew' ),
            __( 'GTM + GA4', 'mwew' ),
            'manage_options',
            'mwew-gtm-ga4-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( $this->option_group, $this->option_name );

        add_settings_section(
            'mwew_gtm_ga4_main_section',
            __( 'Google Tag Manager & GA4 Settings', 'mwew' ),
            null,
            'mwew-gtm-ga4-settings'
        );

        add_settings_field(
            'gtm_container_id',
            __( 'GTM Container ID', 'mwew' ),
            [ $this, 'render_text_field' ],
            'mwew-gtm-ga4-settings',
            'mwew_gtm_ga4_main_section',
            [
                'label_for' => 'gtm_container_id',
                'name'      => 'gtm_container_id',
                'placeholder' => 'GTM-XXXXXXX'
            ]
        );

        add_settings_field(
            'ga4_measurement_id',
            __( 'GA4 Measurement ID', 'mwew' ),
            [ $this, 'render_text_field' ],
            'mwew-gtm-ga4-settings',
            'mwew_gtm_ga4_main_section',
            [
                'label_for' => 'ga4_measurement_id',
                'name'      => 'ga4_measurement_id',
                'placeholder' => 'G-XXXXXXXXXX'
            ]
        );

        add_settings_field(
            'enable_event_tracking',
            __( 'Enable Event Tracking', 'mwew' ),
            [ $this, 'render_checkbox_field' ],
            'mwew-gtm-ga4-settings',
            'mwew_gtm_ga4_main_section',
            [
                'label_for' => 'enable_event_tracking',
                'name'      => 'enable_event_tracking',
            ]
        );

        add_settings_field(
            'voucher_category_ids',
            __( 'Voucher Product Categories', 'mwew' ),
            [ $this, 'render_category_multiselect_field' ],
            'mwew-gtm-ga4-settings',
            'mwew_gtm_ga4_main_section',
            [
                'label_for' => 'voucher_category_ids',
                'name'      => 'voucher_category_ids'
            ]
        );

    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'GTM + GA4 Settings', 'mwew' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( 'mwew-gtm-ga4-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_text_field( $args ) {
        $options = get_option( $this->option_name );
        $value = $options[ $args['name'] ] ?? '';
        printf(
            '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" placeholder="%4$s" />',
            esc_attr( $args['name'] ),
            esc_attr( $this->option_name ),
            esc_attr( $value ),
            esc_attr( $args['placeholder'] ?? '' )
        );
    }

    public function render_checkbox_field( $args ) {
        $options = get_option( $this->option_name );
        $checked = isset( $options[ $args['name'] ] ) && $options[ $args['name'] ];
        printf(
            '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />',
            esc_attr( $args['name'] ),
            esc_attr( $this->option_name ),
            checked( $checked, true, false )
        );
    }

    public function render_category_multiselect_field( $args ) {
        $options = get_option( $this->option_name );
        $selected = $options[ $args['name'] ] ?? [];

        if ( ! is_array( $selected ) ) {
            $selected = [];
        }

        $terms = get_terms( [
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ] );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            printf(
                '<select id="%1$s" name="%2$s[%1$s][]" multiple style="min-width: 300px; min-height: 120px;">',
                esc_attr( $args['name'] ),
                esc_attr( $this->option_name )
            );

            foreach ( $terms as $term ) {
                printf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    esc_attr( $term->term_id ),
                    selected( in_array( $term->term_id, $selected ), true, false ),
                    esc_html( $term->name )
                );
            }

            echo '</select>';
        } else {
            esc_html_e( 'No product categories found.', 'mwew' );
        }
    }


}
