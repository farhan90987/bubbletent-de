<?php

namespace WPFormsSurveys\Reporting;

/**
 * Field related survey reporting methods.
 *
 * @since 1.0.0
 */
class Fields {

	/**
	 * Build and return the survey data for a given field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field       Field settings.
	 * @param int   $form_id     Form ID.
	 * @param int   $entry_count Total number of entries.
	 * @param array $form_data   Form data and settings.
	 *
	 * @return array
	 */
	public static function get_survey_field_data( $field, $form_id, $entry_count = 0, $form_data = [] ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		// Get answers for this field.
		$answers = wpforms()->obj( 'entry_fields' )->get_fields(
			[
				'number'   => -1,
				'form_id'  => $form_id,
				'field_id' => $field['id'],
				'order'    => 'ASC',
			]
		);

		$ignored_entry_ids = self::get_ignored_entry_ids( $form_id );

		$answers = array_filter(
			$answers,
			static function ( $answer ) use ( $ignored_entry_ids ) {

				return ! in_array( $answer->entry_id, $ignored_entry_ids, true );
			}
		);

		// Setup and define basic default data.
		$data = [
			'id'       => $field['id'],
			'type'     => $field['type'],
			'badge'    => self::get_field_badge_markup( $field['type'] ),
			'question' => $field['label'],
			'total'    => $entry_count,
			'answered' => count( $answers ),
			'skipped'  => ! empty( $answers ) && ! empty( $entry_count ) ? $entry_count - count( $answers ) : 0,
			'answers'  => [],
			'chart'    => [
				'supports' => [ 'bar', 'bar-h', 'pie', 'line' ],
				'default'  => 'bar',
				'labels'   => [],
				'totals'   => [],
				'data'     => [],
			],
		];

		// If there are no answers, bail to prevent calculations.
		if ( empty( $answers ) ) {
			return $data;
		}

		// Get ready to process and calculate the data.
		switch ( $field['type'] ) {
			// Radio, Select, and Checkbox fields all share the same calculations.
			case 'radio':
			case 'select':
			case 'checkbox':
			case 'rating':

				$is_radio_field = $field['type'] === 'radio';

				// Loop through each answer for process.
				foreach ( $answers as $answer ) {

					if ( self::is_multiple( $field ) ) {

						// Checkbox and Multiple Select values are slightly different because
						// there can be multiple values in a single answer.
						// This requires adjusted logic.
						$checks = explode( "\n", $answer->value );

						// Checkbox and Multiple Select reporting does not support the pie graph
						// so remove it from the defaults graph types supported.
						$data['chart']['supports'] = [ 'bar', 'bar-h', 'line' ];

						// Process values.
						foreach ( $checks as $check_key => $check ) {

							$exists = false;

							// Normalize value for choices that include free text (e.g., Other: custom text).
							$normalized_check = $is_radio_field ? self::normalize_answer_value( $check, $field ) : $check;

							if ( ! empty( $data['answers'] ) ) {
								foreach ( $data['answers'] as $key => $item ) {
									if ( $normalized_check === $item['value'] ) {
										$data['answers'][ $key ]['count']++;

										$exists = true;

										break;
									}
								}
							}

							if ( ! $exists ) {
								$data['answers'][ $answer->id . '_' . $check_key ] = [
									'value' => $normalized_check,
									'count' => 1,
								];
							}
						}
					} else {

						$answer_value = $answer->value;
						$exists       = false;

						// For the rating field we adjust the values to make
						// them more human readable.
						if ( $field['type'] === 'rating' ) {

							switch ( $field['icon'] ) {
								case 'star':
									/* translators: %s - number of stars. */
									$answer_value = esc_html( sprintf( _n( '%s star', '%s stars', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;

								case 'heart':
									/* translators: %s - number of hearts. */
									$answer_value = esc_html( sprintf( _n( '%s heart', '%s hearts', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;

								case 'thumb':
									/* translators: %s - number of thumbs. */
									$answer_value = esc_html( sprintf( _n( '%s thumb', '%s thumbs', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;

								case 'smiley':
									/* translators: %s - number of smileys. */
									$answer_value = esc_html( sprintf( _n( '%s smiley', '%s smileys', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;
							}

							// Add the scale at the end for reference.
							$answer_value .= " ({$answer->value}/{$field['scale']})";
						}

						// Normalize value for choices that include free text (e.g., Other option).
						if ( $is_radio_field ) {
							$answer_value = self::normalize_answer_value( $answer_value, $field );
						}

						// Process values.
						if ( ! empty( $data['answers'] ) ) {
							foreach ( $data['answers'] as $key => $item ) {
								if ( $answer_value === $item['value'] ) {
									$data['answers'][ $key ]['count']++;

									$exists = true;

									break;
								}
							}
						}

						if ( ! $exists ) {
							$data['answers'][ $answer->id ] = [
								'value' => $answer_value,
								'count' => 1,
							];

							if ( $field['type'] === 'rating' ) {
								$data['answers'][ $answer->id ]['value_raw'] = $answer->value;
							}
						}
					}
				}

				// Rating field specific actions.
				if ( $field['type'] === 'rating' && ! empty( $data['answers'] ) ) {
					// Reorder answers by numeric value.
					usort(
						$data['answers'],
						function( $a, $b ) {
							return $a['value_raw'] - $b['value_raw'];
						}
					);

					// Calculate average rating.
					$total = 0;

					foreach ( $answers as $answer ) {
						$total += absint( $answer->value );
					}

					$data['average'] = round( $total / count( $answers ), 1 );
				}

				// For Radio, Checkboxes, and Select fields, reorder the answers
				// to match the order they are displayed in the survey.
				if ( in_array( $field['type'], [ 'radio', 'select', 'checkbox' ], true ) && ! empty( $data['answers'] ) ) {

					$answers_originals = $data['answers'];
					$answers_ordered   = [];

					foreach ( $field['choices'] as $choice_key => $choice ) {
						foreach ( $answers_originals as $key => $answers_original ) {
							if ( sanitize_text_field( $choice['label'] ) === $answers_original['value'] ) {
								$answers_ordered[ $key ]              = $answers_original;
								$answers_ordered[ $key ]['choice_id'] = $choice_key;

								unset( $answers_originals[ $key ] );
								break;
							}
						}
					}

					// If there are any answers remaining that means they are
					// choices that were available at some point, but have been
					// removed and no longer exist. In this case we add them to
					// the end.
					if ( ! empty( $answers_originals ) ) {
						$answers_ordered = array_replace_recursive( $answers_ordered, $answers_originals );
					}

					$data['answers'] = $answers_ordered;
				}

				// Loop through each answer and compile/format values needed for Chart JS.
				if ( ! empty( $data['answers'] ) ) {
					foreach ( $data['answers'] as $key => $item ) {
						$percent                            = round( ( $item['count'] / count( $answers ) ) * 100 );
						$data['answers'][ $key ]['percent'] = $percent;
						$data['chart']['labels'][]          = $item['value'];
						$data['chart']['totals'][]          = $item['count'];
						$data['chart']['data'][]            = $percent;
					}
				}
				break;

			case 'text':
			case 'textarea':
				// Text input and text area results.
				// Text based fields don't support charts so we disable.
				$data['chart']['supports'] = [];
				$data['chart']['default']  = false;

				// Loop through each answer.
				foreach ( $answers as $answer ) {

					$data['answers'][ $answer->id ] = [
						'value'     => $answer->value,
						'date'      => wpforms_datetime_format( $answer->date, '', true ),
						'date_unix' => strtotime( $answer->date ),
						'entry_id'  => absint( $answer->entry_id ),
					];
				}
				break;

			// Likert Scale results.
			case 'likert_scale':
				// Get the form data to have access to field rows and columns.
				if ( empty( $form_data ) ) {
					$form_data = wpforms()->obj( 'form' )->get(
						$form_id,
						[
							'content_only' => true,
						]
					);
				}

				// Likert fields don't support charts so we disable.
				$data['chart']['supports'] = [];
				$data['chart']['default']  = false;

				// Basic details for rendering the results table.
				$data['table']            = [];
				$data['table']['columns'] = [];
				$data['table']['rows']    = [];
				$data['table']['single']  = ! empty( $field['single_row'] ) ? true : false;
				$data['table']['width']   = $data['table']['single'] ? round( 100 / count( $form_data['fields'][ $field['id'] ]['columns'] ), 4 ) : round( 80 / count( $form_data['fields'][ $field['id'] ]['columns'] ), 4 );

				// Prefix the field rows and column keys to preserve the order
				// when looping in our javascript template.
				foreach ( $form_data['fields'][ $field['id'] ]['rows'] as $k => $v ) {
					$data['table']['rows'][ "r{$k}" ] = $v;
				}
				foreach ( $form_data['fields'][ $field['id'] ]['columns'] as $k => $v ) {
					$data['table']['columns'][ "c{$k}" ] = $v;
				}

				// Get the row and column IDs to use to verify data.
				$row_ids    = array_map( 'absint', array_keys( $form_data['fields'][ $field['id'] ]['rows'] ) );
				$column_ids = array_map( 'absint', array_keys( $form_data['fields'][ $field['id'] ]['columns'] ) );

				// Set all the initial counts to zero.
				$counts = [];

				foreach ( $row_ids as $r ) {
					foreach ( $column_ids as $c ) {
						$counts[ $r ][ $c ] = 0;
					}
				}

				// Loop through each answer to process counts.
				foreach ( $answers as $answer ) {

					// Fetch and decode the raw values (arrays).
					$values = json_decode( $answer->value, true );
					$values = ! empty( $values['value_raw'] ) ? $values['value_raw'] : false;

					if ( ! is_array( $values ) ) {
						continue;
					}

					foreach ( $values as $row_key => $column_keys ) {

						// If this row key is not found, that means the admin
						// has likely removed that row from the field settings,
						// so we discard it.
						if ( ! in_array( absint( $row_key ), $row_ids, true ) ) {
							continue;
						}

						$column_keys = (array) $column_keys;

						foreach ( $column_keys as $column_key ) {

							// If this column key is not found, that means the
							// admin has likely removed that column from the
							// field settings, so we discard it.
							if ( ! in_array( absint( $column_key ), $column_ids, true ) ) {
								continue;
							}

							// Increment the count.
							$counts[ $row_key ][ $column_key ]++;
						}
					}
				}

				// Compile the final answer data to return.
				foreach ( $row_ids as $r ) {
					foreach ( $column_ids as $c ) {

						$answer_key = "r{$r}_c{$c}";
						$total      = ! empty( $counts[ $r ] ) ? array_sum( $counts[ $r ] ) : 0;
						$count      = $counts[ $r ][ $c ];

						$data['answers'][ $answer_key ] = [
							'count'   => $count,
							'percent' => ! empty( $count ) && ! empty( $total ) ? round( ( $count / $total ) * 100 ) : 0,
							'highest' => ! empty( $count ) ? max( $counts[ $r ] ) === $count : false,
						];
					}
				}
				break;

			case 'net_promoter_score':
				// Define initial values.
				$data['nps'] = [
					'detractors' => [
						'count'   => 0,
						'percent' => 0,
					],
					'passives'   => [
						'count'   => 0,
						'percent' => 0,
					],
					'promoters'  => [
						'count'   => 0,
						'percent' => 0,
					],
					'score'      => 0,
				];

				for ( $i = 0; $i < 11; $i++ ) {
					$data['answers'][ $i ] = [
						'value'   => $i,
						'count'   => 0,
						'percent' => 0,
					];
				}

				// Loop through each answer for process.
				foreach ( $answers as $answer ) {
					$data['answers'][ (int) $answer->value ]['count']++;
				}

				// Loop through each answer.
				if ( ! empty( $data['answers'] ) ) {
					foreach ( $data['answers'] as $key => $item ) {
						$percent                            = round( ( $item['count'] / count( $answers ) ) * 100 );
						$data['answers'][ $key ]['percent'] = $percent;

						// Compile/format values needed for Chart JS.
						$data['chart']['labels'][] = $item['value'];
						$data['chart']['totals'][] = $item['count'];
						$data['chart']['data'][]   = $percent;

						// Assign NPS category.
						if ( $item['value'] >= 9 ) {
							$data['nps']['promoters']['count'] += $item['count'];
						} elseif ( $item['value'] >= 7 ) {
							$data['nps']['passives']['count'] += $item['count'];
						} else {
							$data['nps']['detractors']['count'] += $item['count'];
						}
					}
				}

				// Calculate NPS category percentages.
				foreach ( $data['nps'] as $key => $nps_category ) {
					if ( $key === 'score' ) {
						continue;
					}
					$data['nps'][ $key ]['percent'] = round( ( $nps_category['count'] / count( $answers ) ) * 100 );
				}

				// Calculate raw NPS score.
				$data['nps']['score'] = round( ( ( $data['nps']['promoters']['count'] - $data['nps']['detractors']['count'] ) / count( $answers ) ) * 100, 2 );
				break;
		}

		/**
		 * Return the final array of data, filterable.
		 *
		 * @since 1.11.0
		 *
		 * @param array $data    Survey data for a given field.
		 * @param int   $form_id Form ID.
		 */
		return apply_filters( 'wpforms_surveys_reporting_fields_get_survey_field_data', $data, $form_id );
	}

	/**
	 * Get ignored entry ids.
	 * Return ids of abandoned and partial entries.
	 *
	 * @since 1.7.0
	 *
	 * @param int $form_id Form id.
	 *
	 * @return array
	 */
	private static function get_ignored_entry_ids( $form_id ) {

		$entry_handler = wpforms()->obj( 'entry' );

		if ( ! $entry_handler ) {
			return [];
		}

		$abandoned = $entry_handler->get_entries(
			[
				'form_id' => $form_id,
				'status'  => 'abandoned',
				'select'  => 'entry_ids',
			]
		);

		$partial = $entry_handler->get_entries(
			[
				'form_id' => $form_id,
				'status'  => 'partial',
				'select'  => 'entry_ids',
			]
		);

		$trash = $entry_handler->get_entries(
			[
				'form_id' => $form_id,
				'status'  => 'trash',
				'select'  => 'entry_ids',
			]
		);

		$spam = $entry_handler->get_entries(
			[
				'form_id' => $form_id,
				'status'  => 'spam',
				'select'  => 'entry_ids',
			]
		);

		return wp_list_pluck( array_merge( $abandoned, $partial, $trash, $spam ), 'entry_id' );
	}

	/**
	 * Return array of fields in a form that have survey reporting enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 * @param bool  $ids       Return field IDs when true, otherwise field arrays.
	 *
	 * @return bool|array
	 */
	public static function get_survey_fields( $form_data, $ids = false ) {

		if ( empty( $form_data['fields'] ) ) {
			return false;
		}

		$fields    = $form_data['fields'];
		$field_ids = [];

		if ( ! empty( $form_data['settings']['survey_enable'] ) ) {

			foreach ( $fields as $id => $field ) {
				if ( ! in_array( $field['type'], self::get_survey_field_types(), true ) ) {
					unset( $fields[ $id ] );
				} else {
					$field_ids[] = $id;
				}
			}
		} else {
			foreach ( $fields as $id => $field ) {
				if ( ! self::field_has_survey( $field ) ) {
					unset( $fields[ $id ] );
				} else {
					$field_ids[] = $id;
				}
			}
		}

		if ( $ids ) {
			return $field_ids;
		}

		return $fields;
	}

	/**
	 * Output HTML markup for a badge that indicates the field type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Field type slug.
	 *
	 * @return string
	 */
	public static function get_field_badge_markup( $type ) {

		$badge = [];

		switch ( $type ) {
			case 'text':
				$badge['name'] = esc_html__( 'Single Line Text', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-text-width';
				break;

			case 'textarea':
				$badge['name'] = esc_html__( 'Paragraph Text', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-paragraph';
				break;

			case 'select':
				$badge['name'] = esc_html__( 'Dropdown', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-caret-square-o-down';
				break;

			case 'radio':
				$badge['name'] = esc_html__( 'Multiple Choice', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-list-ul';
				break;

			case 'checkbox':
				$badge['name'] = esc_html__( 'Checkboxes', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-check-square-o';
				break;

			case 'rating':
				$badge['name'] = esc_html__( 'Rating', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-star';
				break;

			case 'likert_scale':
				$badge['name'] = esc_html__( 'Likert Scale', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-ellipsis-h';
				break;

			case 'net_promoter_score':
				$badge['name'] = esc_html__( 'Net Promoter Score', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-tachometer';
				break;
		}

		$badge = apply_filters( 'wpforms_surveys_reporting_fields_get_field_badge_markup', $badge );

		return sprintf(
			'<span class="badge"><i class="fa %s" aria-hidden="true"></i> %s</span>',
			sanitize_html_class( $badge['icon'] ),
			esc_html( $badge['name'] )
		);
	}

	/**
	 * Check if the provided fields from a form contain survey.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Found fields.
	 *
	 * @return bool
	 */
	public static function fields_has_survey( $fields ) {

		if ( ! empty( $fields ) && is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( self::field_has_survey( $field ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if a specific field has survey reporting enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function field_has_survey( $field, $form_data = [] ) {

		if ( isset( $field['survey'] ) && $field['survey'] === '1' ) {
			return true;
		} elseif ( ! empty( $form_data['settings']['survey_enable'] ) && in_array( $field['type'], self::get_survey_field_types(), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get an array of field types that support Surveys.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_survey_field_types() {

		return apply_filters(
			'wpforms_surveys_reporting_fields_get_survey_field_types',
			[ 'text', 'textarea', 'select', 'radio', 'checkbox', 'rating', 'likert_scale', 'net_promoter_score' ]
		);
	}

	/**
	 * Determine if it's the field with multiple feature.
	 *
	 * @since 1.6.3
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	public static function is_multiple( $field ) {

		if ( empty( $field['type'] ) ) {
			return false;
		}

		if (
			$field['type'] === 'checkbox' ||
			( $field['type'] === 'select' && ! empty( $field['multiple'] )
		) ) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize a stored answer value to a choice label for reporting.
	 *
	 * Collapses values like "Other: free text" into a single "Other" bucket
	 * and trims any dynamic suffix when the prefix matches an existing choice label.
	 *
	 * @since 1.16.0
	 *
	 * @param string $value Raw stored value.
	 * @param array  $field Field settings.
	 *
	 * @return string Normalized label to use for aggregation.
	 */
	private static function normalize_answer_value( string $value, array $field ): string {

		$value_trim = trim( $value );
		$sanitized  = sanitize_text_field( $value_trim );
		$labels     = self::get_choice_labels( $field );

		// Known choice? Return it.
		if ( in_array( $sanitized, $labels, true ) ) {
			return $sanitized;
		}

		// No valid choices array? Return default.
		if ( empty( $field['choices'] ) || ! is_array( $field['choices'] ) ) {
			return $value;
		}

		// Look for a defined the Other option.
		foreach ( $field['choices'] as $choice ) {
			if ( ! empty( $choice['other'] ) ) {
				return $choice['label'] ?? esc_html__( 'Other', 'wpforms-surveys-polls' );
			}
		}

		// Fallback: generic Other bucket.
		return esc_html__( 'Other', 'wpforms-surveys-polls' );
	}

	/**
	 * Get a sanitized list of choice labels for a field.
	 *
	 * @since 1.16.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	private static function get_choice_labels( array $field ): array {

		if ( empty( $field['choices'] ) || ! is_array( $field['choices'] ) ) {
			return [];
		}

		$labels = [];

		foreach ( $field['choices'] as $choice ) {
			if ( isset( $choice['label'] ) ) {
				$labels[] = sanitize_text_field( $choice['label'] );
			}
		}

		return $labels;
	}
}
