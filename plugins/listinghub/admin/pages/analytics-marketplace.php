<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

global $wpdb;

// Determine listing post type and key tables.
$listing_type = get_option( 'ep_listinghub_url', 'listing' );
if ( $listing_type === '' ) {
	$listing_type = 'listing';
}

$view_table_name         = defined( 'ep_listinghub_VIEW_LOG_TABLE' ) ? ep_listinghub_VIEW_LOG_TABLE : 'listinghub_view_log';
$view_table              = $wpdb->prefix . $view_table_name;
$renter_login_table_name = defined( 'ep_listinghub_RENTER_LOGIN_LOG_TABLE' ) ? ep_listinghub_RENTER_LOGIN_LOG_TABLE : 'listinghub_renter_login_log';
$renter_login_table      = $wpdb->prefix . $renter_login_table_name;
$visitor_retention_table_name = defined( 'ep_listinghub_VISITOR_RETENTION_LOG_TABLE' ) ? ep_listinghub_VISITOR_RETENTION_LOG_TABLE : 'listinghub_visitor_retention_log';
$visitor_retention_table      = $wpdb->prefix . $visitor_retention_table_name;

$users_table    = $wpdb->users;
$posts_table    = $wpdb->posts;
$postmeta_table = $wpdb->postmeta;

// Active tab: active listings | active renters | accounts created | supply–demand.
$tab          = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'active_listings';
$allowed_tabs = array( 'active_listings', 'active_renters', 'accounts_created', 'supply_demand', 'visitor_retention' );
if ( ! in_array( $tab, $allowed_tabs, true ) ) {
	$tab = 'active_listings';
}

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

// ---------------------------------------------
// 1) Active listings count (per period, session views).
// ---------------------------------------------
$active_listing_rows    = array();
$total_active_listings  = 0;
$total_listing_sessions = 0;

if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $view_table ) ) === $view_table ) {
	if ( $since ) {
		$sql_active_listings = $wpdb->prepare(
			"SELECT listing_id, COUNT(*) AS session_views
			 FROM {$view_table}
			 WHERE created >= %s
			   AND is_session = 1
			 GROUP BY listing_id
			 ORDER BY session_views DESC",
			$since
		);
	} else {
		$sql_active_listings = "SELECT listing_id, COUNT(*) AS session_views
			 FROM {$view_table}
			 WHERE is_session = 1
			 GROUP BY listing_id
			 ORDER BY session_views DESC";
	}

	$active_listing_rows = $wpdb->get_results( $sql_active_listings, ARRAY_A );
	if ( ! empty( $active_listing_rows ) ) {
		foreach ( $active_listing_rows as $row ) {
			$listing_id = (int) $row['listing_id'];
			if ( $listing_id <= 0 ) {
				continue;
			}
			$session_views          = (int) $row['session_views'];
			$total_active_listings++;
			$total_listing_sessions += $session_views;
		}
	}
}

// ---------------------------------------------
// 2) Active renters (MAU/WAU) – logged-in renters only.
// ---------------------------------------------
$renter_login_rows         = array();
$total_active_renters      = 0;
$total_renter_login_events = 0;

if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $renter_login_table ) ) === $renter_login_table ) {
	if ( $since ) {
		$sql_renters = $wpdb->prepare(
			"SELECT user_id, COUNT(*) AS login_count
			 FROM {$renter_login_table}
			 WHERE created >= %s
			 GROUP BY user_id
			 ORDER BY login_count DESC",
			$since
		);
	} else {
		$sql_renters = "SELECT user_id, COUNT(*) AS login_count
			 FROM {$renter_login_table}
			 GROUP BY user_id
			 ORDER BY login_count DESC";
	}

	$renter_login_rows = $wpdb->get_results( $sql_renters, ARRAY_A );

	if ( ! empty( $renter_login_rows ) ) {
		foreach ( $renter_login_rows as $idx => $row ) {
			$user_id = (int) $row['user_id'];
			if ( $user_id <= 0 ) {
				unset( $renter_login_rows[ $idx ] );
				continue;
			}

			$user = get_user_by( 'ID', $user_id );
			if ( ! $user ) {
				unset( $renter_login_rows[ $idx ] );
				continue;
			}

			$roles = (array) $user->roles;
			if ( ! in_array( 'Renter', $roles, true ) ) {
				// Only count real renter accounts.
				unset( $renter_login_rows[ $idx ] );
				continue;
			}

			$login_count = (int) $row['login_count'];

			$total_active_renters++;
			$total_renter_login_events += $login_count;

			$renter_login_rows[ $idx ]['user']        = $user;
			$renter_login_rows[ $idx ]['login_count'] = $login_count;
		}
	}
}

