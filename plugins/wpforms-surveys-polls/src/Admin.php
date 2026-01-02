<?php

namespace WPFormsSurveys;

use WPForms_Builder;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * Various admin functionality.
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Handle name for wp_register_styles handle.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	private const HANDLE = 'wpforms-surveys-polls';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	private function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.0
	 */
	private function hooks(): void {

		// Add Survey toggle setting to select core fields.
		add_action( 'wpforms_field_options_top_advanced-options', [ $this, 'field_survey_toggle' ], 10, 2 );

		// Add a result link to the forms' overview table.
		add_filter( 'wpforms_overview_row_actions', [ $this, 'form_list_row_actions' ], 10, 2 );

		// Register form builder settings area.
		add_filter( 'wpforms_builder_settings_sections', [ $this, 'builder_settings_register' ], 20, 2 );

		// Form builder settings content.
		add_action( 'wpforms_form_settings_panel_content', [ $this, 'builder_settings_content' ], 20, 2 );

		// Set editor style for block type editor. Must run at 20 in add-ons.
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_args' ], 20, 2 );

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues_before', [ $this, 'admin_builder_enqueues' ], 20 );

		// Format the Likert Scale in single entry page for better readability.
		add_filter( 'wpforms_entry_single_data', [ $this, 'format_likert_scale_entry' ] );
	}

	/**
	 * Format the Likert Scale entry.
	 *
	 * @since 1.9.0
	 *
	 * @param array $fields Submitted entry data on fields.
	 *
	 * @return array
	 */
	public function format_likert_scale_entry( $fields ) {

		foreach ( $fields as $key => $field ) {

			if ( ! isset( $field['type'] ) || $field['type'] !== 'likert_scale' ) {
				continue;
			}

			$fields[ $key ]['value'] = Helpers::format_likert_scale_entry( $field['value'], "\n" );
		}

		return $fields;
	}

	/**
	 * Enqueue for the admin form builder.
	 *
	 * @since 1.3.3
	 */
	public function admin_builder_enqueues(): void {

		// Localize data.
		wp_localize_script(
			'wpforms-survey-builder',
			'wpforms_surveys_polls',
			[
				'alert_disable_entries' => esc_html__( "You've just turned off storing entry information in WordPress. Surveys and Polls addon requires entries to be stored, otherwise, it won't be able to process the data and display you the results.", 'wpforms-surveys-polls' ),
				'alert_enable_entries'  => esc_html__( 'This feature can\'t currently be used because entry storage is disabled for this form. Please go to Settings > General and uncheck the "Disable storing entry information in WordPress" option. Then, try enabling this feature again.', 'wpforms-surveys-polls' ),
			]
		);
	}

	/**
	 * Add setting to core fields to allow enabling survey tracking/reporting.
	 *
	 * This setting gets added to single line text, paragraph text, dropdown,
	 * multiple choice, checkbox and rating fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field    Field settings.
	 * @param object $instance Field base class instance.
	 */
	public function field_survey_toggle( $field, $instance ): void {

		// Limit to our specific field types.
		if ( ! in_array( $field['type'], Reporting\Fields::get_survey_field_types(), true ) ) {
			return;
		}

		$builder = WPForms_Builder::instance();

		// Create checkbox setting.
		$instance->field_element(
			'row',
			$field,
			[
				'slug'    => 'survey',
				'content' => $instance->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'survey',
						'value'   => isset( $field['survey'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Enable Survey Reporting', 'wpforms-surveys-polls' ),
						'tooltip' => esc_html__( 'Check this option to track user input and include in survey reporting.', 'wpforms-surveys-polls' ),
					],
					false
				),
				'class'   => ! empty( $builder->form_data['settings']['survey_enable'] ) ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * On the forms' overview table add a link to go to the survey results page.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $actions Table row actions.
	 * @param object $form    Form object.
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 */
	public function form_list_row_actions( $actions, $form ) {

		if ( ! Reporting\Forms::form_has_survey( $form ) ) {
			return $actions;
		}

		if ( ! wpforms_current_user_can( 'view_entries_form_single', $form->ID ) ) {
			return $actions;
		}

		// Action link to view survey results.
		$action = [
			'survey' => sprintf(
				'<a href="%s" title="%s">%s</a>',
				add_query_arg(
					[
						'page'    => 'wpforms-entries',
						'view'    => 'survey',
						'form_id' => $form->ID,
					],
					admin_url( 'admin.php' )
				),
				esc_attr__( 'View Survey Results', 'wpforms-surveys-polls' ),
				esc_html__( 'Survey Results', 'wpforms-surveys-polls' )
			),
		];

		return wpforms_array_insert( $actions, $action, 'entries' );
	}

	/**
	 * Surveys and Polls form builder register settings area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections  Settings area sections.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function builder_settings_register( $sections, $form_data ) {

		$sections['surveys_polls'] = esc_html__( 'Surveys and Polls', 'wpforms-surveys-polls' );

		return $sections;
	}

	/**
	 * Surveys and Polls form builder settings content.
	 *
	 * @since 1.0.0
	 *
	 * @param object $instance Settings panel instance.
	 *
	 * @noinspection HtmlUnknownTarget*/
	public function builder_settings_content( $instance ): void {

		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-surveys_polls">';

			printf(
				'<div class="wpforms-panel-content-section-title">%s</div>',
				esc_html__( 'Surveys and Polls', 'wpforms-surveys-polls' )
			);

			$survey_note = sprintf(
				wp_kses( /* translators: %s - WPForms.com documentation page URL. */
					__( 'Survey Reporting for all supported fields will be turned on. For more details and advanced survey options visit our <a href="%s" target="_blank" rel="noopener noreferrer">Surveys documentation</a>.', 'wpforms-surveys-polls' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-surveys-and-polls-addon/#enable-survey', 'Builder Settings', 'Survey Documentation' ) )
			);

			wpforms_panel_field(
				'toggle',
				'settings',
				'survey_enable',
				$instance->form_data,
				esc_html__( 'Enable Survey Reporting', 'wpforms-surveys-polls' ),
				[
					'after' => '<p class="note">' . $survey_note . '</p>',
				]
			);

			$poll_note = sprintf(
				wp_kses( /* translators: %s - WPForms.com documentation page URL. */
					__( 'Poll results for all Checkbox, Multiple Choice, and Dropdown fields will automatically display to users after they submit the form. For more details and advanced poll options visit our <a href="%s" target="_blank" rel="noopener noreferrer">Polls documentation</a>.', 'wpforms-surveys-polls' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-surveys-and-polls-addon/#display-poll', 'Builder Settings', 'Polls Documentation' ) )
			);

			wpforms_panel_field(
				'toggle',
				'settings',
				'poll_enable',
				$instance->form_data,
				esc_html__( 'Enable Poll Results', 'wpforms-surveys-polls' ),
				[
					'after' => '<p class="note">' . $poll_note . '</p>',
				]
			);

		echo '</div>';
	}

	/**
	 * Load enqueues for the Gutenberg editor.
	 *
	 * @since 1.2.0
	 * @deprecated 1.13.0
	 */
	public function gutenberg_enqueues(): void {

		_deprecated_function( __METHOD__, '1.13.0 of the WPForms Surveys and Polls addon.' );

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			wpforms_surveys_polls()->url . "assets/css/wpforms-surveys-polls{$min}.css",
			[],
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}

	/**
	 * Set editor style handle for block type editor.
	 *
	 * @see FileUploadField::register_block_type_args
	 *
	 * @since 1.8.0
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type Block type name including namespace.
	 */
	public function register_block_type_args( $args, $block_type ) {

		if ( $block_type !== 'wpforms/form-selector' ) {
			return $args;
		}

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_register_style(
			self::HANDLE,
			wpforms_surveys_polls()->url . "assets/css/wpforms-surveys-polls{$min}.css",
			[ $args['editor_style'] ],
			WPFORMS_SURVEYS_POLLS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}
}
