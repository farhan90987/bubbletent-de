<?php

namespace WPFormsSurveys\Fields\LikertScale;

use WPForms\Forms\Fields\Addons\LikertScale\Field as FieldLite;

/**
 * Likert Scale field.
 *
 * @since 1.0.0
 */
class Field extends FieldLite {

	/**
	 * Add hooks.
	 *
	 * @since 1.8.0
	 */
	protected function hooks() {

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues_before', [ $this, 'admin_builder_enqueues' ] );

		// Template for form builder preview.
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'admin_builder_template' ] );

		// Form frontend display enqueues.
		add_action( 'wpforms_frontend_css', [ $this, 'frontend_enqueues' ] );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_likert_scale', [ $this, 'field_properties' ], 5, 3 );

		// Customize the information saved in the entry_fields database.
		add_filter( 'wpforms_entry_save_fields', [ $this, 'save_field' ], 10, 3 );

		// This field requires fieldset+legend instead of the field label in the modern markup mode.
		add_filter( "wpforms_frontend_modern_is_field_requires_fieldset_{$this->type}", '__return_true', PHP_INT_MAX, 2 );
	}

	/**
	 * Enqueues for the admin form builder.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_enqueues() {

		$min = wpforms_get_min_suffix();

		// JavaScript.
		wp_enqueue_script(
			'wpforms-survey-builder',
			wpforms_surveys_polls()->url . "assets/js/admin-survey-builder{$min}.js",
			[ 'jquery', 'wpforms-builder', 'wpforms-utils' ],
			WPFORMS_SURVEYS_POLLS_VERSION,
			false
		);
	}

	/**
	 * Template for form builder preview.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_template() {

		?>
		<script type="text/html" id="tmpl-wpforms-likert-scale-preview">
			<# var rowCount = 1; #>
			<table class="{{ data.style }} {{ data.singleClass }}">
				<thead>
					<tr>
					<# if ( ! data.singleRow ) { #>
						<th style="width:20%;"></th>
					<# } #>
					<# _.each( data.columns, function( columnData, key ) {  #>
						<th style="width:{{ data.width }}%;">{{ columnData.value }}</th>
					<# }) #>
					</tr>
				</thead>
				<tbody>
					<# _.each( data.rows, function( rowData, key ) {  #>
						<# if ( ! data.singleRow || ( data.singleRow && rowCount === 1 ) ) { #>
							<tr>
							<# if ( ! data.singleRow ) { #>
								<th>{{ rowData.value }}</th>
							<# } #>
							<# _.each( data.columns, function( columnData, key ) {  #>
								<td>
									<input type="{{ data.inputType }}" readonly>
									<label></label>
								</td>
							<# }) #>
							</tr>
						<# } #>
						<# rowCount++ #>
					<# }) #>
				</tbody>
			</table>
		</script>
		<?php
	}

	/**
	 * Enqueues for the frontend form display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms displayed on the current page.
	 */
	public function frontend_enqueues( $forms ) {

		$min = wpforms_get_min_suffix();

		if (
			true === wpforms_has_field_type( 'likert_scale', $forms, true ) ||
			wpforms()->obj( 'frontend' )->assets_global()
		) {
			// CSS.
			wp_enqueue_style(
				'wpforms-surveys-polls',
				wpforms_surveys_polls()->url . "assets/css/wpforms-surveys-polls{$min}.css",
				[],
				WPFORMS_SURVEYS_POLLS_VERSION
			);
		}
	}

	/**
	 * New field default settings in the form builder.
	 *
	 * @since 1.0.0
	 * @deprecated 1.15.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	public function admin_builder_defaults( $field ) {

		_deprecated_function( __METHOD__, '1.15.0 of the WPForms Surveys and Polls plugin' );

		if ( $field['type'] === 'likert_scale' ) {

			// Enable survey tracking.
			$field['survey'] = '1';

			// Due to the contents, this field is best rendered as large.
			$field['size'] = 'large';
		}

		return $field;
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		// Remove primary input since this is a custom field.
		// Remove for attribute from the label as there is no id for it.
		unset( $properties['inputs']['primary'], $properties['label']['attr']['for'] );

		// Define data.
		$form_id        = absint( $form_data['id'] );
		$field_id       = absint( $field['id'] );
		$field['style'] = $field['style'] ?? $this->default_settings['style'];

		// Create the inputs.
		foreach ( (array) $field['columns'] as $column_key => $column ) {
			foreach ( $field['rows'] as $row_key => $row ) {
				$properties['inputs'][ "r{$row_key}_c{$column_key}" ] = [
					'attr'     => [
						'name'  => "wpforms[fields][{$field_id}][{$row_key}]" . ( ! empty( $field['multiple_responses'] ) ? '[]' : '' ),
						'value' => $column_key,
					],
					'block'    => [],
					'class'    => $field['style'] === 'modern' ? [ 'wpforms-screen-reader-element', 'wpforms-likert-scale-option' ] : [ 'wpforms-likert-scale-option' ],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}_{$row_key}_{$column_key}",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden' => 1,
						'value'  => sanitize_text_field( "{$row} {$column}" ),
					],
				];

				// Add input error class if needed.
				if ( ! empty( $properties['error']['value'][ "r{$row_key}" ] ) ) {
					$properties['inputs'][ "r{$row_key}_c{$column_key}" ]['class'][] = 'wpforms-error';
				}

				// Add input required class if needed.
				if ( ! empty( $field['required'] ) ) {
					$properties['inputs'][ "r{$row_key}_c{$column_key}" ]['class'][] = 'wpforms-field-required';
				}
			}
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	protected function get_field_populated_single_property_value( $raw_value, $input, $properties, $field ) {

		if ( empty( $raw_value ) ) {
			return $properties;
		}

		/*
		 * $input is different depending on the source of the population.
		 * Dynamic: 'r2_c4' or similar string.
		 * Fallback: number which is a row (starting from 1), and we need to get the value (column) from an original submitted source.
		 */
		preg_match( '/^r(\d+)_c(\d+)$/i', $input, $matches );

		$inputs = [];

		if ( empty( $matches ) || ! is_array( $matches ) ) {
			$inputs = $this->get_fallback_inputs( $raw_value, $input, $field );
		} elseif ( is_string( $raw_value ) ) {
			// We are in Dynamic mode.
			$inputs = [ $input ];
		}

		foreach ( $inputs as $key ) {
			if ( isset( $properties['inputs'][ $key ] ) ) {
				$properties['inputs'][ $key ]['attr']['checked'] = true;
			}
		}

		return $properties;
	}

	/**
	 * Get the inputs for fallback (failed form submission).
	 *
	 * During fallback and multiple responses per row, we get single row but several columns as a raw value.
	 * We need to process this situation differently, and check each of that selected row/column pairs.
	 *
	 * @since 1.9.0
	 *
	 * @param string|array|mixed $raw_value Value from either $_GET or $_POST for a field.
	 * @param string             $input     Represent a subfield inside the field. Maybe empty.
	 * @param array              $field     Current field specific data.
	 *
	 * @return array
	 */
	private function get_fallback_inputs( $raw_value, $input, $field ) {

		$inputs = [];

		if ( empty( $field['multiple_responses'] ) ) {
			/*
			 * Single response per row.
			 * We have this structure ($input => column):
			 * Array (
			 *     [2] => 2
			 *     [3] => 3
			 * )
			 */
			if ( ! is_numeric( $raw_value ) ) {
				return [];
			}

			$inputs[] = 'r' . (int) $input . '_c' . (int) $raw_value;
		} else {
			/*
			 * Several responses per row.
			 * We have this structure ($input => {#}=>column):
			 * Array (
			 *     [2] => Array (
			 *                0 => 2
			 *            )
			 *     [3] => Array (
			 *                0 => 1,
			 *                1 => 4
			 *            )
			 *  )
			 */
			if ( ! is_array( $raw_value ) ) {
				return [];
			}

			foreach ( $raw_value as $column ) {
				$inputs[] = 'r' . (int) $input . '_c' . (int) $column;
			}
		}

		return $inputs;
	}

	/**
	 * Customize the information stored in the entry_field database.
	 *
	 * We need to include both the "pretty" and raw values in the database.
	 * The pretty values allow the field values to be searched,
	 * and the raw values are used for survey reporting calculations.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field settings.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  Entry ID.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_field( $field, $form_data, $entry_id ) {

		if ( ! empty( $field['type'] ) && $this->type === $field['type'] && ! empty( $field['value'] ) ) {
			$field['value'] = wp_json_encode(
				[
					'value'     => $field['value'],
					'value_raw' => $field['value_raw'],
				]
			);
		}

		return $field;
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlWrongAttributeValue
	 * @noinspection HtmlUnknownAttribute
	 */
	public function field_display( $field, $deprecated, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Define data.
		$inputs     = $field['properties']['inputs'];
		$input_type = ! empty( $field['multiple_responses'] ) ? 'checkbox' : 'radio';
		$style      = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$single     = ! empty( $field['single_row'] );
		$size       = ! empty( $field['size'] ) ? sanitize_html_class( $field['size'] ) : 'large';
		$width      = 80;

		if ( ! empty( $field['columns'] ) ) {
			$width = $single ? round( 100 / count( $field['columns'] ), 4 ) : round( 80 / count( $field['columns'] ), 4 );
		}
		?>

		<table class="wpforms-field-<?php echo esc_attr( $size ); ?> <?php echo esc_attr( $style ); ?><?php echo $single ? ' single-row' : ''; ?>">
			<thead>
				<tr>
					<?php
					if ( ! $single ) {
						echo '<th style="width:20%;"></th>';
					}
					foreach ( $field['columns'] as $column ) {
						printf(
							'<th style="width:%d%%;">%s</th>',
							esc_attr( $width ),
							esc_html( sanitize_text_field( $column ) )
						);
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( (array) $field['rows'] as $row_key => $row ) {
					echo '<tr>';
						if ( ! $single ) {
							echo '<th>';
								echo esc_html( sanitize_text_field( $row ) );
								$this->field_display_error( "r{$row_key}", $field );
							echo '</th>';
						}
						foreach ( $field['columns'] as $column_key => $column ) {
							$input = $inputs[ "r{$row_key}_c{$column_key}" ];

							echo '<td>';
							echo '<div class="wpforms-likert-scale-mobile-flex">';
									printf(
										'<span class="wpforms-likert-scale-mobile-label">%s</span>',
										esc_html( sanitize_text_field( $column ) )
									);
									printf(
										'<input type="%s" %s %s>',
										esc_attr( $input_type ),
										wpforms_html_attributes( $input['id'], $input['class'], $input['data'], $input['attr'] ),
										$input['required'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);
									echo '<label for="' . esc_attr( sanitize_html_class( $input['id'] ) ) . '">';
										echo ! empty( $input['sublabel']['hidden'] ) ? '<span class="wpforms-screen-reader-element">' : '<span>';
											echo esc_html( sanitize_text_field( $input['sublabel']['value'] ) );
										echo '</span>';
									echo '</label>';
								echo '</div>';
							echo '</td>';
						}
					echo '</tr>';

					if ( $single ) {
						break;
					}
				}
				?>
			</tbody>
		</table>
		<?php
		// Display errors for single row fields after the table since we do
		// not display the row legend column.
		if ( $single ) {
			$row_keys = array_keys( $field['rows'] );

			$this->field_display_error( "r{$row_keys[0]}", $field );
		}
	}

	/**
	 * Display field input errors if present.
	 *
	 * @since 1.15.0
	 *
	 * @param string $key   Input key.
	 * @param array  $field Field data and settings.
	 */
	public function field_display_error( $key, $field ): void {

		// Need an error.
		if ( empty( $field['properties']['error']['value'][ $key ] ) ) {
			return;
		}

		// Get the input key.
		$column_key = 'c' . array_keys( $field['columns'] ?? [] )[0];
		$input_key  = isset( $field['properties']['inputs'][ $key ]['id'] ) ? $key : $key . '_' . $column_key;

		printf(
			'<label class="wpforms-error" for="%s">%s</label>',
			esc_attr( $field['properties']['inputs'][ $input_key ]['id'] ?? '' ),
			esc_html( $field['properties']['error']['value'][ $key ] )
		);
	}

	/**
	 * Validate field on form submit event.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$form_id  = absint( $form_data['id'] );
		$required = wpforms_get_required_label();
		$row_keys = array_keys( $form_data['fields'][ $field_id ]['rows'] );
		$single   = ! empty( $form_data['fields'][ $field_id ]['single_row'] );
		$x        = 1;

		// The validation logic for this field is only applicable if the field
		// is configured as required.
		if ( empty( $form_data['fields'][ $field_id ]['required'] ) ) {
			return;
		}

		foreach ( $row_keys as $row_key ) {
			if ( ! isset( $field_submit[ $row_key ] ) ) {
				wpforms()->obj( 'process' )->errors[ $form_id ][ $field_id ][ "r{$row_key}" ] = $required;
			}

			if ( $single && $x === 1 ) {
				break;
			}

			++$x;
		}
	}

	/**
	 * Format field.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

		// Define data.
		$name       = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';
		$value      = '';
		$value_raw  = ! empty( $field_submit ) ? $this->sanitize_field_submit( (array) $field_submit ) : '';
		$rows       = $form_data['fields'][ $field_id ]['rows'];
		$columns    = $form_data['fields'][ $field_id ]['columns'];
		$single     = ! empty( $form_data['fields'][ $field_id ]['single_row'] );
		$show_empty = apply_filters( 'wpforms_likert_scale_show_empty', false );

		// Process submitted data.
		if ( ! empty( $value_raw ) ) {

			$x = 1;

			foreach ( $rows as $row_key => $row_label ) {

				$answers  = isset( $value_raw[ $row_key ] ) ? (array) $value_raw[ $row_key ] : [];
				$selected = [];

				foreach ( $columns as $column_id => $column_label ) {
					if ( in_array( $column_id, $answers, true ) ) {
						$selected[] = sanitize_text_field( $column_label );
					}
				}

				if (
					$x > 1
					&& ! empty( $value )
					&& ( ! empty( $selected ) || $show_empty )
				) {
					$value .= "\n";
				}

				if ( ! empty( $selected ) ) {
					if ( $single ) {
						$value .= implode( ', ', $selected );
					} else {
						$value .= sanitize_text_field( $row_label ) . ":\n" . implode( ', ', $selected );
					}
				} elseif ( $show_empty ) {
					$value .= sanitize_text_field( $row_label ) . ":\n" . esc_html__( '(Empty)', 'wpforms-surveys-polls' );
				}

				if ( $single ) {
					break;
				}

				++$x;
			}
		}

		// Set final field details.
		wpforms()->obj( 'process' )->fields[ $field_id ] = [
			'name'      => sanitize_text_field( $name ),
			'value'     => $value,
			'value_raw' => $value_raw,
			'id'        => absint( $field_id ),
			'type'      => $this->type,
		];
	}

	/**
	 * Sanitize the submitted data. All values and keys should integers.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_submit Submitted data for Likert field.
	 *
	 * @return array
	 */
	public function sanitize_field_submit( $field_submit = [] ) {

		if ( ! is_array( $field_submit ) || ! count( $field_submit ) ) {
			return [];
		}

		foreach ( $field_submit as $key => $value ) {
			if ( is_int( $key ) ) {
				if ( is_array( $value ) ) {
					$field_submit[ $key ] = $this->sanitize_field_submit( $value );
				} else {
					$field_submit[ $key ] = absint( $value );
				}
			} else {
				unset( $field_submit[ $key ] );
			}
		}

		return $field_submit;
	}

	/**
	 * Get field name for an ajax error message.
	 *
	 * @since 1.6.3
	 *
	 * @param string|mixed    $name  Field name for error triggered.
	 * @param array           $field Field settings.
	 * @param array           $props List of properties.
	 * @param string|string[] $error Error message.
	 *
	 * @return string
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function ajax_error_field_name( $name, $field, $props, $error ) {

		$name = (string) $name;

		if ( $field['type'] !== 'likert_scale' || empty( $props['inputs'] ) || empty( $field['single_row'] ) ) {
			return $name;
		}

		foreach ( $props['inputs'] as $key => $input ) {
			if ( 0 !== strpos( $key, 'r1_' ) ) {
				unset( $props['inputs'][ $key ] );
			}
		}

		$input = end( $props['inputs'] );

		return (string) isset( $input['attr']['name'] ) ? $input['attr']['name'] : '';
	}
}
