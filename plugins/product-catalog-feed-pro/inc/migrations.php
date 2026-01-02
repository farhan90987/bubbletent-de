<?php

class Wpwoof_Migration_Manager {

	private static $migrations = [
		'5.2.5' => 'migrate_to_5_2_5',
		'5.6.0' => 'migrate_to_5_6_0',
		'5.7.2' => 'migrate_to_5_7_2',
	];

	private static $log_file;

	/**
	 * Executes the necessary migrations based on the provided current version.
	 *
	 * @param string $current_version The current version of the application or system.
	 * @param bool $is_activate Specifies whether the method is triggered during an activation process. Defaults to false.
	 * @param string|null $log_file Specifies the log file to use for logging migration information. Defaults to null.
	 *
	 * @return void
	 */
	public static function run( $current_version, $is_activate = false, $log_file = null ) {
		self::$log_file = $log_file;

		foreach ( self::$migrations as $version => $method ) {
			if ( version_compare( $current_version, $version, '<' ) ) {
				self::log( "Running migration: {$method}" );
				self::$method( $is_activate );
			}
		}
	}

	/**
	 * Performs the migration tasks required for version 5.2.5.
	 *
	 * @param bool $is_activate Indicates if the migration is being executed during an activation process.
	 *
	 * @return void
	 */
	private static function migrate_to_5_2_5( $is_activate ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key regexp '^[0-9]+\-wpfoof\-' OR meta_key regexp '^[0-9]+wpfoof\-'" );
		self::log( "Migration 5.2.5 completed: Cleaned up postmeta" );
	}

	/**
	 * Performs the migration to version 5.6.0 by rescheduling necessary cron jobs.
	 *
	 * @param bool $is_activate Indicates whether the migration is being triggered during an activation process.
	 *
	 * @return void
	 */
	private static function migrate_to_5_6_0( $is_activate ) {
		wp_unschedule_hook( 'wpwoof_feed_update' );
		wp_schedule_event( time(), 'every_minute', 'wpwoof_feed_update' );
		wp_unschedule_hook( 'wpwoof_generate_feed' );

		if ( ! $is_activate ) {
			WoocommerceWpwoofCommon::reschedule_active_feeds();
		}

		self::log( "Migration 5.6.0 completed: Rescheduled cron jobs" );
	}

	/**
	 * Handles the migration process to version 5.7.2.
	 *
	 * @param bool $is_activate Indicates whether the migration is being executed during an activation process.
	 *
	 * @return void
	 */
	private static function migrate_to_5_7_2( $is_activate ) {

		add_action( 'init', function () use ( $is_activate ) {
			self::do_migrate_to_5_7_2( $is_activate );
		}, 999 );
	}

	/**
	 * Handles the migration process to version 5.7.2, updating feed configurations
	 * and ensuring compatibility with the new version.
	 *
	 * @param bool $is_activate Indicates whether the migration is initiated during an activation process.
	 *
	 * @return void
	 */
	private static function do_migrate_to_5_7_2( $is_activate ) {
		WPWOOF_Logger::create_table();

		WPWOOF_Logger::instance()->log(
			'Starting migration to version 5.7.2',
			WPWOOF_Logger::LOG_TYPE_MIGRATION,
			null,
			WPWOOF_Logger::LOG_LEVEL_INFO
		);

		global $wpdb;
		$sql = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_%'";
		$res = $wpdb->get_results( $sql, 'ARRAY_A' );

		foreach ( $res as $val ) {
			$feed_config = unserialize( $val['option_value'] );
			$feed_id     = $feed_config['edit_feed'];

			$old_excluded = isset( $feed_config['feed_category_excluded'] ) ? $feed_config['feed_category_excluded'] : array();
			$old_included = isset( $feed_config['feed_category'] ) ? $feed_config['feed_category'] : array();

			$updated_config = self::migrate_feed_categories_to_5_7_2( $feed_config );

			#3846 Meta Feeds: Show main item for variable products must be OFF as default
			if ( $feed_config['feed_type'] == 'facebook' && empty( $feed_config['feed_remove_variations'] ) ) {
				if ( $updated_config === null ) {
					$updated_config = $feed_config;
				}
				unset( $updated_config['feed_variation_show_main'] );
				self::log( "Migration 5.7.2: Feed {$feed_id} - 'Show main variable product item' option disabled for Facebook feed" );

				WPWOOF_Logger::instance()->log(
					sprintf( 'Disabled "Show main variable product item" option for Facebook feed: %s (ID: %d)',
						$feed_config['feed_name'] ?? 'Unknown',
						$feed_id
					),
					WPWOOF_Logger::LOG_TYPE_MIGRATION,
					$feed_id,
					WPWOOF_Logger::LOG_LEVEL_INFO
				);
			}

			if ( $updated_config !== null ) {
				$new_excluded = isset( $updated_config['feed_category_excluded'] ) ? $updated_config['feed_category_excluded'] : array();
				$new_included = isset( $updated_config['feed_category'] ) ? $updated_config['feed_category'] : array();

				update_option( $val['option_name'], $updated_config );

				$excluded_changed = $old_excluded !== $new_excluded;
				$included_changed = $old_included !== $new_included;

				if ( $excluded_changed || $included_changed ) {
					$log_message = "Migration 5.7.2: Updated feed {$feed_id}";

					if ( $excluded_changed ) {
						$log_message .= " | Old excluded: [" . implode( ', ', $old_excluded ) . "] | New excluded: [" . implode( ', ', $new_excluded ) . "]";
					}

					if ( $included_changed ) {
						$log_message .= " | Old included: [" . implode( ', ', $old_included ) . "] | New included: [" . implode( ', ', $new_included ) . "]";
					}

					self::log( $log_message );

					WPWOOF_Logger::instance()->log(
						sprintf( 'Feed configuration changed during migration: %s (ID: %d)',
							$feed_config['feed_name'] ?? 'Unknown',
							$feed_id
						),
						WPWOOF_Logger::LOG_TYPE_MIGRATION,
						$feed_id,
						WPWOOF_Logger::LOG_LEVEL_INFO
					);
				}
			}
		}

		self::log( "Migration 5.7.2 completed: Updated feed category exclusions" );

		WPWOOF_Logger::instance()->log(
			'Migration to version 5.7.2 completed successfully',
			WPWOOF_Logger::LOG_TYPE_MIGRATION,
			null,
			WPWOOF_Logger::LOG_LEVEL_INFO
		);
	}

