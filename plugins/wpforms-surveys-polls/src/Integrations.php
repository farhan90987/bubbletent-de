<?php

namespace WPFormsSurveys;

use WPForms\Integrations\Divi\Divi;

/**
 * Integrations class.
 *
 * @since 1.12.0
 */
class Integrations {

	/**
	 * Hooks.
	 *
	 * @since 1.12.0
	 */
	public function hooks() {

		add_action( 'wpforms_frontend_css', [ $this, 'enqueue_divi_styles' ] );
	}

	/**
	 * Enqueue Divi styles.
	 *
	 * @since 1.12.0
	 *
	 * @param array $forms Forms displayed on current page.
	 */
	public function enqueue_divi_styles( array $forms ) {

		if ( ! Divi::is_divi_loaded() ) {
			return;
		}

		if (
			! wpforms()->obj( 'frontend' )->assets_global() &&
			! wpforms_has_field_type( 'likert_scale', $forms, true ) &&
			! wpforms_has_field_type( 'net_promoter_score', $forms, true )
		) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-surveys-polls-divi',
			wpforms_surveys_polls()->url . "assets/css/integrations/divi/wpforms-surveys-polls-divi{$min}.css",
			[],
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}
}
