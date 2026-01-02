<?php

namespace WPFormsSurveys\Reporting;

/**
 * Survey reporting admin page and related functionality.
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'init' ], 10 );
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Register AJAX callbacks.
		add_action( 'wp_ajax_wpforms_surveys_field_data', [ $this, 'survey_get_field_data' ] );
		add_action( 'wp_ajax_wpforms_surveys_set_preview_field', [ $this, 'survey_set_preview_field' ] );
	}

	/**
	 * Get survey data for a given field.
	 *
	 * @since 1.0.0
	 * @since 1.15.0 Added caching and permissions check.
	 */
	public function survey_get_field_data() {

		// Run a security check.
		check_ajax_referer( 'wpforms-admin', 'nonce' );

		$fetch_error = __( 'Error fetching the data.', 'wpforms-surveys-polls' );

		if ( empty( $_POST['form_id'] ) || empty( $_POST['field_ids'] ) || ! isset( $_POST['entry_count'] ) ) {
			wp_send_json_error( [ 'message' => esc_html( $fetch_error ) ] );
		}

		$form_id = absint( $_POST['form_id'] );

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'view_entries_form_single', $form_id ) ) {
			wp_send_json_error( [ 'message' => esc_html( $fetch_error ) ] );
		}

		$form_data = wpforms()->obj( 'form' )->get(
			$form_id,
			[
				'content_only' => true,
				'cap'          => 'view_entries_form_single',
			]
		);

		if ( empty( $form_data ) ) {
			wp_send_json_error( [ 'message' => esc_html( $fetch_error ) ] );
		}

		// Inform users that a form doesn't have any entries to provide survey results for.
		if ( (int) $_POST['entry_count'] <= 0 ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Survey results will display here after form submissions are received. Please check back later.', 'wpforms-surveys-polls' ) ] );
		}

		$field_ids   = array_map( 'absint', (array) $_POST['field_ids'] );
		$entry_count = absint( $_POST['entry_count'] );
		$fields      = $this->prepare_fields_data( $field_ids, $form_data, $form_id, $entry_count );

		// Reset the original array keys to preserve the intended order,
		// as JavaScript may reorder object keys, especially numeric ones.
		wp_send_json_success( array_values( $fields ) );
	}

	/**
	 * Prepare survey field data.
	 *
	 * @since 1.15.0
	 *
	 * @param array $field_ids   Fields ids.
	 * @param array $form_data   Form data and settings.
	 * @param int   $form_id     Form ID.
	 * @param int   $entry_count Entry count.
	 *
	 * @return array
	 */
	private function prepare_fields_data( array $field_ids, array $form_data, int $form_id, int $entry_count ): array {

		$fields   = [];
		$field_id = '';

		foreach ( $field_ids as $field_id ) {
			if ( ! isset( $form_data['fields'][ $field_id ] ) ) {
				continue;
			}

			$fields[ $field_id ] = Fields::get_survey_field_data( $form_data['fields'][ $field_id ], $form_id, $entry_count );
		}

		if ( empty( $fields ) ) {
			return [];
		}

		/**
		 * Allow caching of survey report data.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $is_cache_enabled Whether to cache survey report data.
		 * @param int  $form_id          Form ID.
		 */
		if ( (bool) apply_filters( 'wpforms_surveys_polls_report_caching', true, $form_id ) ) { // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$cache_key = count( $fields ) === 1 ? "wpforms_survey_report_{$form_id}_{$entry_count}_$field_id" : "wpforms_survey_report_{$form_id}_$entry_count";

			// Reset the original array keys to preserve the intended order,
			// as JavaScript may reorder object keys, especially numeric ones.
			set_transient( $cache_key, wp_json_encode( array_values( $fields ) ), DAY_IN_SECONDS * 2 );
		}

		return $fields;
	}

	/**
	 * Set field data cache.
	 *
	 * @since 1.0.0
	 * @deprecated 1.15.0
	 */
	public function survey_set_field_cache() {

		_deprecated_function( __METHOD__, '1.15.0 of the WPForms Surveys and Polls addon.' );

		// Run a security check.
		check_ajax_referer( 'wpforms-admin', 'nonce' );

		if ( empty( $_POST['form_id'] ) || empty( $_POST['entry_count'] ) || empty( $_POST['field_data'] ) ) {
			wp_send_json_error();
		}

		$form_id = absint( $_POST['form_id'] );

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'view_entries_form_single', $form_id ) ) {
			wp_send_json_error();
		}

		$entry_count = absint( $_POST['entry_count'] );

		$data     = isset( $_POST['field_data'] ) ? sanitize_text_field( wp_unslash( $_POST['field_data'] ) ) : '';
		$field_id = ! empty( $_POST['field_id'] ) ? absint( $_POST['field_id'] ) : false;

		if ( ! apply_filters( 'wpforms_surveys_polls_report_caching', true, $form_id ) ) {
			return;
		}

		if ( $field_id ) {
			// Cache survey field preview.
			set_transient( "wpforms_survey_report_{$form_id}_{$entry_count}_{$field_id}", $data, DAY_IN_SECONDS * 2 );
		} else {
			// Cache survey report.
			set_transient( "wpforms_survey_report_{$form_id}_{$entry_count}", $data, DAY_IN_SECONDS * 2 );
		}
	}

	/**
	 * Set preferred survey preview field.
	 *
	 * @since 1.0.0
	 */
	public function survey_set_preview_field() {

		// Run a security check.
		check_ajax_referer( 'wpforms-admin', 'nonce' );

		if ( empty( $_POST['form_id'] ) || empty( $_POST['field_id'] ) ) {
			wp_send_json_error();
		}

		$form_id = absint( $_POST['form_id'] );

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'view_entries_form_single', $form_id ) ) {
			wp_send_json_error();
		}

		$field_id = absint( $_POST['field_id'] );

		// Update form meta.
		wpforms()->obj( 'form' )->update_meta( $form_id, 'survey_preview', $field_id );
	}
}
