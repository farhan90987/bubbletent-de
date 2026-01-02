<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logger = WPWOOF_Logger::instance();

$current_page = isset( $_GET['log_page'] ) ? max( 1, intval( $_GET['log_page'] ) ) : 1;
$per_page = 50;
$offset = ( $current_page - 1 ) * $per_page;

$filter_type = isset( $_GET['log_type'] ) ? sanitize_text_field( $_GET['log_type'] ) : '';
$filter_feed_id = isset( $_GET['log_feed_id'] ) ? intval( $_GET['log_feed_id'] ) : 0;
$filter_level = isset( $_GET['log_level'] ) ? sanitize_text_field( $_GET['log_level'] ) : '';

if ( isset( $_POST['clear_logs'] ) && check_admin_referer( 'wpwoof_clear_logs' ) ) {
	$clear_type = ! empty( $_POST['clear_type'] ) ? sanitize_text_field( $_POST['clear_type'] ) : null;
	$clear_feed_id = ! empty( $_POST['clear_feed_id'] ) ? intval( $_POST['clear_feed_id'] ) : null;

	$logger->clear_all_logs( $clear_type, $clear_feed_id );

	echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Logs cleared successfully.', 'wpwoof' ) . '</p></div>';
}

$args = array(
	'limit' => $per_page,
	'offset' => $offset
);

if ( $filter_type ) {
	$args['type'] = $filter_type;
}
if ( $filter_feed_id ) {
	$args['feed_id'] = $filter_feed_id;
}
if ( $filter_level ) {
	$args['level'] = $filter_level;
}

$logs = $logger->get_logs( $args );
$total_logs = $logger->count_logs( $args );
$total_pages = ceil( $total_logs / $per_page );

$feeds = wpwoof_get_feeds();
?>

