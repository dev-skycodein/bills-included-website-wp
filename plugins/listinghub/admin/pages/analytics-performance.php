<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

global $wpdb;

// Determine post type and tables.
$listing_type = get_option( 'ep_listinghub_url', 'listing' );
if ( $listing_type === '' ) {
	$listing_type = 'listing';
}
$view_table_name = defined( 'ep_listinghub_VIEW_LOG_TABLE' ) ? ep_listinghub_VIEW_LOG_TABLE : 'listinghub_view_log';
$view_table      = $wpdb->prefix . $view_table_name;

// Time range filter (today, last 7 days, last 30 days, all time).
$period          = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : '30_days';
$allowed_periods = array( 'today', '7_days', '30_days', 'all' );
if ( ! in_array( $period, $allowed_periods, true ) ) {
	$period = '30_days';
}
$since = null;
$now   = current_time( 'timestamp' );
switch ( $period ) {
	case 'today':
		$today_date = date_i18n( 'Y-m-d', $now );
		$since      = $today_date . ' 00:00:00';
		break;
	case '7_days':
		$since = date_i18n( 'Y-m-d H:i:s', $now - 7 * DAY_IN_SECONDS );
		break;
	case '30_days':
		$since = date_i18n( 'Y-m-d H:i:s', $now - 30 * DAY_IN_SECONDS );
		break;
	case 'all':
	default:
		$since = null;
		break;
}

// Aggregate views from the view log table for the selected period.
$rows = array();
if ( $since ) {
	$sql = $wpdb->prepare(
		"SELECT listing_id,
				COUNT(*) AS period_views,
				SUM(CASE WHEN is_session = 1 THEN 1 ELSE 0 END) AS period_sessions
		 FROM {$view_table}
		 WHERE created >= %s
		 GROUP BY listing_id
		 ORDER BY period_views DESC",
		$since
	);
} else {
	$sql = "SELECT listing_id,
				   COUNT(*) AS period_views,
				   SUM(CASE WHEN is_session = 1 THEN 1 ELSE 0 END) AS period_sessions
		FROM {$view_table}
		GROUP BY listing_id
		ORDER BY period_views DESC";
}
$view_rows = $wpdb->get_results( $sql, ARRAY_A );

if ( ! empty( $view_rows ) ) {
	foreach ( $view_rows as $row ) {
		$listing_id = (int) $row['listing_id'];
		if ( $listing_id <= 0 ) {
			continue;
		}

		$rows[] = array(
			'listing_id'        => $listing_id,
			'period_views'      => (int) $row['period_views'],
			'period_sessions'   => isset( $row['period_sessions'] ) ? (int) $row['period_sessions'] : 0,
		);
	}
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Listing performance', 'listinghub' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Views and unique sessions per listing in the selected period (no lifetime totals).', 'listinghub' ); ?>
			</p>

			<form method="get" class="mb-2">
				<input type="hidden" name="page" value="listinghub-analytics-performance" />
				<label for="listinghub-performance-period">
					<?php esc_html_e( 'Time range:', 'listinghub' ); ?>
				</label>
				<select id="listinghub-performance-period" name="period">
					<option value="today" <?php selected( $period, 'today' ); ?>><?php esc_html_e( 'Today', 'listinghub' ); ?></option>
					<option value="7_days" <?php selected( $period, '7_days' ); ?>><?php esc_html_e( 'Last 7 days', 'listinghub' ); ?></option>
					<option value="30_days" <?php selected( $period, '30_days' ); ?>><?php esc_html_e( 'Last 30 days', 'listinghub' ); ?></option>
					<option value="all" <?php selected( $period, 'all' ); ?>><?php esc_html_e( 'All time', 'listinghub' ); ?></option>
				</select>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'listinghub' ); ?></button>
			</form>

			<?php if ( empty( $rows ) ) : ?>
				<p><?php esc_html_e( 'No view data found yet for this period.', 'listinghub' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Listing', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Views (period)', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Session views (period)', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $item ) : ?>
							<?php
							$listing_id = (int) $item['listing_id'];
							$title      = get_the_title( $listing_id );
							$link       = get_edit_post_link( $listing_id, 'raw' );
							?>
							<tr>
								<td>
									<?php
									if ( $link && $title ) {
										echo '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . ' (#' . (int) $listing_id . ')</a>';
									} elseif ( $title ) {
										echo esc_html( $title ) . ' (#' . (int) $listing_id . ')';
									} else {
										echo '#' . (int) $listing_id;
									}
									?>
								</td>
								<td><?php echo esc_html( (string) $item['period_views'] ); ?></td>
								<td><?php echo esc_html( (string) $item['period_sessions'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