// ---------------------------------------------
// 3) Renters & agencies account created.
// ---------------------------------------------
$account_rows        = array();
$new_renter_accounts = 0;
$new_agency_accounts = 0;

$user_query_args = array(
	'role__in' => array( 'Renter', 'Agency' ),
	'orderby'  => 'user_registered',
	'order'    => 'DESC',
	'fields'   => 'all',
	'number'   => 500,
);

if ( $since ) {
	$user_query_args['date_query'] = array(
		array(
			'after'     => $since,
			'inclusive' => true,
		),
	);
}

$users_for_accounts = get_users( $user_query_args );

if ( ! empty( $users_for_accounts ) ) {
	foreach ( $users_for_accounts as $user ) {
		if ( ! $user instanceof WP_User ) {
			continue;
		}
		$roles = (array) $user->roles;

		$type = '';
		if ( in_array( 'Renter', $roles, true ) ) {
			$type = 'Renter';
			$new_renter_accounts++;
		} elseif ( in_array( 'Agency', $roles, true ) ) {
			$type = 'Agency';
			$new_agency_accounts++;
		} else {
			continue;
		}

		$account_rows[] = array(
			'user'          => $user,
			'type'          => $type,
			'registered_ts' => mysql2date( 'U', $user->user_registered, true ),
		);
	}
}

// ---------------------------------------------
// 4) Supply–demand ratio by area (city).
// ---------------------------------------------
$supply_by_city        = array();
$demand_by_city        = array();
$total_supply_listings = 0;
$total_demand_sessions = 0;

// Supply: published listings grouped by city meta.
$sql_supply = $wpdb->prepare(
	"SELECT pm.meta_value AS city, COUNT(*) AS total_listings
	 FROM {$posts_table} p
	 INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id
	 WHERE p.post_type = %s
	   AND p.post_status = 'publish'
	   AND pm.meta_key = 'city'
	 GROUP BY pm.meta_value",
	$listing_type
);

$supply_rows = $wpdb->get_results( $sql_supply, ARRAY_A );
if ( ! empty( $supply_rows ) ) {
	foreach ( $supply_rows as $row ) {
		$city   = trim( (string) $row['city'] );
		$city   = $city !== '' ? $city : __( 'Unknown', 'listinghub' );
		$count  = (int) $row['total_listings'];
		$supply_by_city[ $city ] = $count;
		$total_supply_listings  += $count;
	}
}

// Demand: session views from view log joined to listing city in the selected period.
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $view_table ) ) === $view_table ) {
	if ( $since ) {
		$sql_demand = $wpdb->prepare(
			"SELECT pm.meta_value AS city, COUNT(*) AS total_sessions
			 FROM {$view_table} v
			 INNER JOIN {$postmeta_table} pm ON v.listing_id = pm.post_id
			 WHERE pm.meta_key = 'city'
			   AND v.is_session = 1
			   AND v.created >= %s
			 GROUP BY pm.meta_value",
			$since
		);
	} else {
		$sql_demand = "SELECT pm.meta_value AS city, COUNT(*) AS total_sessions
			 FROM {$view_table} v
			 INNER JOIN {$postmeta_table} pm ON v.listing_id = pm.post_id
			 WHERE pm.meta_key = 'city'
			   AND v.is_session = 1
			 GROUP BY pm.meta_value";
	}

	$demand_rows = $wpdb->get_results( $sql_demand, ARRAY_A );
	if ( ! empty( $demand_rows ) ) {
		foreach ( $demand_rows as $row ) {
			$city  = trim( (string) $row['city'] );
			$city  = $city !== '' ? $city : __( 'Unknown', 'listinghub' );
			$count = (int) $row['total_sessions'];
			$demand_by_city[ $city ] = $count;
			$total_demand_sessions  += $count;
		}
	}
}

