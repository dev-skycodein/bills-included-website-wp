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
			'listing_id'      => $listing_id,
			'period_views'    => (int) $row['period_views'],
			'period_sessions' => isset( $row['period_sessions'] ) ? (int) $row['period_sessions'] : 0,
		);
	}
}

// Compute listing completeness scores (structural, not time-based).
$completeness_meta_keys = array(
	'monthly_rent',
	'search_price',
	'bedrooms',
	'bathrooms',
	'address',
	'city',
	'google_maps_link',
	'latitude',
	'longitude',
	'company_name',
	'phone',
	'contact-email',
	'contact_web',
	'source_listing_url',
	'availability_status',
	'property_type',
);

$completeness_rows = array();
$all_listing_ids   = get_posts(
	array(
		'post_type'      => $listing_type,
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

if ( ! empty( $all_listing_ids ) ) {
	$total_fields = count( $completeness_meta_keys );
	foreach ( $all_listing_ids as $listing_id ) {
		$filled = 0;
		foreach ( $completeness_meta_keys as $meta_key ) {
			$value = get_post_meta( $listing_id, $meta_key, true );
			if ( is_array( $value ) ) {
				$non_empty = array_filter(
					array_map(
						static function ( $v ) {
							return is_string( $v ) ? trim( (string) $v ) : $v;
						},
						$value
					),
					static function ( $v ) {
						return $v !== '' && $v !== null;
					}
				);
				if ( ! empty( $non_empty ) ) {
					$filled++;
				}
			} else {
				$val_str = trim( (string) $value );
				if ( $val_str !== '' ) {
					$filled++;
				}
			}
		}
		if ( $total_fields > 0 ) {
			$score = (int) round( ( $filled / $total_fields ) * 100 );
		} else {
			$score = 0;
		}
		$completeness_rows[] = array(
			'listing_id' => (int) $listing_id,
			'score'      => $score,
			'filled'     => $filled,
			'total'      => $total_fields,
		);
	}
}

// Sort completeness rows by score desc, then by listing ID.
if ( ! empty( $completeness_rows ) ) {
	usort(
		$completeness_rows,
		static function ( $a, $b ) {
			if ( $a['score'] === $b['score'] ) {
				return $a['listing_id'] <=> $b['listing_id'];
			}
			return $b['score'] <=> $a['score'];
		}
	);
}

// Time to first enquiry: compute per listing (lifetime, based on first listinghub_message with listing_id meta).
$first_enquiry_rows = array();
$posts_table        = $wpdb->posts;
$postmeta_table     = $wpdb->postmeta;

$sql_fe = $wpdb->prepare(
	"SELECT pm.meta_value AS listing_id, MIN(p.post_date_gmt) AS first_enquiry_gmt
	 FROM {$posts_table} p
	 INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id
	 WHERE p.post_type = %s
	   AND p.post_status = 'private'
	   AND pm.meta_key = 'listing_id'
	 GROUP BY pm.meta_value",
	'listinghub_message'
);
$fe_rows = $wpdb->get_results( $sql_fe, ARRAY_A );

if ( ! empty( $fe_rows ) ) {
	foreach ( $fe_rows as $row ) {
		$listing_id = (int) $row['listing_id'];
		if ( $listing_id <= 0 ) {
			continue;
		}
		$listing = get_post( $listing_id );
		if ( ! $listing || $listing->post_type !== $listing_type ) {
			continue;
		}

		$created_ts       = get_post_time( 'U', true, $listing );
		$first_enquiry_ts = $row['first_enquiry_gmt'] ? mysql2date( 'U', $row['first_enquiry_gmt'], true ) : 0;
		if ( ! $created_ts || ! $first_enquiry_ts ) {
			continue;
		}

		$diff_seconds = max( 0, (int) $first_enquiry_ts - (int) $created_ts );

		$first_enquiry_rows[] = array(
			'listing_id'      => $listing_id,
			'created_ts'      => $created_ts,
			'first_ts'        => $first_enquiry_ts,
			'diff_seconds'    => $diff_seconds,
		);
	}
}

if ( ! empty( $first_enquiry_rows ) ) {
	usort(
		$first_enquiry_rows,
		static function ( $a, $b ) {
			return $a['diff_seconds'] <=> $b['diff_seconds'];
		}
	);
}

// Sub-tabs within Listing performance: views | completeness | first_enquiry.
$subtab          = isset( $_GET['subtab'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab'] ) ) : 'views';
$allowed_subtabs = array( 'views', 'completeness', 'first_enquiry' );
if ( ! in_array( $subtab, $allowed_subtabs, true ) ) {
	$subtab = 'views';
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Listing performance', 'listinghub' ); ?></h2>
			<?php
			$base_url = add_query_arg( array( 'page' => 'listinghub-analytics-performance', 'period' => $period ), admin_url( 'admin.php' ) );
			?>
			<div class="nav-tab-wrapper mb-3">
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'views', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'views' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Views', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'completeness', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'completeness' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Completeness score', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'first_enquiry', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'first_enquiry' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Time to first enquiry', 'listinghub' ); ?></a>
			</div>

			<?php if ( $subtab === 'views' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Views and unique sessions per listing in the selected period (no lifetime totals).', 'listinghub' ); ?>
				</p>

				<form method="get" class="mb-2">
					<input type="hidden" name="page" value="listinghub-analytics-performance" />
					<input type="hidden" name="subtab" value="views" />
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

			<?php elseif ( $subtab === 'completeness' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Percentage of key fields filled in for each listing (based on current post meta).', 'listinghub' ); ?>
				</p>

				<?php if ( empty( $completeness_rows ) ) : ?>
					<p><?php esc_html_e( 'No listings found to calculate completeness.', 'listinghub' ); ?></p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Listing', 'listinghub' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Completeness', 'listinghub' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $completeness_rows as $item ) : ?>
								<?php
								$listing_id = (int) $item['listing_id'];
								$score      = (int) $item['score'];
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
									<td>
										<div style="background:#e5e5ea;border-radius:4px;overflow:hidden;width:160px;height:14px;display:inline-block;vertical-align:middle;margin-right:8px;">
											<div style="background:#2e7ff5;width:<?php echo esc_attr( $score ); ?>%;height:100%;"></div>
										</div>
										<strong><?php echo esc_html( (string) $score ); ?>%</strong>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php elseif ( $subtab === 'first_enquiry' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Time between listing creation and the first contact form submission (via “Contact via Bills” popup).', 'listinghub' ); ?>
				</p>

				<?php if ( empty( $first_enquiry_rows ) ) : ?>
					<p><?php esc_html_e( 'No enquiries found yet to calculate this metric.', 'listinghub' ); ?></p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Listing', 'listinghub' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Time to first enquiry', 'listinghub' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $first_enquiry_rows as $item ) : ?>
								<?php
								$listing_id   = (int) $item['listing_id'];
								$title        = get_the_title( $listing_id );
								$link         = get_edit_post_link( $listing_id, 'raw' );
								$created_ts   = (int) $item['created_ts'];
								$first_ts     = (int) $item['first_ts'];
								$diff_seconds = (int) $item['diff_seconds'];
								$human_diff   = $diff_seconds > 0 ? human_time_diff( $created_ts, $first_ts ) : __( '0 seconds', 'listinghub' );
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
									<td>
										<strong><?php echo esc_html( $human_diff ); ?></strong>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

