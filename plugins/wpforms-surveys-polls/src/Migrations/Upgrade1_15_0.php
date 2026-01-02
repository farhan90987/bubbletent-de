<?php

namespace WPFormsSurveys\Migrations;

use WPForms\Migrations\UpgradeBase;

/**
 * Class Surveys Polls addon v1.15.0 upgrade.
 *
 * @since 1.15.0
 *
 * @noinspection PhpUnused
 */
class Upgrade1_15_0 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.15.0
	 *
	 * @return bool|null Upgrade result:
	 *                   true  - the upgrade completed successfully,
	 *                   false - in the case of failure,
	 *                   null  - upgrade started but not yet finished (background task).
	 */
	public function run() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%wpforms_survey_report_%'" ) );

		return true;
	}
}
