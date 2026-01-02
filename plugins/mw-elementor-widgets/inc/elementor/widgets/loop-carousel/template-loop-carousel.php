<?php
namespace MWEW\Inc\Elementor\Widgets\Loop_Carousel;
use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Plugin;
use \ElementorPro\Modules\QueryControl\Controls\Template_Query;
use \ElementorPro\Modules\QueryControl\Module as QueryControlModule;
use \ElementorPro\Modules\LoopBuilder\Documents\Loop as LoopDocument;
use \Elementor\Core\Base\Document;

class Template_Loop_Carousel extends Widget_Base {

    public function get_name() {
        return 'mw_loop_carousel';
    }

    public function get_title() {
        return __('MW Loop Carousel', 'mwew');
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Content Controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Settings', 'mwew'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
    
        $this->add_control(
			'template_id',
			[
				'label' => esc_html__( 'Choose a templates', 'mwew' ),
				'type' => Template_Query::CONTROL_ID,
				'label_block' => true,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_LIBRARY_TEMPLATE,
					'query' => [
						'post_status' => Document::STATUS_PUBLISH,
						'meta_query' => [
							[
								'key' => Document::TYPE_META_KEY,
								'value' => LoopDocument::get_type(),
								'compare' => 'IN',
							],
						],
					],
				],
				'actions' => [
					'new' => [
						'visible' => true,
						'document_config' => [
							'type' => LoopDocument::get_type(),
						],
					],
					'edit' => [
						'visible' => true,
					],
				],
				'frontend_available' => true,
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
            'section_title',
            [
                'label' => __('Section Title', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Bubble Tent Blog Artikel', 'mwew'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'mwew'),
                'type' => Controls_Manager::TEXT,
                'default' => __('View All', 'mwew'),
            ]
        );
    
        $this->add_control(
            'button_link',
            [
                'label' => __('Button Link (View All)', 'mwew'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'mwew'),
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
    
        // Title Style
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
                'label' => __('Title Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .template-loop-title' => 'color: {{VALUE}};',
                ],
            ]
        );
    
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .template-loop-title',
            ]
        );
    
        $this->end_controls_section();
    
        $this->start_controls_section(
            'style_section_button',
            [
                'label' => __('Button Style', 'mwew'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->start_controls_tabs('tabs_button_style');
        
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => __('Normal', 'mwew'),
            ]
        );
        
        $this->add_control(
            'button_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .template-loop-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .template-loop-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __('Hover', 'mwew'),
            ]
        );
        
        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Text Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .template-loop-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_bg_color',
            [
                'label' => __('Background Color', 'mwew'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .template-loop-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .template-loop-button',
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
                    '{{WRAPPER}} .template-loop-button' => 'border-radius: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .template-loop-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'mwew'),
                'selector' => '{{WRAPPER}} .template-loop-button',
            ]
        );
        
        $this->end_controls_section();
        
    }
    

    protected function render() {
        $settings = $this->get_settings_for_display();

        $template_id = $settings['template_id'];
        $post_count = $settings['post_count'];
        $autoplay = $settings['autoplay'] === 'yes' ? 'true' : 'false';
        $show_nav = $settings['show_nav'] === 'yes' ? 'true' : 'false';
        $show_dots = $settings['show_dots'] === 'yes' ? 'true' : 'false';
        $autoplay_timeout = $settings['autoplay_timeout'];
        $items_to_show = $settings['items_to_show'];
        wp_enqueue_style('template-loop-carousel-style', MWEW_PATH_URL . 'inc/elementor/widgets/loop-carousel/template-loop-carousel.css');

        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $post_count
        ]);

        if ($query->have_posts()) {
            echo '<div class="template-loop-wrapper">';
                echo '<div class="template-loop-inner">';
                    if (!empty($settings['section_title'])) {
                        echo '<h2 class="template-loop-title">' . esc_html__($settings['section_title'], 'mwew') . '</h2>';
                    }
                    if (!empty($settings['button_link']['url'])) {
                        echo '<a href="' . esc_url($settings['button_link']['url']) . '" class="template-loop-button" target="_blank" rel="nofollow">'. esc_html__($settings['button_text'], 'mwew') .'</a>';
                    }
                echo '</div>';
            echo '<div class="owl-carousel template-loop-carousel">';

            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $current_post = $post;

                echo '<div class="item">';
                // Set the global post context
                setup_postdata($current_post);

                // Render the loop template with current post context
                echo Plugin::instance()->frontend->get_builder_content_for_display($template_id);

                echo '</div>';
            }

            echo '</div>'; // owl-carousel
            echo '</div>'; // wrapper

            wp_reset_postdata();

            echo '<script>
                jQuery(document).ready(function($) {
                    $(".template-loop-carousel").owlCarousel({
                        loop: true,
                        margin: 20,
                        nav: ' . $show_nav . ',
                        dots: ' . $show_dots . ',
                        autoplay: ' . $autoplay . ',
                        autoplayTimeout: ' . $autoplay_timeout . ',
                        responsive: {
                            0: {
                                items: 1
                            },
                            768: {
                                items: 2
                            },
                            1024: {
                                items: ' . $items_to_show . '
                            }
                        }
                    });
                });
            </script>';
        }
    }

    public function _content_template() {
        // No live preview in the editor
    }
}