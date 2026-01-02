/* global WPFormsAdmin, wpforms_admin, wpforms_surveys, Chart, Choices, randomColor, pdfMake */

( function( $ ) {
	// Global settings access.
	let s;

	// Main Survey object.
	const WPFormsSurvey = {

		// Settings.
		settings : {
			fieldIDs:      JSON.parse( wpforms_surveys.field_ids ),
			fieldQueue:    '',
			fieldData:     {},
			charts:        {},
			exportSelects: {},
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			// Settings shortcut.
			s = this.settings;

			// Default chart settings.
			Chart.defaults.defaultFontSize = 14;

			// Custom chart plugin to apply a white background to all
			// generated charts. This is necessary for generating JPG exports to
			// avoid black backgrounds.
			Chart.register( {
				id: 'customCanvasBackgroundColor',
				beforeDraw( chart ) {
					const { ctx } = chart;
					ctx.save();
					ctx.globalCompositeOperation = 'destination-over';
					ctx.fillStyle = 'white';
					ctx.fillRect( 0, 0, chart.width, chart.height );
					ctx.restore();
				},
			} );

			// Document ready.
			$( WPFormsSurvey.ready );

			// Element actions.
			WPFormsSurvey.buildUIActions();
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready() {
			if ( _.isEmpty( s.fieldIDs ) ) {
				return;
			}

			// If there are survey fields, fetch their data.
			s.fieldQueue = s.fieldIDs;

			// Use cache is available, otherwise create new report via AJAX.
			if ( ! _.isEmpty( wpforms_surveys.cache ) ) {
				WPFormsSurvey.setFieldCache( wpforms_surveys.cache );
				WPFormsSurvey.generateReport();
			} else {
				WPFormsSurvey.getFieldData();
			}
		},

		/**
		 * Initilize export choices.
		 *
		 * @since 1.0.0
		 */
		initExportChoices() {
			$( '.survey-chart-export' ).each( function() {
				const $this = $( this ),
					id = $this.data( 'id' );

				s.exportSelects[ 'export_' + id ] = new Choices( $this[ 0 ], {
					searchEnabled : false,
					shouldSort : false,
				} );
			} );
		},

		/**
		 * Element binds and actions.
		 *
		 * @since 1.0.0
		 */
		buildUIActions() { // eslint-disable-line max-lines-per-function
			// Toggle chart display types.
			$( document ).on( 'click', '#wpforms-survey-report .chart-toggle', function( event ) {
				event.preventDefault();

				const $this = $( this ),
					current = $this.hasClass( 'current' );

				// If the user clicked the currently displayed chart, do nothing.
				if ( current ) {
					return;
				}

				const type = $this.data( 'type' ),
					$btnWrap = $this.closest( '.actions' ),
					fieldID = $btnWrap.data( 'field-id' );

				// Toggle "current" button class.
				$btnWrap.find( '.current' ).removeClass( 'current' );
				$this.addClass( 'current' );

				// Destroy old charts.
				s.charts[ 'chart_' + fieldID ].destroy();
				s.charts[ 'chart_hq_' + fieldID ].destroy();

				// Generate new chart.
				WPFormsSurvey.generateChart( fieldID, type, s.fieldData[ 'field_' + fieldID ].chart.labels, s.fieldData[ 'field_' + fieldID ].chart.data, false );

				// Generate high quality chart for exports.
				WPFormsSurvey.generateChart( fieldID, type, s.fieldData[ 'field_' + fieldID ].chart.labels, s.fieldData[ 'field_' + fieldID ].chart.data, true );
			} );

			// Toggle question displayed in the survey preview area.
			// Toggle chart display types.
			$( document ).on( 'change', '#wpforms-survey-preview-questions', function() {
				const $this = $( this ),
					fieldID = $this.val();

				// Reset field data and add new field to queue.
				s.fieldQueue = [];
				s.fieldData = {};
				s.fieldIDs = [];
				s.fieldQueue.push( fieldID );
				s.fieldIDs.push( fieldID );

				// Remove current field report.
				$( '#wpforms-survey-report' ).empty().prepend( wpforms_surveys.loader );

				// Generate new report.
				WPFormsSurvey.getFieldData();

				// AJAX request to save this field as the preferred survey
				// preview field.
				const data = {
					action:   'wpforms_surveys_set_preview_field',
					nonce:    wpforms_admin.nonce,
					/* eslint-disable camelcase */
					field_id: fieldID,
					form_id:  wpforms_surveys.form_id,
					/* eslint-enable camelcase */
				};
				$.post( wpforms_admin.ajax_url, data );
			} );

			// Export chart.
			$( document ).on( 'change', '.survey-chart-export', function() {
				const $this = $( this ),
					fieldID = $this.data( 'id' );
				let canvas,
					canvasCtx,
					canvasImg;

				if ( 'jpg' === $this.val() ) {
					canvas = $( ' #chart-' + fieldID + '-hq' )[ 0 ];
					canvasCtx = canvas.getContext( '2d' );
					canvasCtx.globalCompositeOperation = 'destination-over';
					canvasCtx.fillStyle = 'rgb(255,255,255)';
					canvasCtx.fillRect( 0, 0, canvas.scrollWidth, canvas.scrollHeight );
					canvasImg = canvas.toDataURL( 'image/jpeg', 1.0 );
					$( '#chart-' + fieldID + '-download' ).attr( 'href', canvasImg );

					// Triggering click on hidden elements with jQuery doesn't
					// appear to work so we use vanilla JS.
					document.getElementById( 'chart-' + fieldID + '-download' ).click();
				} else if ( 'pdf' === $this.val() ) {
					canvasImg = $( ' #chart-' + fieldID + '-hq' )[ 0 ].toDataURL( 'image/jpeg', 1.0 );
					const docDefinition = {
						pageSize: 'LETTER',
						content: [
							{
								text: s.fieldData[ 'field_' + fieldID ].question,
								fontSize: 20,
								margin: [ 0, 0, 0, 35 ],
							},
							{
								image: canvasImg,
								width: 530,
							},
						],
					};
					pdfMake.createPdf( docDefinition ).download( 'chart-field-' + fieldID + '.pdf' );
				} else if ( 'print' === $this.val() ) {
					window.open( wpforms_surveys.print + '&field_id=' + fieldID );
				}

				// Reset the select to show "Export";
				if ( $this.val() !== '1' ) {
					s.exportSelects[ 'export_' + fieldID ].setChoiceByValue( '1' );
				}
			} );

			// Open new window when viewing Survey print preview.
			$( document ).on( 'click', '.form-details-print-survey-report', function( event ) {
				event.preventDefault();

				window.open( $( this ).attr( 'href' ) );
			} );

			// Survey print preview - close window.
			$( document ).on( 'click', '#wpforms-survey-print-close', function( event ) {
				event.preventDefault();

				window.close();
			} );

			// Survey print preview - trigger browser print.
			$( document ).on( 'click', '#wpforms-survey-print', function( event ) {
				event.preventDefault();

				window.print();
			} );

			// Survey print preview - toggle question visiblity.
			$( document ).on( 'click', '#wpforms-survey-print-preview .question-toggle', function( event ) {
				event.preventDefault();

				const $this = $( this ),
					$question = $this.parent();

				$this.find( 'i' ).toggleClass( 'fa-chevron-down fa-chevron-right' );
				$question.toggleClass( 'no-print' ).find( '.chart-area, .table-wrap, .stats, .no-answers' ).toggle();
			} );

			// Clear cache before swapping pagination.
			$( document ).on( 'htmx:beforeSwap', WPFormsSurvey.clearCache );

			// Run ready action after the pagination is changed.
			$( document ).on( 'htmx:afterSwap', WPFormsSurvey.ready );
		},

		/**
		 * Get the compiled field data information and calculations.
		 *
		 * @since 1.0.0
		 */
		getFieldData() {
			const data = {
				action:      'wpforms_surveys_field_data',
				nonce:       wpforms_admin.nonce,
				/* eslint-disable camelcase */
				form_id:     wpforms_surveys.form_id,
				field_ids:   s.fieldQueue,
				entry_count: wpforms_surveys.entry_count,
				/* eslint-enable camelcase */
			};

			// Trigger AJAX to fetch field data.
			$.post( wpforms_admin.ajax_url, data, function( res ) {
				if ( ! res.success ) {
					$( '#wpforms-survey-loading' ).remove();
					$( '#wpforms-survey-report' ).append( res.data.message );

					return;
				}

				WPFormsAdmin.debug( res.data );

				// Store fields data.
				WPFormsSurvey.setFieldCache( res.data );

				// Remove from the queue.
				s.fieldQueue = [];

				// Proceed to generate the report with results.
				WPFormsSurvey.generateReport();
			} );
		},

		setFieldCache( fieldsData ) {
			// Store fields data.
			for ( const fieldData of Object.values( fieldsData ) ) {
				s.fieldData[ 'field_' + fieldData.id ] = fieldData;
			}
		},

		/**
		 * Clear the cache.
		 *
		 * @since 1.15.0
		 */
		clearCache() {
			wpforms_surveys.cache = false;
		},

		/**
		 * Decode HTML entities.
		 *
		 * @since 1.15.1
		 *
		 * @param {string} html Encoded HTML string.
		 *
		 * @return {string} Decoded HTML string.
		 */
		decodeHTMLEntities( html ) {
			const txt = document.createElement( 'textarea' );

			txt.innerHTML = html;

			return txt.value;
		},

		/**
		 * Makes an AJAX request with the final field data to build the cache.
		 *
		 * @since 1.0.0
		 * @deprecated 1.15.0
		 */
		cacheFieldData() {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsSurvey.cacheFieldData()" has been deprecated' );

			const data = {
				action:      'wpforms_surveys_cache_fields',
				nonce:       wpforms_admin.nonce,
				/* eslint-disable camelcase */
				field_data:  JSON.stringify( s.fieldData ),
				form_id:     wpforms_surveys.form_id,
				field_id:    wpforms_surveys.field_id,
				entry_count: wpforms_surveys.entry_count,
				/* eslint-enable camelcase */
			};

			// Trigger request.
			$.post( wpforms_admin.ajax_url, data );
		},

		/**
		 * Generate the report markup from the finalized field data.
		 *
		 * @since 1.0.0
		 */
		generateReport() {
			// Remove loading indicator.
			$( '#wpforms-survey-loading' ).remove();

			// Build markup from underscores template.
			const questionResults = wp.template( 'wpforms-question-results' );
			$( '#wpforms-survey-report' ).append( questionResults( s.fieldData ) );

			// Generate the chart for each question.
			$.each( s.fieldData, function( key, field ) {
				if ( ! _.isEmpty( field.chart.data ) ) {
					// Generate new chart.
					WPFormsSurvey.generateChart( field.id, field.chart.default, field.chart.labels, field.chart.data ); // jshint ignore:line

					// Generate high quality chart for exports.
					WPFormsSurvey.generateChart( field.id, field.chart.default, field.chart.labels, field.chart.data, true ); // jshint ignore:line
				}
			} );

			// Sort tables and enable sorting.
			$( '.wpforms-table-sorting' ).stupidtable();

			// Initialize ChoicesJS for fancy selects.
			WPFormsSurvey.initExportChoices();

			if ( 'list' === wpforms_surveys.type ) {
				$( '#wpforms-survey-preview .btn-wrap' ).show();
			}
		},

		/**
		 * Generates a chart with Chart.JS.
		 *
		 * @since 1.0.0
		 *
		 * @param {number}  fieldID     Field ID
		 * @param {string}  chartType   Field type
		 * @param {Array}   chartLabels Chart labels.
		 * @param {Array}   chartData   Chart data.
		 * @param {boolean} hq          High quality.
		 */
		generateChart( fieldID, chartType, chartLabels, chartData, hq ) { // eslint-disable-line max-lines-per-function, complexity
			let config = false;

			hq = hq || false;

			// Bar chart config.
			if ( 'bar' === chartType ) {
				config = {
					type: 'bar',
					data: {
						labels: chartLabels,
						datasets: [ {
							label: 'Data',
							hoverBackgroundColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							data: chartData,
						} ],
					},
					options: {
						responsive: ! hq,
						maintainAspectRatio: false,
						scales: {
							x: {
								grid: {
									display: false,
								},
								ticks: {
									font: {
										size: hq ? 20 : 14,
									},
									callback( value ) {
										const label = WPFormsSurvey.decodeHTMLEntities( this.getLabelForValue( value ) );
										if ( typeof label === 'string' && label.length > 20 ) {
											return `${ label.substring( 0, 20 ) }...`;
										}

										return label;
									},
								},
							},
							y: {
								min: 0,
								max: 100,
								ticks: {
									stepSize: 20,
									color: '#999999',
									font: {
										size: hq ? 20 : 11,
									},
									callback( value ) {
										return value + '%';
									},
								},
							},
						},
						plugins: {
							customCanvasBackgroundColor: {},
							legend: {
								display: false,
							},
							tooltip: {
								callbacks: {
									title( item ) {
										return item[ 0 ].label;
									},
									label( item ) {
										const value = item.raw;
										const total = s.fieldData[ 'field_' + fieldID ].chart.totals[ item.dataIndex ];
										return value + '% (' + total + ')';
									},
									labelColor() {
										return {
											backgroundColor: '#2b8fd2',
										};
									},
								},
							},
						},
					},
				};
			} else if ( 'bar-h' === chartType ) {
				config = {
					type: 'bar',
					data: {
						labels: chartLabels,
						datasets: [ {
							label: 'Data',
							hoverBackgroundColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							data: chartData,
						} ],
					},
					options: {
						indexAxis: 'y',
						responsive: ! hq,
						maintainAspectRatio: false,
						scales: {
							x: {
								min: 0,
								max: 100,
								ticks: {
									color: '#999999',
									font: {
										size: hq ? 20 : 11,
									},
									callback( value ) {
										return `${ value }%`;
									},
								},
							},
							y: {
								grid: {
									display: false,
								},
								ticks: {
									font: {
										size: hq ? 20 : 14,
									},
									callback( value ) {
										const label = WPFormsSurvey.decodeHTMLEntities( this.getLabelForValue( value ) );
										if ( typeof label === 'string' && label.length > 20 ) {
											return `${ label.substring( 0, 20 ) }...`;
										}

										return label;
									},
								},
							},
						},
						plugins: {
							legend: {
								display: false,
							},
							tooltip: {
								callbacks: {
									title( item ) {
										return item[ 0 ].label;
									},
									label( item ) {
										const value = item.raw;
										const total = s.fieldData[ 'field_' + fieldID ].chart.totals[ item.dataIndex ];
										return value + '% (' + total + ')';
									},
									labelColor() {
										return {
											backgroundColor: '#2b8fd2',
										};
									},
								},
							},
						},
					},
				};
			} else if ( 'pie' === chartType ) {
				config = {
					type: 'pie',
					data: {
						labels: chartLabels.map( String ),
						datasets: [ {
							hoverBackgroundColor: '#2b8fd2',
							data: chartData.map( String ),
							backgroundColor: randomColor( { luminosity: 'light', count: chartData.length } ),
						} ],
					},
					options: {
						responsive: ! hq,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								position: 'right',
								align: 'start',
								labels : {
									font: {
										size: hq ? 20 : 14,
									},
									padding: 15,
									generateLabels( chart ) {
										const data = chart.data;
										if ( data.labels.length && data.datasets.length ) {
											return data.labels.map( function( label, i ) {
												const meta = chart.getDatasetMeta( 0 );
												const ds = data.datasets[ 0 ];
												const arcOpts = chart.options.elements.arc;
												const segmentColor = ds.backgroundColor[ i ] || arcOpts.backgroundColor;
												if ( typeof label === 'string' && label.length > 20 ) {
													label = label.substring( 0, 20 ) + '...';
												}
												return {
													text: label,
													fillStyle: segmentColor,
													strokeStyle: segmentColor,
													lineWidth: 1,
													hidden: isNaN( ds.data[ i ] ) || meta.data[ i ].hidden,
													index: i,
												};
											} );
										}
										return [];
									},
								},
							},
							tooltip: {
								callbacks: {
									title() {
										return '';
									},
									label( item ) {
										const label = item.label;
										const value = item.formattedValue;
										const total = s.fieldData[ `field_${ fieldID }` ].chart.totals[ item.dataIndex ];

										return `${ label } - ${ value }% (${ total })`;
									},
									labelColor() {
										return {
											backgroundColor: '#2b8fd2',
										};
									},
								},
							},
						},
					},
				};
			} else if ( 'line' === chartType ) {
				config = {
					type: 'line',
					data: {
						labels: chartLabels,
						datasets: [ {
							label: 'Data',
							borderColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							fill: false,
							tension: 0.4,
							data: chartData,
						} ],
					},
					options: {
						responsive: ! hq,
						maintainAspectRatio: false,
						scales: {
							x: {
								grid: {
									display: false,
								},
								ticks: {
									font: {
										size: hq ? 20 : 14,
									},
									callback( value ) {
										const label = WPFormsSurvey.decodeHTMLEntities( this.getLabelForValue( value ) );
										if ( typeof label === 'string' && label.length > 20 ) {
											return `${ label.substring( 0, 20 ) }...`;
										}

										return label;
									},
								},
							},
							y: {
								beginAtZero: true,
								max: 100,
								ticks: {
									stepSize: 20,
									color: '#999999',
									font: {
										size: hq ? 20 : 11,
									},
									callback( value ) {
										return `${ value }%`;
									},
								},
							},
						},
						plugins: {
							legend: {
								display: false,
							},
							tooltip: {
								callbacks: {
									title( item ) {
										return item[ 0 ].label;
									},
									label( item ) {
										const value = item.raw;
										const total = s.fieldData[ 'field_' + fieldID ].chart.totals[ item.dataIndex ];
										return value + '% (' + total + ')';
									},
								},
							},
						},
					},
				};
			}

			if ( config ) {
				if ( hq ) {
					s.charts[ 'chart_hq_' + fieldID ] = new Chart( 'chart-' + fieldID + '-hq', config );
				} else {
					s.charts[ 'chart_' + fieldID ] = new Chart( 'chart-' + fieldID, config );
				}
			}
		},
	};

	WPFormsSurvey.init();
}( jQuery ) );
