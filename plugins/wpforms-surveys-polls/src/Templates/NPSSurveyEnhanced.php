<?php

namespace WPFormsSurveys\Templates;

use WPForms_Template;

/**
 * NPS Survey Enhanced template.
 *
 * @since 1.2.0
 */
class NPSSurveyEnhanced extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'NPS Survey Enhanced Form', 'wpforms-surveys-polls' );
		$this->slug        = 'nps-survey-enhanced';
		$this->description = esc_html__( 'Measure customer loyalty and find out exactly what they are thinking with this enhanced Net Promoter Score survey template.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = [
			'field_id' => '8',
			'fields'   => [
				1 => [
					'id'       => '1',
					'type'     => 'net_promoter_score',
					'label'    => esc_html__( 'How likely are you to recommend us to a friend or colleague?', 'wpforms-surveys-polls' ),
					'required' => '1',
					'style'    => 'modern',
					'size'     => 'large',
				],
				2 => [
					'id'                => '2',
					'type'              => 'textarea',
					'label'             => esc_html__( 'How did we disappoint you and what can we do to make things right?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => [
						0 => [
							0 => [
								'field'    => '1',
								'operator' => '<',
								'value'    => '7',
							],
						],
					],
				],
				4 => [
					'id'                => '4',
					'type'              => 'textarea',
					'label'             => esc_html__( 'What could we do to improve?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => [
						0 => [
							0 => [
								'field'    => '1',
								'operator' => '>',
								'value'    => '6',
							],
							1 => [
								'field'    => '1',
								'operator' => '<',
								'value'    => '9',
							],
						],
					],
				],
				6 => [
					'id'                => '6',
					'type'              => 'textarea',
					'label'             => esc_html__( 'What do you like most about us?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => [
						0 => [
							0 => [
								'field'    => '1',
								'operator' => '>',
								'value'    => '8',
							],
						],
					],
				],
				7 => [
					'id'                => '7',
					'type'              => 'radio',
					'label'             => esc_html__( 'Would you be willing to provide us a testimonial?', 'wpforms-surveys-polls' ),
					'choices'           => [
						1 => [
							'label' => esc_html__( 'Yes', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						2 => [
							'label' => esc_html__( 'No', 'wpforms-surveys-polls' ),
							'value' => '',
						],
					],
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => [
						0 => [
							0 => [
								'field'    => '1',
								'operator' => '>',
								'value'    => '8',
							],
						],
					],
				],
				5 => [
					'id'                => '5',
					'type'              => 'email',
					'label'             => esc_html__( 'What is your email address?', 'wpforms-surveys-polls' ),
					'description'       => esc_html__( 'We\'ll use this to get in touch with you, so we can make things right - Thanks for giving us a chance 🙂', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => [
						0 => [
							0 => [
								'field'    => '1',
								'operator' => '<',
								'value'    => '9',
							],
						],
						1 => [
							0 => [
								'field'    => '7',
								'operator' => '==',
								'value'    => '1',
							],
						],
					],
				],
			],
			'settings' => [
				'submit_text'                 => esc_html__( 'Submit', 'wpforms-surveys-polls' ),
				'antispam_v3'                 => '1',
				'ajax_submit'                 => '1',
				'confirmation_message_scroll' => '1',
				'submit_text_processing'      => esc_html__( 'Sending...', 'wpforms-surveys-polls' ),
				'survey_enable'               => '1',
			],
			'meta'     => [
				'template' => $this->slug,
			],
		];
	}
}