<div class="wpwoof-logs-container">
	<h2><?php _e( 'Logs', 'wpwoof' ); ?></h2>

	<div class="wpwoof-logs-filters">
		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>">
			<input type="hidden" name="tab" value="logs">

			<select name="log_type">
				<option value=""><?php _e( 'All Types', 'wpwoof' ); ?></option>
				<option value="<?php echo WPWOOF_Logger::LOG_TYPE_GENERAL; ?>" <?php selected( $filter_type, WPWOOF_Logger::LOG_TYPE_GENERAL ); ?>>
					<?php _e( 'General', 'wpwoof' ); ?>
				</option>
				<option value="<?php echo WPWOOF_Logger::LOG_TYPE_FEED; ?>" <?php selected( $filter_type, WPWOOF_Logger::LOG_TYPE_FEED ); ?>>
					<?php _e( 'Feed', 'wpwoof' ); ?>
				</option>
				<option value="<?php echo WPWOOF_Logger::LOG_TYPE_MIGRATION; ?>" <?php selected( $filter_type, WPWOOF_Logger::LOG_TYPE_MIGRATION ); ?>>
					<?php _e( 'Migration', 'wpwoof' ); ?>
				</option>
			</select>

			<select name="log_feed_id">
				<option value=""><?php _e( 'All Feeds', 'wpwoof' ); ?></option>
				<?php foreach ( $feeds as $feed ) :
					$feed_data = unserialize( $feed['option_value'] );
					if ( isset( $feed_data['feed_name'] ) ) : ?>
						<option value="<?php echo esc_attr( $feed['option_id'] ); ?>" <?php selected( $filter_feed_id, $feed['option_id'] ); ?>>
							<?php echo esc_html( $feed_data['feed_name'] ); ?>
						</option>
					<?php endif;
				endforeach; ?>
			</select>

			<select name="log_level">
				<option value=""><?php _e( 'All Levels', 'wpwoof' ); ?></option>
				<option value="<?php echo WPWOOF_Logger::LOG_LEVEL_INFO; ?>" <?php selected( $filter_level, WPWOOF_Logger::LOG_LEVEL_INFO ); ?>>
					<?php _e( 'Info', 'wpwoof' ); ?>
				</option>
				<option value="<?php echo WPWOOF_Logger::LOG_LEVEL_WARNING; ?>" <?php selected( $filter_level, WPWOOF_Logger::LOG_LEVEL_WARNING ); ?>>
					<?php _e( 'Warning', 'wpwoof' ); ?>
				</option>
				<option value="<?php echo WPWOOF_Logger::LOG_LEVEL_ERROR; ?>" <?php selected( $filter_level, WPWOOF_Logger::LOG_LEVEL_ERROR ); ?>>
					<?php _e( 'Error', 'wpwoof' ); ?>
				</option>
			</select>

			<button type="submit" class="button"><?php _e( 'Filter', 'wpwoof' ); ?></button>
			<a href="<?php echo admin_url( 'admin.php?page=wpwoof-settings&tab=logs' ); ?>" class="button">
				<?php _e( 'Reset', 'wpwoof' ); ?>
			</a>
		</form>

		<form method="post" class="wpwoof-clear-logs-form" onsubmit="return confirm('<?php _e( 'Are you sure you want to clear logs?', 'wpwoof' ); ?>');">
			<?php wp_nonce_field( 'wpwoof_clear_logs' ); ?>
			<input type="hidden" name="clear_type" value="<?php echo esc_attr( $filter_type ); ?>">
			<input type="hidden" name="clear_feed_id" value="<?php echo esc_attr( $filter_feed_id ); ?>">
			<button type="submit" name="clear_logs" class="button button-secondary">
				<?php _e( 'Clear Filtered Logs', 'wpwoof' ); ?>
			</button>
		</form>
	</div>

	<?php if ( ! empty( $logs ) ) : ?>
		<table class="wp-list-table widefat fixed striped wpwoof-logs-table">
			<thead>
				<tr>
					<th class="wpwoof-log-date"><?php _e( 'Date', 'wpwoof' ); ?></th>
					<th class="wpwoof-log-type"><?php _e( 'Type', 'wpwoof' ); ?></th>
					<th class="wpwoof-log-feed"><?php _e( 'Feed', 'wpwoof' ); ?></th>
					<th class="wpwoof-log-level"><?php _e( 'Level', 'wpwoof' ); ?></th>
					<th class="wpwoof-log-message"><?php _e( 'Message', 'wpwoof' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $logs as $log ) : 
					$log_class = 'wpwoof-log-level-' . esc_attr( $log['log_level'] );
					$feed_name = '';
					if ( $log['feed_id'] ) {
						$feed_config = wpwoof_get_feed( $log['feed_id'] );
						if ( ! is_wp_error( $feed_config ) && isset( $feed_config['feed_name'] ) ) {
							$feed_name = $feed_config['feed_name'];
						} else {
							$feed_name = sprintf( __( 'Deleted Feed (ID: %d)', 'wpwoof' ), $log['feed_id'] );
						}
					}
				?>
					<tr class="<?php echo $log_class; ?>">
						<td class="wpwoof-log-date">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['created_at'] ) ) ); ?>
						</td>
						<td class="wpwoof-log-type">
							<span class="wpwoof-log-badge wpwoof-log-type-<?php echo esc_attr( $log['log_type'] ); ?>">
								<?php echo esc_html( ucfirst( $log['log_type'] ) ); ?>
							</span>
						</td>
						<td class="wpwoof-log-feed">
							<?php echo $feed_name ? esc_html( $feed_name ) : '—'; ?>
						</td>
						<td class="wpwoof-log-level">
							<span class="wpwoof-log-badge wpwoof-log-level-badge-<?php echo esc_attr( $log['log_level'] ); ?>">
								<?php echo esc_html( ucfirst( $log['log_level'] ) ); ?>
							</span>
						</td>
                        <td class="wpwoof-log-message">
                            <?php
                            $message = esc_html( $log['message'] );
                            if ( strpos( $log['message'], 'Stack trace:' ) !== false || strpos( $log['message'], '#0' ) !== false ) {
                                echo '<pre>' . $message . '</pre>';
                            } else {
                                echo $message;
                            }
                            ?>
                        </td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="wpwoof-logs-pagination">
				<?php
				$base_url = add_query_arg( array(
					'page' => 'wpwoof-settings',
					'tab' => 'logs',
					'log_type' => $filter_type,
					'log_feed_id' => $filter_feed_id,
					'log_level' => $filter_level
				), admin_url( 'admin.php' ) );

				echo paginate_links( array(
					'base' => add_query_arg( 'log_page', '%#%', $base_url ),
					'format' => '',
					'current' => $current_page,
					'total' => $total_pages,
					'prev_text' => __( '&laquo; Previous', 'wpwoof' ),
					'next_text' => __( 'Next &raquo;', 'wpwoof' )
				) );
				?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<p class="wpwoof-no-logs"><?php _e( 'No logs found.', 'wpwoof' ); ?></p>
	<?php endif; ?>
</div>
