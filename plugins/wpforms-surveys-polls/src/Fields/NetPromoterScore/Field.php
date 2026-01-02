<?php

namespace WPFormsSurveys\Fields\NetPromoterScore;

use WPForms\Forms\Fields\Addons\NetPromoterScore\Field as FieldLite;

/**
 * Net Promoter Score field.
 *
 * @since 1.1.0
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

		// Form frontend display enqueues.
		add_action( 'wpforms_frontend_css', [ $this, 'frontend_enqueues' ] );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_net_promoter_score', [ $this, 'field_properties' ], 5, 3 );

		// This field requires fieldset+legend instead of the field label in the modern markup mode.
		add_filter( "wpforms_frontend_modern_is_field_requires_fieldset_{$this->type}", '__return_true', PHP_INT_MAX, 2 );
	}

	/**
	 * Enqueues for the admin form builder.
	 *
	 * @since 1.1.0
	 */
	public function admin_builder_enqueues(): void {

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
	 * Enqueues for the frontend form display.
	 *
	 * @since 1.1.0
	 *
	 * @param array $forms Forms displayed on the current page.
	 */
	public function frontend_enqueues( $forms ): void {

		$min = wpforms_get_min_suffix();

		if (
			true === wpforms_has_field_type( 'net_promoter_score', $forms, true ) ||
			wpforms()->obj( 'frontend' )->assets_global()
		) {
			// JS.
			wp_enqueue_script(
				'wpforms-surveys-polls',
				wpforms_surveys_polls()->url . "assets/js/wpforms-surveys-polls{$min}.js",
				[],
				WPFORMS_SURVEYS_POLLS_VERSION,
				false
			);

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
	 * @since 1.1.0
	 * @deprecated 1.15.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	public function admin_builder_defaults( $field ) {

		_deprecated_function( __METHOD__, '1.15.0 of the WPForms Surveys and Polls plugin' );

		if ( $field['type'] === 'net_promoter_score' ) {

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
	 * @since 1.1.0
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
		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );

		for ( $i = 0; $i < 11; $i++ ) {
			$properties['inputs'][ $i ] = [
				'label'    => [
					'text' => $i,
				],
				'attr'     => [
					'name'  => "wpforms[fields][{$field_id}]",
					'value' => $i,
				],
				'class'    => $field['style'] === 'modern' ? [ 'wpforms-screen-reader-element', 'wpforms-net-promoter-score-option' ] : [ 'wpforms-net-promoter-score-option' ],
				'data'     => [],
				'id'       => "wpforms-{$form_id}-field_{$field_id}_{$i}",
				'required' => ! empty( $field['required'] ) ? 'required' : '',
			];
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	protected function get_field_populated_single_property_value( $raw_value, $input, $properties, $field ) {

		if ( ! is_string( $raw_value ) ) {
			return $properties;
		}

		$get_value = stripslashes( sanitize_text_field( $raw_value ) );

		if ( ! empty( $properties['inputs'][ $get_value ] ) ) {
			$properties['inputs'][ $get_value ]['attr']['checked'] = true;
		}

		return $properties;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field Field settings.
	 *
	 * @noinspection UnusedFunctionResultInspection
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Label.
		$this->field_option( 'label', $field );

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Style (theme).
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-surveys-polls' ),
				'tooltip' => esc_html__( 'Select the style for the net promoter score.', 'wpforms-surveys-polls' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? esc_attr( $field['style'] ) : 'modern',
				'options' => [
					'modern'  => esc_html__( 'Modern', 'wpforms-surveys-polls' ),
					'classic' => esc_html__( 'Classic', 'wpforms-surveys-polls' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'style',
				'content' => $lbl . $fld,
			]
		);

		// Size.
		$this->field_option( 'size', $field );

		// Start label.
		$lowest_lbl_label = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'lowest_label',
				'value' => esc_html__( 'Lowest Score Label', 'wpforms-surveys-polls' ),
			],
			false
		);

		$lowest_lbl_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'lowest_label',
				'value' => $field['lowest_label'] ?? esc_html__( 'Not at all Likely', 'wpforms-surveys-polls' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'lowest_label',
				'content' => $lowest_lbl_label . $lowest_lbl_field,
			]
		);

		// End label.
		$highest_lbl_label = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'highest_label',
				'value' => esc_html__( 'Highest Score Label', 'wpforms-surveys-polls' ),
			],
			false
		);

		$highest_lbl_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'highest_label',
				'value' => $field['highest_label'] ?? esc_html__( 'Extremely Likely', 'wpforms-surveys-polls' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'highest_label',
				'content' => $highest_lbl_label . $highest_lbl_field,
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'close',
			]
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$style = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';

		// Label.
		$this->field_preview_option( 'label', $field );

		// Lowest/Highest labels.
		$lowest_label  = $field['lowest_label'] ?? esc_html__( 'Not at all Likely', 'wpforms-surveys-polls' );
		$highest_label = $field['highest_label'] ?? esc_html__( 'Extremely Likely', 'wpforms-surveys-polls' );
		?>

		<table class="<?php echo esc_attr( $style ); ?>">
			<thead>
				<tr>
					<th colspan="11">
						<span class="not-likely"><?php echo esc_html( $lowest_label ); ?></span>
						<span class="extremely-likely"><?php echo esc_html( $highest_label ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				for ( $i = 0; $i < 11; $i++ ) {
					?>
					<td>
						<input type="radio" readonly>
						<label><?php echo absint( $i ); ?></label>
					</td>
					<?php
				}
				?>
				</tr>
			</tbody>
		</table>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute*/
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$inputs        = $field['properties']['inputs'];
		$style         = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$size          = ! empty( $field['size'] ) ? sanitize_html_class( $field['size'] ) : 'large';
		$lowest_label  = $field['lowest_label'] ?? '';
		$highest_label = $field['highest_label'] ?? '';
		?>

		<table class="wpforms-field-<?php echo esc_attr( $size ); ?> <?php echo esc_attr( $style ); ?>">
			<thead>
				<tr>
					<th colspan="11">
						<span class="not-likely"><?php echo esc_html( $lowest_label ); ?></span>
						<span class="extremely-likely"><?php echo esc_html( $highest_label ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				foreach ( $inputs as $input ) {
					echo '<td>';
						printf(
							'<input type="radio" %s %s>',
							wpforms_html_attributes( $input['id'], $input['class'], $input['data'], $input['attr'] ),
							esc_attr( $input['required'] )
						); // WPCS: XSS ok.
						echo '<label for="' . esc_attr( sanitize_html_class( $input['id'] ) ) . '">';
							echo esc_html( sanitize_text_field( $input['label']['text'] ) );
						echo '</label>';
					echo '</td>';
				}
				?>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Validate field on form submit event.
	 *
	 * @since 1.1.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$form_id = absint( $form_data['id'] );

		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) && $field_submit !== '0' ) {
			wpforms()->obj( 'process' )->errors[ $form_id ][ $field_id ] = wpforms_get_required_label();
		}

		// Allowed answers are 0-10.
		if ( ! empty( $field_submit ) && ( absint( $field_submit ) > 10 || absint( $field_submit ) < 0 ) ) {
			wpforms()->obj( 'process' )->errors[ $form_id ][ $field_id ] = esc_html__( 'Invalid score; must be 0-10.', 'wpforms-surveys-polls' );
		}
	}

	/**
	 * Format field.
	 *
	 * @since 1.1.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$value = $field_submit !== '' ? absint( $field_submit ) : '';
		$name  = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';

		// Set final field details.
		wpforms()->obj( 'process' )->fields[ $field_id ] = [
			'name'  => sanitize_text_field( $name ),
			'value' => sanitize_text_field( $value ),
			'id'    => absint( $field_id ),
			'type'  => $this->type,
		];
	}
}
