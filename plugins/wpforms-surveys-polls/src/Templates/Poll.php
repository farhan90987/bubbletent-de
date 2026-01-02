<?php

namespace WPFormsSurveys\Templates;

use WPForms_Template;

/**
 * Poll form template.
 *
 * @since 1.0.0
 */
class Poll extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'Poll Form', 'wpforms-surveys-polls' );
		$this->slug        = 'poll';
		$this->description = esc_html__( 'Ask visitors a question and display the results after they provide an answer.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = [
			'field_id' => '2',
			'fields'   => [
				'1' => [
					'id'       => '1',
					'type'     => 'radio',
					'label'    => esc_html__( 'What is your favorite color?', 'wpforms-surveys-polls' ),
					'required' => '1',
					'size'     => 'medium',
					'choices'  => [
						'1' => [
							'label' => esc_html__( 'Red', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						'2' => [
							'label' => esc_html__( 'Green', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						'3' => [
							'label' => esc_html__( 'Blue', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						'4' => [
							'label' => esc_html__( 'Orange', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						'5' => [
							'label' => esc_html__( 'Purple', 'wpforms-surveys-polls' ),
							'value' => '',
						],
						'6' => [
							'label' => esc_html__( 'Other', 'wpforms-surveys-polls' ),
							'value' => '',
						],
					],
				],
			],
			'settings' => [
				'submit_text'                 => esc_html__( 'Vote', 'wpforms-surveys-polls' ),
				'antispam_v3'                 => '1',
				'ajax_submit'                 => '1',
				'confirmation_message_scroll' => '1',
				'confirmation_message'        => esc_html__( 'Thanks for voting! Results are below.', 'wpforms-surveys-polls' ),
				'submit_text_processing'      => esc_html__( 'Sending...', 'wpforms-surveys-polls' ),
				'survey_enable'               => '1',
				'poll_enable'                 => '1',
			],
			'meta'     => [
				'template' => $this->slug,
			],
		];
	}
}
