<?php

namespace WPFormsSurveys\Templates;

use WPForms_Template;

/**
 * NPS Survey Simple template.
 *
 * @since 1.2.0
 */
class NPSSurveySimple extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'NPS Survey Simple Form', 'wpforms-surveys-polls' );
		$this->slug        = 'nps-survey-simple';
		$this->description = esc_html__( 'Find out if your clients or customers would recommend you to someone else with this basic Net Promoter Score survey template.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = [
			'field_id' => '4',
			'fields'   => [
				1 => [
					'id'       => '1',
					'type'     => 'net_promoter_score',
					'label'    => esc_html__( 'How likely are you to recommend us to a friend or colleague?', 'wpforms-surveys-polls' ),
					'required' => '1',
					'survey'   => '1',
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
				3 => [
					'id'                => '3',
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