// Merge supply and demand into one array keyed by city.
$supply_demand_rows = array();
$all_cities         = array_unique( array_merge( array_keys( $supply_by_city ), array_keys( $demand_by_city ) ) );

foreach ( $all_cities as $city ) {
	$supply = isset( $supply_by_city[ $city ] ) ? (int) $supply_by_city[ $city ] : 0;
	$demand = isset( $demand_by_city[ $city ] ) ? (int) $demand_by_city[ $city ] : 0;
	$ratio  = $supply > 0 ? round( $demand / $supply, 2 ) : null;

	$supply_demand_rows[] = array(
		'city'   => $city,
		'supply' => $supply,
		'demand' => $demand,
		'ratio'  => $ratio,
	);
}

// Sort by ratio descending (highest demand per listing first).
if ( ! empty( $supply_demand_rows ) ) {
	usort(
		$supply_demand_rows,
		static function ( $a, $b ) {
			$ra = $a['ratio'];
			$rb = $b['ratio'];
			if ( $ra === $rb ) {
				return strcmp( $a['city'], $b['city'] );
			}
			// Null ratios last.
			if ( $ra === null ) {
				return 1;
			}
			if ( $rb === null ) {
				return -1;
			}
			return $rb <=> $ra;
		}
	);
}

// ---------------------------------------------
// 5) Anonymous visitor retention (cookie-based, not logged in).
// ---------------------------------------------
$anon_new_in_period       = 0;
$anon_returning_in_period = 0;
$anon_active_in_period    = 0;
$anon_unique_all_time     = 0;
$anon_visit_days_all_time = 0;

