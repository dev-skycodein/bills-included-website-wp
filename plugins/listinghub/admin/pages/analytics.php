<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

global $wpdb;
// Table name can be derived from constant when available, otherwise fall back to default.
$table_name = defined( 'ep_listinghub_SEARCH_LOG_TABLE' ) ? ep_listinghub_SEARCH_LOG_TABLE : 'listinghub_search_log';
$table      = $wpdb->prefix . $table_name;
// We use an aggregate view (filter usage counts), so just load a bounded number of recent rows.
$max_logs = 5000;

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
		// From midnight today in the site's timezone.
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

// CSV export
if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
	if ( $since ) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE created >= %s ORDER BY created DESC LIMIT 5000",
				$since
			),
			ARRAY_A
		);
	} else {
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created DESC LIMIT 5000", ARRAY_A );
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="listinghub-search-log-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Date', 'Keyword', 'Sort', 'Filters (params)', 'Result count' ) );
	foreach ( $rows as $row ) {
		$params_display = $row['params'];
		if ( $params_display ) {
			$decoded = json_decode( $params_display, true );
			if ( is_array( $decoded ) ) {
				$params_display = implode( ', ', array_map( function ( $k, $v ) {
					return $k . '=' . ( is_array( $v ) ? implode( '|', $v ) : $v );
				}, array_keys( $decoded ), $decoded ) );
			}
		}
		fputcsv( $out, array(
			$row['created'],
			$row['keyword'],
			$row['sort'],
			$params_display,
			$row['result_count'],
		) );
	}
	fclose( $out );
	exit;
}

// Load recent logs for aggregation.
if ( $since ) {
	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE created >= %s ORDER BY created DESC LIMIT %d",
			$since,
			$max_logs
		),
		ARRAY_A
	);
} else {
	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} ORDER BY created DESC LIMIT %d",
			$max_logs
		),
		ARRAY_A
	);
}

// Build per-filter usage counts: each key/value pair gets its own row with a count.
$filter_counts = array();
if ( ! empty( $logs ) ) {
	foreach ( $logs as $row ) {
		if ( empty( $row['params'] ) ) {
			continue;
		}
		$decoded = json_decode( $row['params'], true );
		if ( ! is_array( $decoded ) ) {
			continue;
		}
		foreach ( $decoded as $k => $v ) {
			// Skip sort; it has little value as a \"filter\".
			if ( $k === 'sfsort_listing' ) {
				continue;
			}
			$values = is_array( $v ) ? $v : array( $v );
			foreach ( $values as $val ) {
				if ( $val === '' ) {
					continue;
				}
				$key = $k . '|' . $val;
				if ( ! isset( $filter_counts[ $key ] ) ) {
					$filter_counts[ $key ] = array(
						'raw_key' => $k,
						'raw_val' => $val,
						'count'   => 0,
					);
				}
				$filter_counts[ $key ]['count']++;
			}
		}
	}
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics', 'listinghub' ); ?></h2>
		</div>
	</div>

	<div class="nav-tab-wrapper mb-3">
		<span class="nav-tab nav-tab-active"><?php esc_html_e( 'Search & filters', 'listinghub' ); ?></span>
	</div>

	<div class="metabox-holder">
		<p class="description"><?php esc_html_e( 'Filter usage across recent listing searches (one row per filter/value pair).', 'listinghub' ); ?></p>

		<form method="get" class="mb-2">
			<input type="hidden" name="page" value="listinghub-analytics" />
			<label for="listinghub-analytics-period">
				<?php esc_html_e( 'Time range:', 'listinghub' ); ?>
			</label>
			<select id="listinghub-analytics-period" name="period">
				<option value="today" <?php selected( $period, 'today' ); ?>><?php esc_html_e( 'Today', 'listinghub' ); ?></option>
				<option value="7_days" <?php selected( $period, '7_days' ); ?>><?php esc_html_e( 'Last 7 days', 'listinghub' ); ?></option>
				<option value="30_days" <?php selected( $period, '30_days' ); ?>><?php esc_html_e( 'Last 30 days', 'listinghub' ); ?></option>
				<option value="all" <?php selected( $period, 'all' ); ?>><?php esc_html_e( 'All time', 'listinghub' ); ?></option>
			</select>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'listinghub' ); ?></button>
		</form>

		<p class="mb-2">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'listinghub-analytics', 'export' => 'csv', 'period' => $period ), admin_url( 'admin.php' ) ) ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Download CSV (last 5000)', 'listinghub' ); ?>
			</a>
		</p>
		<?php if ( empty( $filter_counts ) ) : ?>
			<p><?php esc_html_e( 'No filter usage data yet.', 'listinghub' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Filter', 'listinghub' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Value', 'listinghub' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Uses', 'listinghub' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$post_type = get_option( 'ep_listinghub_url', 'listing' );
					$tax_cat   = $post_type . '-category';
					$tax_loc   = $post_type . '-locations';

					$label_map = array(
						'sflisting-locations'  => __( 'Location', 'listinghub' ),
						'sflisting-category'   => __( 'Category', 'listinghub' ),
						'sfpostcode'           => __( 'Postcode', 'listinghub' ),
						'sfbedrooms'           => __( 'Bedrooms', 'listinghub' ),
						'sfproperty_type'      => __( 'Property Type', 'listinghub' ),
						'sfsearch_price_min'   => __( 'Min price', 'listinghub' ),
						'sfsearch_price_max'   => __( 'Max price', 'listinghub' ),
						'input-search'         => __( 'Keyword', 'listinghub' ),
					);

					foreach ( $filter_counts as $item ) :
						$raw_key = $item['raw_key'];
						$raw_val = $item['raw_val'];
						$count   = (int) $item['count'];

						if ( isset( $label_map[ $raw_key ] ) ) {
							$label = $label_map[ $raw_key ];
						} else {
							$label = $raw_key;
							if ( strpos( $raw_key, 'sf' ) === 0 ) {
								$label = substr( $raw_key, 2 ); // strip sf
								$label = str_replace( '_', ' ', $label );
								$label = ucwords( $label );
							}
						}

						$value_html = '';
						// Locations.
						if ( $raw_key === 'sflisting-locations' ) {
							$term = get_term_by( 'slug', $raw_val, $tax_loc );
							if ( $term && ! is_wp_error( $term ) ) {
								$link = get_term_link( $term );
								if ( ! is_wp_error( $link ) ) {
									$value_html = '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
								} else {
									$value_html = esc_html( $term->name );
								}
							} else {
								$value_html = esc_html( $raw_val );
							}
						}
						// Categories.
						elseif ( $raw_key === 'sflisting-category' ) {
							$term = get_term_by( 'slug', $raw_val, $tax_cat );
							if ( $term && ! is_wp_error( $term ) ) {
								$link = get_term_link( $term );
								if ( ! is_wp_error( $link ) ) {
									$value_html = '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
								} else {
									$value_html = esc_html( $term->name );
								}
							} else {
								$value_html = esc_html( $raw_val );
							}
						} else {
							$value_html = esc_html( $raw_val );
						}
						?>
						<?php
						?>
						<tr>
							<td><?php echo esc_html( $label ); ?></td>
							<td><?php echo $value_html ? wp_kses_post( $value_html ) : '—'; ?></td>
							<td><?php echo esc_html( (string) $count ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
