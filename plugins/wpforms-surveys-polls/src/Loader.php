<?php

namespace WPFormsSurveys;

/**
 * WPForms Surveys and Polls loader class.
 *
 * @since 1.0.0
 */
final class Loader {

	/**
	 * URL to a plugin directory. Used for assets.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public $url = '';

	/**
	 * Initiate the main plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loader
	 */
	public static function get_instance(): Loader {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();

			$instance->init();
		}

		return $instance;
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	private function init(): void {

		$this->url = plugin_dir_url( __DIR__ );

		( new Migrations\Migrations() )->init();

		new Reporting\Ajax();
		new Polls();

		( new Integrations() )->hooks();

		// The admin_init action is too late for FSE.
		// We have to run it before register_block_type() is executed in \WPForms\Integrations\Gutenberg\FormSelector.
		new Admin();

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.12.0
	 */
	private function hooks(): void {

		add_action(
			'admin_init',
			static function () {

				( new Fields\LikertScale\EntriesEdit() )->init();
				( new Fields\NetPromoterScore\EntriesEdit() )->init();

				new Reporting\Admin();
				new Templates\Poll();
				new Templates\Survey();
				new Templates\NPSSurveySimple();
				new Templates\NPSSurveyEnhanced();
			},
			15
		);
	}
}