if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $visitor_retention_table ) ) === $visitor_retention_table ) {
	if ( $since ) {
		// First-ever visit (by this browser id) falls inside the period.
		$anon_new_in_period = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT visitor_id FROM {$visitor_retention_table}
					WHERE user_id = 0
					GROUP BY visitor_id
					HAVING MIN(created) >= %s
				) t",
				$since
			)
		);
		// Seen before the period and at least one day-active row in the period.
		$anon_returning_in_period = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT visitor_id FROM {$visitor_retention_table}
					WHERE user_id = 0
					GROUP BY visitor_id
					HAVING MIN(created) < %s AND MAX(created) >= %s
				) t",
				$since,
				$since
			)
		);
		$anon_active_in_period = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT visitor_id FROM {$visitor_retention_table}
					WHERE user_id = 0 AND created >= %s
					GROUP BY visitor_id
				) t",
				$since
			)
		);
	} else {
		$anon_unique_all_time = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT visitor_id) FROM {$visitor_retention_table} WHERE user_id = 0"
		);
		$anon_visit_days_all_time = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$visitor_retention_table} WHERE user_id = 0"
		);
	}
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Marketplace health', 'listinghub' ); ?></h2>
			<?php
			$base_url = add_query_arg( array( 'page' => 'listinghub-analytics-marketplace' ), admin_url( 'admin.php' ) );
			?>
			<div class="nav-tab-wrapper mb-3">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'active_listings', 'period' => $period ), $base_url ) ); ?>" class="nav-tab <?php echo $tab === 'active_listings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Active listings', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'active_renters', 'period' => $period ), $base_url ) ); ?>" class="nav-tab <?php echo $tab === 'active_renters' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Active renters (MAU/WAU)', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'accounts_created', 'period' => $period ), $base_url ) ); ?>" class="nav-tab <?php echo $tab === 'accounts_created' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Accounts created', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'supply_demand', 'period' => $period ), $base_url ) ); ?>" class="nav-tab <?php echo $tab === 'supply_demand' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Supply–demand by area', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'visitor_retention', 'period' => $period ), $base_url ) ); ?>" class="nav-tab <?php echo $tab === 'visitor_retention' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Anonymous visitors', 'listinghub' ); ?></a>
			</div>

			<form method="get" class="mb-3">
				<input type="hidden" name="page" value="listinghub-analytics-marketplace" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
				<label for="listinghub-marketplace-period">
					<?php esc_html_e( 'Time range:', 'listinghub' ); ?>
				</label>
				<select id="listinghub-marketplace-period" name="period">
					<option value="today" <?php selected( $period, 'today' ); ?>><?php esc_html_e( 'Today', 'listinghub' ); ?></option>
					<option value="7_days" <?php selected( $period, '7_days' ); ?>><?php esc_html_e( 'Last 7 days', 'listinghub' ); ?></option>
					<option value="30_days" <?php selected( $period, '30_days' ); ?>><?php esc_html_e( 'Last 30 days', 'listinghub' ); ?></option>
					<option value="all" <?php selected( $period, 'all' ); ?>><?php esc_html_e( 'All time', 'listinghub' ); ?></option>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Apply', 'listinghub' ); ?></button>
			</form>

			<?php if ( $tab === 'active_listings' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Listings that received at least one session view in the selected period.', 'listinghub' ); ?>
				</p>
				<div class="row mb-3">
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Active listings (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_active_listings ); ?></p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Session views (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_listing_sessions ); ?></p>
						</div>
					</div>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Listing', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Session views (period)', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $active_listing_rows ) ) : ?>
							<tr>
								<td colspan="2"><?php esc_html_e( 'No active listings found for this period.', 'listinghub' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $active_listing_rows as $row ) : ?>
								<?php
								$listing_id    = (int) $row['listing_id'];
								$session_views = (int) $row['session_views'];
								$title         = get_the_title( $listing_id );
								$link          = get_edit_post_link( $listing_id, 'raw' );
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
									<td><?php echo esc_html( (string) $session_views ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php elseif ( $tab === 'active_renters' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Logged-in renters who have logged in during the selected period (WAU / MAU for renters).', 'listinghub' ); ?>
				</p>
				<div class="row mb-3">
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Active renters (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_active_renters ); ?></p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Renter login events (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_renter_login_events ); ?></p>
						</div>
					</div>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Renter', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Login count (period)', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $renter_login_rows ) ) : ?>
							<tr>
								<td colspan="2"><?php esc_html_e( 'No renter logins found for this period.', 'listinghub' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $renter_login_rows as $row ) : ?>
								<?php
								/** @var WP_User $user */
								$user        = $row['user'];
								$login_count = (int) $row['login_count'];
								?>
								<tr>
									<td><?php echo esc_html( $user->display_name . ' (#' . (int) $user->ID . ')' ); ?></td>
									<td><?php echo esc_html( (string) $login_count ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php elseif ( $tab === 'accounts_created' ) : ?>
				<p class="description">
					<?php esc_html_e( 'New renter and agency accounts created in the selected period.', 'listinghub' ); ?>
				</p>
				<div class="row mb-3">
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'New renter accounts (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $new_renter_accounts ); ?></p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'New agency accounts (period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $new_agency_accounts ); ?></p>
						</div>
					</div>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'User', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Type', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Registered', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $account_rows ) ) : ?>
							<tr>
								<td colspan="3"><?php esc_html_e( 'No new renter or agency accounts found for this period.', 'listinghub' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $account_rows as $row ) : ?>
								<?php
								/** @var WP_User $user */
								$user          = $row['user'];
								$type          = $row['type'];
								$registered_ts = (int) $row['registered_ts'];
								$registered    = $registered_ts ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $registered_ts ) : '';
								?>
								<tr>
									<td><?php echo esc_html( $user->display_name . ' (#' . (int) $user->ID . ')' ); ?></td>
									<td><?php echo esc_html( $type ); ?></td>
									<td><?php echo esc_html( $registered ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php elseif ( $tab === 'supply_demand' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Supply (published listings) and demand (session views) by area (city) for the selected period.', 'listinghub' ); ?>
				</p>
				<div class="row mb-3">
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Total listings (supply)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_supply_listings ); ?></p>
						</div>
					</div>
					<div class="col-md-3">
						<div class="card p-3">
							<strong><?php esc_html_e( 'Session views (demand, period)', 'listinghub' ); ?></strong>
							<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_demand_sessions ); ?></p>
						</div>
					</div>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Area (city)', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Supply – listings', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Demand – session views (period)', 'listinghub' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Demand per listing (ratio)', 'listinghub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $supply_demand_rows ) ) : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No supply/demand data found.', 'listinghub' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $supply_demand_rows as $row ) : ?>
								<tr>
									<td><?php echo esc_html( $row['city'] ); ?></td>
									<td><?php echo esc_html( (string) $row['supply'] ); ?></td>
									<td><?php echo esc_html( (string) $row['demand'] ); ?></td>
									<td>
										<?php
										if ( $row['ratio'] === null ) {
											echo '—';
										} else {
											echo esc_html( number_format_i18n( $row['ratio'], 2 ) );
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php elseif ( $tab === 'visitor_retention' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Visitors without a WordPress account. A random browser id is stored in first-party cookies; we log at most once per visitor per calendar day. New vs returning uses first-ever visit: “new” means the first logged day falls in the range; “returning” means they were seen before the range and again inside it. Logged-in users are not counted.', 'listinghub' ); ?>
				</p>
				<p class="description">
					<?php esc_html_e( 'Cookie consent: use the filter listinghub_visitor_retention_should_track to disable tracking until analytics cookies are allowed.', 'listinghub' ); ?>
				</p>
				<?php if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $visitor_retention_table ) ) !== $visitor_retention_table ) : ?>
					<p><?php esc_html_e( 'Visitor log table is not available yet. Load any front page as a guest once after upgrading the plugin.', 'listinghub' ); ?></p>
				<?php elseif ( $since ) : ?>
					<div class="row mb-3">
						<div class="col-md-3">
							<div class="card p-3">
								<strong><?php esc_html_e( 'New anonymous visitors (period)', 'listinghub' ); ?></strong>
								<p class="mt-1 mb-0"><?php echo esc_html( (string) $anon_new_in_period ); ?></p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card p-3">
								<strong><?php esc_html_e( 'Returning anonymous visitors (period)', 'listinghub' ); ?></strong>
								<p class="mt-1 mb-0"><?php echo esc_html( (string) $anon_returning_in_period ); ?></p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card p-3">
								<strong><?php esc_html_e( 'Distinct visitors active (period)', 'listinghub' ); ?></strong>
								<p class="mt-1 mb-0"><?php echo esc_html( (string) $anon_active_in_period ); ?></p>
							</div>
						</div>
					</div>
					<p class="description">
						<?php esc_html_e( '“Distinct visitors active” counts unique browser ids with at least one logged day in the range (new plus returning).', 'listinghub' ); ?>
					</p>
				<?php else : ?>
					<div class="row mb-3">
						<div class="col-md-3">
							<div class="card p-3">
								<strong><?php esc_html_e( 'Unique anonymous visitors (all time)', 'listinghub' ); ?></strong>
								<p class="mt-1 mb-0"><?php echo esc_html( (string) $anon_unique_all_time ); ?></p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card p-3">
								<strong><?php esc_html_e( 'Logged visitor-days (all time)', 'listinghub' ); ?></strong>
								<p class="mt-1 mb-0"><?php echo esc_html( (string) $anon_visit_days_all_time ); ?></p>
							</div>
						</div>
					</div>
					<p class="description">
						<?php esc_html_e( 'Each row is one calendar day per browser id. Clearing cookies or using another device creates a new id.', 'listinghub' ); ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

