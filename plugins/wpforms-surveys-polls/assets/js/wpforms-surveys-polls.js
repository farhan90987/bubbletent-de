'use strict';

/**
 * WPForms Surveys & Polls frontend part.
 *
 * @since 1.12.0
 */
const WPFormsSurveysPolls = window.WPFormsSurveysPolls || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.12.0
	 *
	 * @type {object}
	 */
	const app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.12.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Initialized once the DOM is fully loaded.
		 *
		 * @since 1.12.0
		 */
		ready: function() {

			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.12.0
		 */
		events: function() {

			$( document )
				.on( 'focus', '.wpforms-net-promoter-score-option', app.focusNPSInputElement )
				.on( 'blur', '.wpforms-net-promoter-score-option', app.blurNPSInputElement );
		},

		/**
		 * Focus Net Promoter Score input element event handler.
		 *
		 * @since 1.12.0
		 *
		 * @param {Event} e Event.
		 */
		focusNPSInputElement: function( e ) {

			$( this ).closest( '.wpforms-field' ).addClass( 'wpforms-field-focused' );
		},

		/**
		 * Blur Net Promoter Score input element event handler.
		 *
		 * @since 1.12.0
		 *
		 * @param {Event} e Event.
		 */
		blurNPSInputElement: function( e ) {

			$( this ).closest( '.wpforms-field' ).removeClass( 'wpforms-field-focused' );
		},
	};

	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsSurveysPolls.init();