	/**
	 * Updates the feed configuration by processing feed categories, including excluded ones, and ensuring consistency.
	 *
	 * @param array $feed_config The configuration of the feed containing category information and settings.
	 *
	 * @return array|null Returns the updated feed configuration array if changes were made, or null if no changes occurred.
	 */
	private static function migrate_feed_categories_to_5_7_2( $feed_config ) {
		$is_changed = false;

		if ( ! empty( $feed_config["feed_include_excluded"] ) ) {
			if ( empty( $feed_config['feed_category_all'] ) && ! empty( $feed_config['feed_category_excluded'] ) ) {
				$feed_config['feed_category_excluded'] = array();
				$is_changed                            = true;
			}
		} else {
			if ( empty( $feed_config['feed_category_all'] ) ) {
				$excluded_cat_ids = WoocommerceWpwoofCommon::get_ids_excluded_categories_in_cat_settings();

				if ( ! empty( $excluded_cat_ids ) ) {
					// Process excluded categories
					$old_excluded = isset( $feed_config['feed_category_excluded'] ) && is_array( $feed_config['feed_category_excluded'] )
						? $feed_config['feed_category_excluded']
						: array();

					$new_excluded = array_unique( array_merge( $old_excluded, $excluded_cat_ids ) );

					// Sort for correct comparison
					$old_sorted = $old_excluded;
					$new_sorted = $new_excluded;
					sort( $old_sorted );
					sort( $new_sorted );

					// Check if there are changes in excluded categories
					if ( $old_sorted !== $new_sorted ) {
						$feed_config['feed_category_excluded'] = $new_excluded;
						$is_changed                            = true;
//						self::log( "  Action: Categories excluded updated (added " . ( count( $new_excluded ) - count( $old_excluded ) ) . " new)" );
					}

					// Remove excluded categories from included list
					if ( isset( $feed_config['feed_category'] ) && is_array( $feed_config['feed_category'] ) && ! empty( $feed_config['feed_category'] ) ) {
						$old_included = $feed_config['feed_category'];
						$new_included = array_diff( $old_included, $excluded_cat_ids );

						// Check if there were any removals
						if ( count( $old_included ) !== count( $new_included ) ) {
							$feed_config['feed_category'] = array_values( $new_included ); // Re-index array
							$is_changed                   = true;
							$removed_count                = count( $old_included ) - count( $new_included );
//							self::log( "  Action: Removed {$removed_count} excluded categories from included list" );
//							self::log( "  Removed IDs: [" . implode( ', ', array_diff( $old_included, $new_included ) ) . "]" );
						}
					}
				}
			}
		}

		return $is_changed ? $feed_config : null;
	}

	/**
	 * Logs a message to the specified log file if debugging is enabled.
	 *
	 * @param string $message The message to be logged.
	 *
	 * @return void
	 */
	private static function log( $message ) {
		if ( WPWOOF_DEBUG && self::$log_file ) {
			file_put_contents( self::$log_file, date( "Y-m-d H:i:s" ) . "\t{$message}\n", FILE_APPEND );
		}
	}
}