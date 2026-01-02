<?php

namespace WPFormsSurveys\Reporting;

use WPForms_Entries_List;
use WPFormsSurveys\Helpers;

/**
 * Survey reporting admin page.
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * All the forms.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $forms;

	/**
	 * Current form ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $form_id;

	/**
	 * Current form data array.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * Total number of entries in the current form.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $entry_count;

	/**
	 * Field IDs for the fields with survey reporting enabled.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $field_ids;

	/**
	 * Field ID for the specific survey field the user has selected to display
	 * in the survey preview area.
	 *
	 * If no specific field has been defined, it will be the first survey field
	 * in the form.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $field_id;

	/**
	 * If we are viewing the entries list table or the survey report.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $view = false;

	/**
	 * If we are viewing the survey report printable template.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $print = false;

	/**
	 * Abort. Bail on proceeding to process the page.
	 *
	 * @since 1.8.0
	 *
	 * @var bool
	 */
	private $abort = false;

	/**
	 * Various URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $urls = [];

	/**
	 * Construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Fire init.
		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Delete current cache when entry edited.
		if ( wpforms()->obj( 'entries_edit' )->is_admin_entry_editing_ajax() ) {
			add_action( 'wpforms_pro_admin_entries_edit_submit_completed', [ $this, 'entries_edit_submit_clear_cache' ], 10, 4 );
		}

		// Check page and view, determine if the user is viewing the survey reporting page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$current_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$current_view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : '';
		$is_deleted   = isset( $_GET['deleted'] ) ? sanitize_key( $_GET['deleted'] ) : '';
		$is_restored  = isset( $_GET['restored'] ) ? sanitize_key( $_GET['restored'] ) : '';
		$is_trashed   = isset( $_GET['trashed'] ) ? sanitize_key( $_GET['trashed'] ) : '';

		$this->view  = in_array( $current_view, [ 'survey', 'list' ], true ) ? $current_view : false;
		$this->print = ! empty( $_GET['print'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $current_page !== 'wpforms-entries' || ! $this->view ) {
			return;
		}

		// Survey results page processing and setup.
		$this->setup();

		// If there is no form ID, bail.
		if ( ! $this->form_id ) {
			return;
		}

		// Clear cache when entry is deleted, trashed or restored.
		if ( $is_deleted || $is_restored || $is_trashed ) {
			$this->entries_delete_clear_cache();
		}

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
		/**
		 * Fire when everything is ready for initializing addon page.
		 *
		 * @since 1.0.0
		 *
		 * @param Admin $instance Reporting admin page instance.
		 */
		do_action( 'wpforms_survey_report_init', $this );
		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		if ( $this->view === 'list' ) {

			// Entry List survey preview area.
			add_action( 'wpforms_entry_list_title', [ $this, 'entry_list_preview' ], 12, 2 );
			add_filter( 'wpforms_entry_table_column_value', [ $this, 'format_likert_scale_value' ], 10, 4 );

		} elseif ( $this->view === 'survey' ) {

			// Remove Screen Options tab from admin area header.
			add_filter( 'screen_options_show_screen', '__return_false' );

			// Survey results page output.
			add_action( 'wpforms_admin_page', [ $this, 'report_page' ] );
		}

		// No necessity to proceed.
		if ( $this->abort ) {
			return;
		}

		// Load the Underscores templates displaying question results.
		add_action( 'admin_print_scripts', [ $this, 'question_template' ] );

		// Enqueues.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );

		// Report Print Page.
		add_action( 'current_screen', [ $this, 'report_print_page' ] );
	}

	/**
	 * Format the Likert Scale entries in Entries list view
	 * to a more readable format.
	 *
	 * @since 1.9.0
	 *
	 * @param string $value       Value.
	 * @param object $entry       Current entry data.
	 * @param string $column_name Current column name.
	 * @param string $field_type  Field type.
	 *
	 * @return string
	 */
	public function format_likert_scale_value( $value, $entry, $column_name, $field_type ) {

		if ( $field_type !== 'likert_scale' ) {
			return $value;
		}

		return Helpers::format_likert_scale_entry( $value, '<br />' );
	}

	/**
	 * Setup and process form data.
	 *
	 * @since 1.0.0
	 */
	public function setup() {

		// Fetch all forms, for the form dropdown toggle nav. We only need this
		// for the survey reporting page.
		if ( $this->view === 'survey' ) {
			$this->forms = wpforms()->obj( 'form' )->get(
				'',
				[
					'orderby' => 'ID',
					'order'   => 'ASC',
				]
			);
		}

		// Get current form ID.
		$this->form_id = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false; // phpcs:ignore

		// If there is no form ID, stop.
		if ( ! $this->form_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpforms-entries' ) );
			exit;
		}

		// Get current form details.
		$this->form_data = wpforms()->obj( 'form' )->get(
			$this->form_id,
			[
				'content_only' => true,
				'cap'          => 'view_entries_form_single',
			]
		);

		// Get number of current entries.
		$this->entry_count = wpforms()->obj( 'entry' )->get_entries(
			[
				'form_id' => $this->form_id,
			],
			true
		);

		// Various URLs needed.
		$this->urls = [
			'survey-report'       => add_query_arg(
				[
					'page'    => 'wpforms-entries',
					'view'    => 'survey',
					'form_id' => $this->form_id,
				],
				admin_url( 'admin.php' )
			),
			'survey-report-print' => add_query_arg(
				[
					'page'    => 'wpforms-entries',
					'view'    => 'survey',
					'form_id' => $this->form_id,
					'print'   => '1',
				],
				admin_url( 'admin.php' )
			),
			'form-edit'           => add_query_arg(
				[
					'page'    => 'wpforms-builder',
					'view'    => 'fields',
					'form_id' => $this->form_id,
				],
				admin_url( 'admin.php' )
			),
			'form-preview'        => add_query_arg(
				[
					'wpforms_form_preview' => $this->form_id,
				],
				home_url()
			),
			'entries-export'      => wp_nonce_url(
				add_query_arg(
					[
						'page'   => 'wpforms-tools',
						'view'   => 'export',
						'form'   => absint( $this->form_id ),
						'search' => ! empty( $_GET['search'] ) ? $_GET['search'] : [], // phpcs:ignore
						'date'   => ! empty( $_GET['date'] ) ? $_GET['date'] : [], // phpcs:ignore
					],
					admin_url( 'admin.php' )
				),
				'wpforms_entry_list_export'
			),
			'entries'             => add_query_arg(
				[
					'page'    => 'wpforms-entries',
					'view'    => 'list',
					'form_id' => $this->form_id,
				],
				admin_url( 'admin.php' )
			),
		];

		// Return earlier when the form is not found or have no entries.
		if (
			$this->view === 'survey'
			&& ( empty( $this->forms ) || ! $this->entry_count )
		) {
			wp_safe_redirect( $this->urls['entries'] );
			exit;
		}

		// Get details about fields with survey reporting enabled.
		$this->field_ids = Fields::get_survey_fields( $this->form_data, true );
		$this->field_id  = [];

		// For the entry list overview page, the survey preview only displays
		// a report for 1 field, so reflect this in the field IDs returned.
		if ( ! empty( $this->field_ids ) && ( 'list' === $this->view || 'survey' === $this->view && ! empty( $_GET['field_id'] ) ) ) { // phpcs:ignore

			$specific_id = false;

			if ( ! empty( $_GET['field_id'] ) ) { // phpcs:ignore

				// Show specific field.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$specific_id = absint( $_GET['field_id'] );

			} elseif ( ! empty( $this->form_data['meta']['survey_preview'] ) ) {

				// Check the form meta and see if the user as set a specific
				// field they want to use in the preview area.
				$specific_id = absint( $this->form_data['meta']['survey_preview'] );
			}

			if ( $specific_id && ! empty( $this->form_data['fields'][ $specific_id ] ) ) {
				$this->field_id = (array) $specific_id;
			} else {
				$this->field_id = (array) $this->field_ids[0];
			}
		}

		// Easter egg to delete current cache.
		if ( isset( $_GET['wpforms_surveys_polls_delete_cache'] ) && wpforms_current_user_can( 'view_entries_form_single', $this->form_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->entries_delete_clear_cache();
		}
	}

	/**
	 * Output entry list reporting preview area with a link to view the full
	 * survey reporting.
	 *
	 * @since 1.0.0
	 *
	 * @param array                $form_data    Form data and settings.
	 * @param WPForms_Entries_List $entries_list WPForms_Entries_List object.
	 */
	public function entry_list_preview( $form_data, $entries_list ) {

		// Check if the form has fields with survey reporting enabled. If not
		// do not display.
		if ( empty( $this->field_ids ) ) {
			return;
		}
		?>
		<div id="wpforms-survey-preview">
			<div id="wpforms-survey-report">
				<?php echo $this->display_loader( true ); // phpcs:ignore ?>
			</div>
			<div class="btn-wrap">
				<a href="<?php echo esc_url( $this->urls['survey-report'] ); ?>" class="view-results">
					<?php esc_html_e( 'View Survey Results', 'wpforms-surveys-polls' ); ?> <i class="fa fa-chevron-right" aria-hidden="true"></i>
				</a>
				<?php if ( count( $this->field_ids ) > 1 ) : ?>
					<span class="or"><?php esc_html_e( 'or', 'wpforms-surveys-polls' ); ?></span>
					<div class="choicesjs-select-wrap">
						<label for="wpforms-survey-preview-questions"><?php esc_html_e( 'View another question', 'wpforms-surveys-polls' ); ?></label>
						<select id="wpforms-survey-preview-questions" class="choicesjs-select" data-sorting="off">
							<?php
							foreach ( $this->field_ids as $field_id ) {
								printf(
									'<option value="%d" %s>%s</option>',
									absint( $field_id ),
									selected( $field_id, $this->field_id[0], false ),
									esc_html( $this->form_data['fields'][ $field_id ]['label'] )
								);
							}
							?>
						</select>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		// Check if the form has fields with survey reporting enabled. If not, do not proceed.
		if ( empty( $this->field_ids ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		/*
		 * JavaScript.
		 */
		wp_enqueue_script( 'wp-util' );

		// The PDF libraries are quite large, so we restrict exporting to the
		// full survey report view and don't load these assets with the
		// entry table view.
		if ( $this->view === 'survey' ) {
			wp_enqueue_script(
				'pdfmake',
				wpforms_surveys_polls()->url . 'assets/js/vendor/pdfmake.min.js',
				[],
				'0.1.35',
				true
			);

			wp_enqueue_script(
				'pdfmake-font',
				wpforms_surveys_polls()->url . 'assets/js/vendor/vfs_fonts.min.js',
				[],
				'0.1.35',
				true
			);
		}

		wp_enqueue_script(
			'wpforms-chart',
			WPFORMS_PLUGIN_URL . 'assets/lib/chart.min.js',
			[],
			'4.4.4',
			true
		);

		wp_enqueue_script(
			'randomColor',
			wpforms_surveys_polls()->url . "assets/js/vendor/randomColor{$min}.js",
			[],
			'0.5.2',
			false
		);

		wp_enqueue_script(
			'stupidtable',
			wpforms_surveys_polls()->url . "assets/js/vendor/stupidtable{$min}.js",
			[ 'jquery' ],
			'1.1.3',
			false
		);

		wp_enqueue_script(
			'wpforms-survey-reporting',
			wpforms_surveys_polls()->url . "assets/js/admin-survey-reporting{$min}.js",
			[ 'jquery', 'wpforms-chart', 'randomColor', 'stupidtable' ],
			WPFORMS_SURVEYS_POLLS_VERSION,
			false
		);

		wp_localize_script(
			'wpforms-survey-reporting',
			'wpforms_surveys',
			[
				'type'        => $this->view,
				'form_id'     => $this->form_id,
				'entry_count' => $this->entry_count,
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'field_ids'   => $this->view === 'survey' && empty( $_GET['field_id'] ) ? wp_json_encode( $this->field_ids ) : wp_json_encode( $this->field_id ),
				'field_id'    => ! empty( $this->field_id ) ? $this->field_id[0] : '',
				'field_nums'  => array_flip( $this->field_ids ),
				'loader'      => $this->display_loader( $this->view !== 'survey' ),
				'print'       => esc_url_raw( $this->urls['survey-report-print'] ),
				'cache'       => $this->get_report_cache_data(),
			]
		);

		// CSS.
		wp_enqueue_style(
			'wpforms-survey-reporting',
			wpforms_surveys_polls()->url . "assets/css/admin-survey-reporting{$min}.css",
			[],
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}

	/**
	 * Output the report cache data.
	 *
	 * @since 1.15.1
	 *
	 * @return array|bool
	 */
	private function get_report_cache_data() {

		// Output cache data if we have it, but provide a filter to disable survey report caching.
		$cache = false;

		/**
		 * Allow caching of survey report data.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $is_cache_enabled  Whether to cache survey report data.
		 * @param int  $form_id           Form ID.
		 */
		if ( (bool) apply_filters( 'wpforms_surveys_polls_report_caching', true, $this->form_id ) ) { // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$cache_key = $this->view === 'list' || ( $this->view === 'survey' && ! empty( $_GET['field_id'] ) )
				? "wpforms_survey_report_{$this->form_id}_{$this->entry_count}_{$this->field_id[0]}"
				: "wpforms_survey_report_{$this->form_id}_{$this->entry_count}";

			$cache = get_transient( $cache_key );
		}

		return $cache ? json_decode( $cache, true ) : false;
	}

	/**
	 * Display abort message if form no longer available.
	 *
	 * @since 1.8.0
	 * @deprecated 1.11.0
	 */
	public function display_abort_message() {

		_deprecated_function( __METHOD__, '1.11.0 of the WPForms Surveys and Polls addon' );
		?>
		<div id="wpforms-entries-list" class="wrap wpforms-admin-wrap">
			<h1 class="page-title">
				<?php esc_html_e( 'Entries', 'wpforms-surveys-polls' ); ?>
			</h1>
			<div class="wpforms-admin-content">
				<?php
				// Output empty state screen.
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render(
					'admin/empty-states/no-entries',
					[
						'message' => esc_html__( 'It looks like the form you are trying to access is no longer available.', 'wpforms-surveys-polls' ),
					],
					true
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Survey report page.
	 *
	 * @since 1.0.0
	 */
	public function report_page() {

		?>
		<div id="wpforms-entries-list" class="wrap wpforms-admin-wrap">
			<h1 class="page-title">
				<?php esc_html_e( 'Survey Results', 'wpforms-surveys-polls' ); ?>
				<a href="<?php echo esc_url( $this->urls['entries'] ); ?>" class="page-title-action wpforms-btn wpforms-btn-orange">
					<svg viewBox="0 0 16 14" class="page-title-action-icon">
						<path d="M16 6v2H4l4 4-1 2-7-7 7-7 1 2-4 4h12Z"/>
					</svg>
					<span class="page-title-action-text"><?php esc_html_e( 'Back to All Entries', 'wpforms-surveys-polls' ); ?></span>
				</a>
			</h1>
			<div class="wpforms-admin-content">
				<div class="form-details wpforms-clear">
					<span class="form-details-sub"><?php esc_html_e( 'Select Form', 'wpforms-surveys-polls' ); ?></span>
					<h3 class="form-details-title">
						<?php
						if ( ! empty( $this->form_data['settings']['form_title'] ) ) {
							echo esc_html( sanitize_text_field( $this->form_data['settings']['form_title'] ) );
						}
						$this->form_selector_html();
						?>
					</h3>
					<div class="form-details-actions">
					<?php if ( wpforms_current_user_can( 'edit_form_single', $this->form_id ) ) : ?>
						<a href="<?php echo esc_url( $this->urls['form-edit'] ); ?>" class="form-details-actions-edit">
							<span class="dashicons dashicons-edit"></span>
							<?php esc_html_e( 'Edit This Form', 'wpforms-surveys-polls' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( wpforms_current_user_can( 'view_form_single', $this->form_id ) ) : ?>
						<a href="<?php echo esc_url( $this->urls['form-preview'] ); ?>" class="form-details-actions-preview" target="_blank" rel="noopener">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Preview Form', 'wpforms-surveys-polls' ); ?>
						</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $this->urls['entries-export'] ); ?>" class="form-details-actions-export">
						<span class="dashicons dashicons-migrate"></span>
						<?php esc_html_e( 'Export All', 'wpforms-surveys-polls' ); ?>
					</a>
					<a href="<?php echo esc_url( $this->urls['survey-report-print'] ); ?>" class="form-details-print-survey-report">
						<i class="fa fa-print" aria-hidden="true"></i>
						<?php esc_html_e( 'Print Survey Report', 'wpforms-surveys-polls' ); ?>
					</a>
					</div>
				</div>
				<div id="wpforms-survey-report">
					<?php echo $this->display_loader(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display form selector HTML.
	 *
	 * @since 1.5.8
	 */
	protected function form_selector_html() {

		if ( ! wpforms_current_user_can( 'view_forms' ) ) {
			return;
		}

		if ( empty( $this->forms ) ) {
			return;
		}

		?>
		<div class="form-selector">
			<a href="#" title="<?php esc_attr_e( 'Open form selector', 'wpforms-surveys-polls' ); ?>" class="toggle dashicons dashicons-arrow-down-alt2"></a>
			<div class="form-list">
				<ul>
					<?php
					foreach ( $this->forms as $key => $form ) {
						$form_url = add_query_arg(
							[
								'page'    => 'wpforms-entries',
								'view'    => 'list',
								'form_id' => absint( $form->ID ),
							],
							admin_url( 'admin.php' )
						);

						echo '<li><a href="' . esc_url( $form_url ) . '">' . esc_html( $form->post_title ) . '</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Survey report printable page.
	 *
	 * @since 1.0.0
	 */
	public function report_print_page() {

		// Check if we should show the survey report print template.
		if ( ! $this->print ) {
			return;
		}

		?>
		<!doctype html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<title><?php esc_html_e( 'WPForms Survey Print Preview', 'wpforms-surveys-polls' ); ?> - <?php echo esc_html( sanitize_text_field( $this->form_data['settings']['form_title'] ) ); ?></title>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="robots" content="noindex,nofollow,noarchive">
			<?php
			do_action( 'admin_enqueue_scripts' );
			do_action( 'admin_print_scripts' );
			do_action( 'admin_head' );
			?>
		</head>
		<body id="wpforms-survey-print-preview">
			<h1 class="header">
				<?php echo esc_html( sanitize_text_field( $this->form_data['settings']['form_title'] ) ); ?>
				<div class="buttons">
					<button type="button" id="wpforms-survey-print-close"><?php esc_html_e( 'Close', 'wpforms-surveys-polls' ); ?></button>
					<button type="button" id="wpforms-survey-print"><?php esc_html_e( 'Print', 'wpforms-surveys-polls' ); ?></button>
				</div>
			</h1>
			<div id="wpforms-survey-report">
				<?php echo $this->display_loader( ! empty( $_GET['field_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php do_action( 'admin_print_footer_scripts' ); ?>
		</body>
		</html>
		<?php
		exit();
	}

	/**
	 * Output HTML markup for our loading animation indicator.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $single If we are loading a single field or all results.
	 *
	 * @return string
	 */
	public function display_loader( $single = false ) {

		ob_start();
		?>
		<div id="wpforms-survey-loading">
			<div class="loader">
				<div class="bar1"></div>
				<div class="bar2"></div>
				<div class="bar3"></div>
				<div class="bar4"></div>
				<div class="bar5"></div>
			</div>
			<?php if ( $single ) : ?>
				<div class="loader-msg-single"><?php esc_html_e( 'Calculating Field Results', 'wpforms-surveys-polls' ); ?></div>
			<?php else : ?>
				<div class="loader-msg">
					<?php esc_html_e( 'Calculating Survey Results', 'wpforms-surveys-polls' ); ?>
					<span><?php esc_html_e( 'We\'re crunching the numbers, this may take a minute.', 'wpforms-surveys-polls' ); ?></span>
				</div>
			<?php endif; ?>

		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Underscore templates displaying question results.
	 *
	 * @since 1.0.0
	 */
	public function question_template() {

		// Check if the form has fields with survey reporting enabled.
		// If not do not proceed.
		if ( empty( $this->field_ids ) ) {
			return;
		}
		?>
		<script type="text/html" id="tmpl-wpforms-question-results">
			<# var count = 1; #>
			<# _.each( data, function( fieldData, key ) {  #>
				<div class="question">
					<?php
					if ( $this->view === 'survey' && $this->print && empty( $_GET['field_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						echo '<button type="button" class="question-toggle" title="' . esc_attr__( 'Toggle displaying question', 'wpforms-surveys-polls' ) . '"><i class="fa fa-chevron-down" aria-hidden="true"></i></button>';
					}
					?>
					<div class="details">
						<div class="title-area">
							<h6><span class="q-num"><?php esc_html_e( 'Question', 'wpforms-surveys-polls' ); ?> <# print( ( wpforms_surveys.field_nums[ fieldData.id ] || 0 ) + 1 ); #></span> {{{ fieldData.badge }}}</h6>
							<h4>{{ fieldData.question }}</h4>
						</div>
						<# if ( ! _.isEmpty( fieldData.answers ) ) { #>
							<div class="actions" data-field-id="{{ fieldData.id }}">
								<div class="buttons">
									<# if ( _.contains( fieldData.chart.supports, 'line' ) ) { #>
										<button type="button" data-type="line" title="<?php esc_html_e( 'View line chart', 'wpforms-surveys-polls' ); ?>" class="chart-toggle <# if ( 'line' === fieldData.chart.default ) { print( ' current' ); } #>"><i class="fa fa-area-chart" aria-hidden="true"></i></button>
									<# } #>
									<# if ( _.contains( fieldData.chart.supports, 'pie' ) ) { #>
										<button type="button" data-type="pie" title="<?php esc_html_e( 'View pie chart', 'wpforms-surveys-polls' ); ?>" class="chart-toggle <# if ( 'pie' === fieldData.chart.default ) { print( ' current' ); } #>"><i class="fa fa-pie-chart" aria-hidden="true"></i></button>
									<# } #>
									<# if ( _.contains( fieldData.chart.supports, 'bar-h' ) ) { #>
										<button type="button" data-type="bar-h" title="<?php esc_html_e( 'View horizontal bar chart', 'wpforms-surveys-polls' ); ?>" class="chart-toggle <# if ( 'bar-h' === fieldData.chart.default ) { print( ' current' ); } #>"><i class="fa fa-align-left" aria-hidden="true"></i></button>
									<# } #>
									<# if ( _.contains( fieldData.chart.supports, 'bar' ) ) { #>
										<button type="button" data-type="bar" title="<?php esc_html_e( 'View bar chart', 'wpforms-surveys-polls' ); ?>" class="chart-toggle <# if ( 'bar' === fieldData.chart.default ) { print( ' current' ); } #>"><i class="fa fa-bar-chart" aria-hidden="true"></i></button>
									<# } #>
								</div>
								<?php if ( $this->view === 'survey' ) : ?>
									<div class="choicesjs-select-wrap">
										<select class="survey-chart-export" data-id="{{ fieldData.id }}" data-sorting="off">
											<option value="1" placeholder><?php esc_html_e( 'Export', 'wpforms-surveys-polls' ); ?></option>
											<# if ( ! _.isEmpty( fieldData.chart.supports ) ) { #>
												<option value="jpg"><?php esc_html_e( 'Save as JPG', 'wpforms-surveys-polls' ); ?></option>
												<option value="pdf"><?php esc_html_e( 'Save as PDF', 'wpforms-surveys-polls' ); ?></option>
											<# } #>
											<option value="print"><?php esc_html_e( 'Print', 'wpforms-surveys-polls' ); ?></option>
										</select>
									</div>
								<?php endif; ?>
							</div>
						<# } #>
					</div>
					<# if ( _.isEmpty( fieldData.answers ) ) { #>
						<div class="no-answers">
							<i class="fa fa-exclamation-circle" aria-hidden="true"></i>
							<p><?php esc_html_e( 'There are no answers to this question yet.', 'wpforms-surveys-polls' ); ?></p>
						</div>
					<# } else { #>
						<# if ( ! _.isEmpty( fieldData.chart.supports ) ) { #>
							<div class="chart-area {{ fieldData.type }}">
								<canvas id="chart-{{ fieldData.id }}"></canvas>
							</div>
							<div class="chart-area-hq">
								<canvas id="chart-{{ fieldData.id }}-hq" width="1200" height="600"></canvas>
								<a href="#" id="chart-{{ fieldData.id }}-download" download="chart-field-{{ fieldData.id }}.jpg"><?php esc_html_e( 'Download Chart', 'wpforms-surveys-polls' ); ?></a>
							</div>
							<# if ( 'net_promoter_score' === fieldData.type ) { #>
								<div class="table-wrap net-promoter-score-results">
									<table class="net-promoter-score-results">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Detractors (0-6)', 'wpforms-surveys-polls' ); ?></th>
												<th><?php esc_html_e( 'Passives (7-8)', 'wpforms-surveys-polls' ); ?></th>
												<th><?php esc_html_e( 'Promoters (9-10)', 'wpforms-surveys-polls' ); ?></th>
												<th class="score"><?php esc_html_e( 'Net Promoter Score', 'wpforms-surveys-polls' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td><span class="count">{{ fieldData.nps.detractors.count }}</span><span class="percent">{{ fieldData.nps.detractors.percent }}%</span></td>
												<td><span class="count">{{ fieldData.nps.passives.count }}</span><span class="percent">{{ fieldData.nps.passives.percent }}%</span></td>
												<td><span class="count">{{ fieldData.nps.promoters.count }}</span><span class="percent">{{ fieldData.nps.promoters.percent }}%</span></td>
												<td>{{ fieldData.nps.score }}</td>
											</tr>
										</tbody>
									</table>
								</div>
							<# } else { #>
								<div class="table-wrap">
									<table class="wpforms-table-sorting data-results">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Answers', 'wpforms-surveys-polls' ); ?></th>
												<th data-sort="int" data-sort-default="desc" data-sort-onload="yes" class="responses"><?php esc_html_e( 'Responses', 'wpforms-surveys-polls' ); ?> <i class="fa fa-sort" aria-hidden="true"></i></th>
											</tr>
										</thead>
										<tbody>
											<# _.each( fieldData.answers, function( answer, key ) { #>
												<tr><td>{{ answer.value }}</td><td data-sort-value="{{ answer.percent }}">{{ answer.percent }}% <span class="total">{{ answer.count }}</span></td></tr>
											<# }) #>
										</tbody>
									</table>
								</div>
							<# } #>
						<# } else if ( 'likert_scale' === fieldData.type ) { #>
							<# var rowCount = 1; #>
							<div class="table-wrap likert-results">
								<table class="likert-results<# if ( fieldData.table.single ) { print( ' single' ); } else { print( ' wpforms-table-sorting' ); } #>">
									<thead>
										<tr>
										<# if ( ! fieldData.table.single ) { #>
											<th style="width:20%;"></th>
										<# } #>
										<# _.each( fieldData.table.columns, function( columnLabel, key ) {  #>
											<th data-sort="int" data-sort-default="desc" style="width:{{ fieldData.table.width }}%;"<# if ( ! fieldData.table.single ) { print( ' class="sortable"' ); } #>>{{ columnLabel }} <# if ( ! fieldData.table.single ) { print( '<i class="fa fa-sort" aria-hidden="true">' ); } #></i></th>
										<# }) #>
										</tr>
									</thead>
									<tbody>
										<# _.each( fieldData.table.rows, function( rowLabel, rowKey ) {  #>
											<# if ( ! fieldData.table.single || ( fieldData.table.single && rowCount === 1 ) ) { #>
												<tr>
												<# if ( ! fieldData.table.single ) { #>
													<td class="th"><# print( _.unescape( rowLabel ) ); #></td>
												<# } #>
												<# _.each( fieldData.table.columns, function( columnLabel, columnKey ) {  #>
													<td data-sort-value="{{ fieldData.answers[rowKey + '_' + columnKey].count }}"<# if ( fieldData.answers[rowKey + '_' + columnKey].highest ) { print( ' class="highest"' ); } #>>
														<# if ( fieldData.answers[rowKey + '_' + columnKey].count > 0 ) { #>
															<span class="count">{{ fieldData.answers[rowKey + '_' + columnKey].count }}</span>
															<span class="percent">{{ fieldData.answers[rowKey + '_' + columnKey].percent }}%</span>
														<# } #>
													</td>
												<# }) #>
												</tr>
											<# } #>
											<# rowCount++ #>
										<# }) #>
									</tbody>
								</table>
							</div>
						<# } else { #>
							<div class="table-wrap text-results">
								<table class="wpforms-table-sorting text-results">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Answers', 'wpforms-surveys-polls' ); ?></th>
											<th data-sort="int" data-sort-default="desc" data-sort-onload="yes" class="date"><?php esc_html_e( 'Date', 'wpforms-surveys-polls' ); ?> <i class="fa fa-sort" aria-hidden="true"></i></th>
										</tr>
									</thead>
									<tbody>
										<# _.each( fieldData.answers, function( answer, key ) { #>
											<tr><td>{{ answer.value }}</td><td class="date" data-sort-value="{{ answer.date_unix }}">{{ answer.date }} <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-entries&view=details&entry_id=' ) ); ?>{{ answer.entry_id }}" title="<?php esc_html_e( "View respondent's answers", 'wpforms-surveys-polls' ); ?>" target="_blank" class="view-entry"><i class="fa fa-external-link" aria-hidden="true"></i></a></td></tr>
										<# }) #>
									</tbody>
								</table>
							</div>
						<# } #>
						<div class="stats <# if ( fieldData.average ) { print( 'has-average' ); } #>">
							<div class="answered">
								<strong>{{ fieldData.answered }}</strong>
								<?php esc_html_e( 'Answered', 'wpforms-surveys-polls' ); ?>
							</div>
							<# if ( fieldData.average ) { #>
								<div class="average">
									<strong>{{ fieldData.average }}</strong>
									<?php esc_html_e( 'Average', 'wpforms-surveys-polls' ); ?>
								</div>
							<# } #>
							<div class="skipped">
								<strong>{{ fieldData.skipped }}</strong>
								<?php esc_html_e( 'Skipped', 'wpforms-surveys-polls' ); ?>
							</div>
						</div>
					<# } #>
				</div>
				<# count++ #>
			<# }) #>
		</script>
		<?php
	}

	/**
	 * Delete current cache when entry edited.
	 *
	 * @since 1.6.3
	 *
	 * @param array  $form_data      Form data.
	 * @param mixed  $response       Entries edit process response.
	 * @param array  $updated_fields Updated fields data.
	 * @param object $entry          Existing entry data.
	 */
	public function entries_edit_submit_clear_cache( $form_data, $response, $updated_fields, $entry ) {

		if ( empty( $response['modified'] ) || empty( $updated_fields ) ) {
			return;
		}

		$entry = (array) $entry;

		if ( ! wpforms_current_user_can( 'edit_entry_single', $entry['entry_id'] ) ) {
			return;
		}

		$fields      = ! empty( $form_data['fields'] ) ? $form_data['fields'] : [];
		$entry_count = wpforms()->obj( 'entry' )->get_entries( [ 'form_id' => $form_data['id'] ], true );
		$deleted     = false;

		foreach ( $fields as $field ) {

			if ( ! Fields::field_has_survey( $field, $form_data ) ) {
				continue;
			}

			if ( ! isset( $field['id'] ) || ! array_key_exists( $field['id'], $updated_fields ) ) {
				continue;
			}

			delete_transient( "wpforms_survey_report_{$form_data['id']}_{$entry_count}_{$field['id']}" );
			$deleted = true;
		}

		if ( $deleted ) {
			delete_transient( "wpforms_survey_report_{$form_data['id']}_{$entry_count}" );
		}
	}

	/**
	 * Drop cache for current count and all previous.
	 *
	 * @since 1.11.0
	 */
	private function entries_delete_clear_cache() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$option_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_%' . $wpdb->esc_like( "wpforms_survey_report_{$this->form_id}_" ) . '%'
			)
		);

		if ( empty( $option_names ) ) {
			return;
		}

		$option_names = array_map(
			static function ( $option_name ) {

				return str_replace( [ '_transient_timeout_', '_transient_' ], '', $option_name );
			},
			$option_names
		);

		$option_names = array_unique( $option_names );

		foreach ( $option_names as $option_name ) {
			delete_transient( $option_name );
		}
	}
}
