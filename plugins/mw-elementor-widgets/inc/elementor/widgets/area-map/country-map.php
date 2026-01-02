<?php

namespace MWEW\Inc\Elementor\Widgets\Area_Map;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use MWEW\Inc\Services\Map_Repo;
use MWEW\Inc\Database\Listing_Maps_DB;

class Country_Map extends Widget_Base
{

    public function get_name()
    {
        return 'mw_country_map';
    }

    public function get_title()
    {
        return __('MW Country Map', 'mwew');
    }

    public function get_icon()
    {
        return 'eicon-map-pin';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {

        // Main Settings
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Settings', 'mwew'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'section_title',
            [
                'label' => __('Section Title', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Section Title', 'mwew'),
            ]
        );

        $this->add_control(
            'section_title_desktop',
            [
                'label' => __('Hide On Desktop', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => false,
            ]
        );

         $this->add_control(
            'section_title_tablet',
            [
                'label' => __('Hide On Tablet', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => false,
            ]
        );

        $this->add_control(
            'section_title_mobile',
            [
                'label' => __('Hide On Mobile', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => false,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label' => __('Tab Title', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Tab Title', 'mwew'),
            ]
        );
        
        $repeater->add_control(
            'map_id',
            [
                'label' => __('Map ID', 'mwew'),
                'type' => Controls_Manager::SELECT,
                'options' => Map_Repo::get_map_id_title(),
                'default' => '',
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => __('Title', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => __('Description', 'mwew'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '',
            ]
        );

        $this->add_control(
            'tabs_list',
            [
                'label' => __('Tabs', 'mwew'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ tab_title }}}',
            ]
        );

        $this->add_control(
            'post_count',
            [
                'label' => __('Number of Posts', 'mwew'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4,
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Dots?', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label' => __('Show Navigation Arrows?', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'items_to_show',
            [
                'label' => __('Items to Show (Desktop)', 'mwew'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 6
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay?', 'mwew'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay_timeout',
            [
                'label' => __('Autoplay Delay (ms)', 'mwew'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4000,
            ]
        );

        $this->end_controls_section();

        // -------------------
        // Section Title Style
        // -------------------

        


        $this->start_controls_section(
            'style_section_section_title',
            [
                'label' => __('Section Title Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => __( 'Alignment', 'mwew' ),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'mwew' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'mwew' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'mwew' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __( 'Justify', 'mwew' ),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .mw-country-map-section-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'section_title_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-country-map-section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .mw-country-map-section-title',
            ]
        );

        $this->end_controls_section();

        // -------------------
        // Title Style
        // -------------------
        $this->start_controls_section(
            'style_section_title',
            [
                'label' => __('Title Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-map-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .mw-bubble-map-title',
            ]
        );

        $this->end_controls_section();

        // -------------------
        // Description Style
        // -------------------
        $this->start_controls_section(
            'style_section_description',
            [
                'label' => __('Description Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-map-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .mw-bubble-map-description',
            ]
        );

        $this->end_controls_section();

        // -------------------
        // Button Style
        // -------------------
        $this->start_controls_section(
            'section_tab_buttons_style',
            [
                'label' => __('Tab Buttons Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_button_styles');

        // -----------------------
        // Normal (inactive) tab
        // -----------------------
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => __('Normal', 'mwew'),
            ]
        );

        $this->add_control(
            'tab_button_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_button_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // -----------------------
        // Hover state
        // -----------------------
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __('Hover', 'mwew'),
            ]
        );

        $this->add_control(
            'tab_button_hover_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_button_hover_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // -----------------------
        // Active state
        // -----------------------
        $this->start_controls_tab(
            'tab_button_active',
            [
                'label' => __('Active', 'mwew'),
            ]
        );

        $this->add_control(
            'tab_button_active_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tab_button_active_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .mw-bubble-tab-button.active::after' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .mw-bubble-tab-button.active::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .mw-bubble-tab-button',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'mwew'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                    '%'  => ['min' => 0, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'mwew'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-tab-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .mw-bubble-tab-button',
            ]
        );

        $this->end_controls_section();

        // -------------------
        // Carousel Style
        // -------------------
        $this->start_controls_section(
            'style_section_carousel',
            [
                'label' => __('Carousel Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'carousel_max_width',
            [
                'label' => __('Max Width', 'mwew'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1920,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1140,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'carousel_title_typography',
                'label' => __('Title Typography', 'mwew'),
                'selector' => '{{WRAPPER}} h3.mw-title',
            ]
        );
         $this->add_control(
            'carousel_title_color',
            [
                'label' => __('Title Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} h3.mw-title' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'carousel_tags_typography',
                'label' => __('Tag Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .mw-feature',
            ]
        );

        // Dots background
        $this->add_control(
            'carousel_dots_bg_color',
            [
                'label' => __('Dots Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-items .owl-dots button.owl-dot' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        // Nav icon color
        $this->add_control(
            'carousel_nav_icon_color',
            [
                'label' => __('Nav Icon Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-items .owl-nav button.owl-prev' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .mw-bubble-carousel-items .owl-nav button.owl-next' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Nav background color
        $this->add_control(
            'carousel_nav_bg_color',
            [
                'label' => __('Nav Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-items .owl-nav button.owl-prev' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .mw-bubble-carousel-items .owl-nav button.owl-next' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'carousel_item_bg_color',
            [
                'label' => __('Item Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-items .item.listing-style' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'carousel_section_bg_color',
            [
                'label' => __('Carousel Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'carousel_item_button_border',
                'selector' => '{{WRAPPER}} .mw-bubble-carousel-items .item.listing-style',
            ]
        );

        $this->add_control(
            'carousel_item_border_radius',
            [
                'label' => __('Item Border Radius', 'mwew'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                    '%'  => ['min' => 0, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-carousel-items .item.listing-style' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );



        $this->end_controls_section();

        // -------------------
        // Markup Style Style
        // -------------------
        $this->start_controls_section(
            'style_marker',
            [
                'label' => __('Marker Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'marker_color',
            [
                'label' => __('Marker Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .mw-bubble-region svg.marker' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_section();
    }

    public function get_style_depends() {
	    return ['mw-country-map'];
	}

	public function get_script_depends() {
	    return ['mw-country-map'];
	}


    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $section_title = $settings['section_title'];
        $post_count = $settings['post_count'];
        $autoplay = $settings['autoplay'] === 'yes' ? 'true' : 'false';
        $show_nav = $settings['show_nav'] === 'yes' ? 'true' : 'false';
        $show_dots = $settings['show_dots'] === 'yes' ? 'true' : 'false';
        $autoplay_timeout = $settings['autoplay_timeout'];
        $items_to_show = $settings['items_to_show'];

        $tabs = $settings['tabs_list'];
        if (empty($tabs)) return;


        $classes = '';

        if ( $settings['section_title_desktop'] === 'yes' ) {
            $classes .= ' elementor-hidden-desktop';
        }
        if ( $settings['section_title_tablet'] === 'yes' ) {
            $classes .= ' elementor-hidden-tablet';
        }
        if ( $settings['section_title_mobile'] === 'yes' ) {
            $classes .= ' elementor-hidden-phone';
        }



        echo '<div class="mw-bubble-tent-finder">';



        // Tab Contents
        echo '<div class="mw-bubble-tab-contents">';
        if(!empty($section_title)){
            echo '<div class="mw-country-map-section-title '. $classes .'">' . $section_title . '</div>';
        }
        
        foreach ($tabs as $index => $tab) {
            echo '<div class="mw-bubble-tab-content" data-index="' . $index . '" style="' . ($index === 0 ? '' : 'display:none;') . '">';

            // Region Section
            echo '<div class="mw-bubble-region">';
            // Left Text
            echo '<div class="mw-bubble-left">';
            echo '<h3 class="mw-bubble-map-title">' . wp_kses_post($tab['title']) . '</h3>';
            echo '<div class="mw-bubble-map-description">' . wp_kses_post($tab['description']) . '</div>';
            echo '</div>';

            // Right Map
            echo '<div class="mw-bubble-right">';
            if (!empty($tab['map_id'])) {
                $map = Listing_Maps_DB::get_by_id($tab['map_id']);
                $region = Map_Repo::get_region_by_id($map['region_id']);
                $location_info = Map_Repo::get_listing_info_by_id($tab['map_id']);
                if (!empty($region['image'])) {
                    echo '<figure id="mw-bubble-map-' . $index . '" class="mw-bubble-map" data-listing-info="' . esc_attr(wp_json_encode($location_info)) . '" data-map-data="' . esc_attr(wp_json_encode($map['map_data'])) . '">
                                <img src="' . esc_url($region['image']) . '" alt="' . esc_attr($region['name']) . '" />
                            </figure>';
                    echo '<script>
                        jQuery(document).ready(function($) {
                            setTimeout(function() { 
                                render_map(0);
                            }, 300); // delay first render a little

                            $(".mw-bubble-tab-button").on("click", function() {
                                var index = $(this).data("index");

                                // Update active button
                                $(".mw-bubble-tab-button").removeClass("active");
                                $(".mw-bubble-tab-button[data-index=\'" + index + "\']").addClass("active");

                                $("html, body").animate({
                                  scrollTop: $(".mw-bubble-tent-finder").offset().top
                                }, 300);

                                // Show the correct tab content
                                $(".mw-bubble-tab-content").hide();
                                $(".mw-bubble-tab-content[data-index=\'" + index + "\']").show();


                                // Call your map render function
                                if (typeof render_map === "function") {
                                  render_map(index);
                                }
                                
                            });


                            function render_map(index) {
                                var $mapDiv = $("#mw-bubble-map-" + index);
                                var mapData = $mapDiv.data("map-data");
                                var listingData = $mapDiv.data("listing-info");
                                if (typeof mapData === "undefined" || typeof listingData === "undefined") {
                                    console.warn("No map data found for tab " + index);
                                    return;
                                }
                                const mapMarkerManager = new MapMarkerManager(mapData, listingData, index);
                            }
                        });
                    </script>';
                }
            }
            // Tab Buttons
            if(count($tabs) > 1):
                echo '<div class="mw-bubble-tabs">';
                foreach ($tabs as $index => $tab) {
                    echo '<button class="mw-bubble-tab-button' . ($index === 0 ? ' active' : '') . '" data-index="' . $index . '">' . esc_html($tab['tab_title']) . '</button>';
                }
                echo '</div>';
            endif;
            echo '</div>';
            echo '<template id="tooltip-template">
                    <div class="mw-tooltip">
                        <a href="" class="mw-tooltip-url">
                            <div class="mw-tooltip-content">
                                <!-- Price badge -->
                                <div class="mw-tooltip-price">
                                    <span class="price"></span>
                                    <small class="unit"></small>
                                </div>

                                <!-- Image preview (optional) -->
                                <div class="mw-tooltip-image">
                                    <img class="image" src="" alt="Bubble Tent Preview" />
                                </div>

                                <div class="mw-tooltip-content-wrap">
                                    <!-- Name + Location -->
                                    <div class="mw-tooltip-text">
                                        <img src="' . MWEW_PATH_URL . 'assets/images/Location.svg" />
                                        <div>
                                            <strong class="name"></strong>
                                            <span class="location"></span>
                                        </div>
                                    </div>

                                    <!-- Date range button style -->
                                    <div class="mw-tooltip-date">
                                        <img src="' . MWEW_PATH_URL . 'assets/images/Calendar.svg" />
                                        <span class="date-range"></span>
                                    </div>
                                </div>
                            </div>
                            
                        </a>
                    </div>
                </template>';

            echo '</div>'; // .mw-bubble-region

            // Carousel Section
            echo '<div class="mw-bubble-carousel">';
            $query = new \WP_Query([
                    'post_type' => 'listing',
                    'posts_per_page' => $post_count,
                    'post_status'    => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => 'region',
                            'field' => 'term_id',
                            'terms' => $map['region_id'],
                        ],
                    ],
                ]);

                if ($query->have_posts()) {
                    echo '<div class="mw-bubble-carousel-wrapper">';
                    echo '<div class="owl-carousel mw-bubble-carousel-items">';

                    while ($query->have_posts()) {
                        $query->the_post();
                        echo '<div class="item listing-style">';

                            echo '<a href="' . esc_url(get_the_permalink()) . '" class="mw-item-link">';

                                if ( has_post_thumbnail() ) {
                                    $post_img = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                                } else {
                                    if ( class_exists( '\Elementor\Utils' ) ) {
                                        $post_img = \Elementor\Utils::get_placeholder_image_src();
                                    } else {
                                        $post_img = ELEMENTOR_ASSETS_URL . 'images/placeholder.png';
                                    }
                                }


                                if ($post_img) {
                                    echo '<div class="mw-thumb" style="background-image: url(' . esc_url($post_img) . ');">';
                                    
                                    $highlight_features = get_field('highlight_features', get_the_ID());

                                    if ($highlight_features) {
                                        if (is_array($highlight_features)) {
                                            foreach ($highlight_features as $feature_post) {
                                                echo '<span class="mw-feature">' . esc_html(get_the_title($feature_post)) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="mw-feature">' . esc_html(get_the_title($highlight_features)) . '</span>';
                                        }
                                    }
                                    
                                    echo '</div>';
                                }
                                echo '<div class="mw-item-inner">';
                                    echo '<img src="'. MWEW_PATH_URL .'/assets/images/Location.svg"> <h3 class="mw-title">' . get_the_title() . '</h3>';
                                echo '</div>';

                            echo '</a>';

                        echo '</div>';
                    }


                    echo '</div>'; // .owl-carousel
                    echo '</div>'; // .mw-bubble-carousel-wrapper

                    wp_reset_postdata();

                    // Add JS for both carousel + tabs
                    echo '<script>
                            jQuery(document).ready(function($) {
                                $(".mw-bubble-carousel-items").owlCarousel({
                                    loop: true,
                                    margin: 20,
                                    nav: ' . $show_nav . ',
                                    dots: ' . $show_dots . ',
                                    autoplay: ' . $autoplay . ',
                                    autoplayTimeout: ' . $autoplay_timeout . ',
                                    slideBy: 1,
                                    responsive: {
                                        0: { items: 1 },
                                        768: { items: 2 },
                                        1024: { items: ' . $items_to_show . ' }
                                    }
                                });
                            });
                        </script>';
                }
            echo '</div>'; // .mw-bubble-carousel

            echo '</div>'; // .mw-bubble-tab-content
        }
        echo '</div>'; // .mw-bubble-tab-contents
        echo '</div>'; // .mw-bubble-tent-finder
    }


    public function _content_template()
    {
        // No live preview in the editor
    }
}
