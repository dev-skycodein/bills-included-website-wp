<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'listinghub' ) );
}

global $wpdb;

// Load all agencies (gsli_agency CPT) and determine claimed/unclaimed.
$agency_args = array(
	'post_type'      => 'gsli_agency',
	'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'no_found_rows'  => true,
);
$agency_ids = get_posts( $agency_args );

$agencies  = array();
$claimed   = 0;
$unclaimed = 0;

if ( ! empty( $agency_ids ) ) {
	foreach ( $agency_ids as $agency_id ) {
		$owner_id = (int) get_post_meta( $agency_id, 'agency_owner', true );
		$status   = $owner_id > 0 ? 'claimed' : 'unclaimed';
		if ( 'claimed' === $status ) {
			$claimed++;
		} else {
			$unclaimed++;
		}

		$agencies[ $agency_id ] = array(
			'agency_id' => (int) $agency_id,
			'owner_id'  => $owner_id,
			'status'    => $status,
		);
	}
}

// Count claim starts: cya_claim CPT with agency_post_id.
$posts_table    = $wpdb->posts;
$postmeta_table = $wpdb->postmeta;

$sql_claims = $wpdb->prepare(
	"SELECT pm.meta_value AS agency_post_id,
	        COUNT(*) AS total_claims
	 FROM {$posts_table} p
	 INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id
	 WHERE p.post_type = %s
	   AND p.post_status <> 'trash'
	   AND pm.meta_key = 'agency_post_id'
	 GROUP BY pm.meta_value",
	'cya_claim'
);

$claim_rows       = $wpdb->get_results( $sql_claims, ARRAY_A );
$claims_by_agency = array();
$total_claims     = 0;

if ( ! empty( $claim_rows ) ) {
	foreach ( $claim_rows as $row ) {
		$agency_post_id = (int) $row['agency_post_id'];
		$count          = (int) $row['total_claims'];
		$total_claims  += $count;
		$claims_by_agency[ $agency_post_id ] = $count;
	}
}

// Merge claim counts into agency rows.
foreach ( $agencies as $id => &$agency ) {
	$agency['claims'] = isset( $claims_by_agency[ $id ] ) ? (int) $claims_by_agency[ $id ] : 0;
}
unset( $agency );

// Listings per agency: count published listings with agency_post_id meta.
$listing_post_type = get_option( 'ep_listinghub_url', 'listing' );
if ( ! $listing_post_type ) {
	$listing_post_type = 'listing';
}

$posts_table    = $wpdb->posts;
$postmeta_table = $wpdb->postmeta;

$sql_listings = $wpdb->prepare(
	"SELECT pm.meta_value AS agency_post_id,
	        COUNT(*) AS total_listings
	 FROM {$posts_table} p
	 INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id
	 WHERE p.post_type = %s
	   AND p.post_status = 'publish'
	   AND pm.meta_key = 'agency_post_id'
	 GROUP BY pm.meta_value",
	$listing_post_type
);

$listing_rows         = $wpdb->get_results( $sql_listings, ARRAY_A );
$listings_by_agency   = array();
$total_listings_count = 0;

if ( ! empty( $listing_rows ) ) {
	foreach ( $listing_rows as $row ) {
		$agency_post_id              = (int) $row['agency_post_id'];
		$count                       = (int) $row['total_listings'];
		$total_listings_count       += $count;
		$listings_by_agency[ $agency_post_id ] = $count;
	}
}

foreach ( $agencies as $id => &$agency ) {
	$agency['listings'] = isset( $listings_by_agency[ $id ] ) ? (int) $listings_by_agency[ $id ] : 0;
}
unset( $agency );

// Agency logins/sessions: aggregate from login log table.
$login_table_name = defined( 'ep_listinghub_AGENCY_LOGIN_LOG_TABLE' ) ? ep_listinghub_AGENCY_LOGIN_LOG_TABLE : 'listinghub_agency_login_log';
$login_table      = $wpdb->prefix . $login_table_name;

$logins_by_agency   = array();
$total_login_events = 0;

if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $login_table ) ) === $login_table ) {
	$sql_logins = "SELECT agency_post_id, COUNT(*) AS total_logins
		FROM {$login_table}
		GROUP BY agency_post_id";

	$login_rows = $wpdb->get_results( $sql_logins, ARRAY_A );
	if ( ! empty( $login_rows ) ) {
		foreach ( $login_rows as $row ) {
			$agency_post_id              = (int) $row['agency_post_id'];
			$count                       = (int) $row['total_logins'];
			$total_login_events         += $count;
			$logins_by_agency[ $agency_post_id ] = $count;
		}
	}
}

foreach ( $agencies as $id => &$agency ) {
	$agency['logins'] = isset( $logins_by_agency[ $id ] ) ? (int) $logins_by_agency[ $id ] : 0;
}
unset( $agency );

// Sub-tabs: claims overview | listings per agency | logins/sessions.
$subtab          = isset( $_GET['subtab'] ) ? sanitize_text_field( wp_unslash( $_GET['subtab'] ) ) : 'claims';
$allowed_subtabs = array( 'claims', 'listings', 'logins' );
if ( ! in_array( $subtab, $allowed_subtabs, true ) ) {
	$subtab = 'claims';
}

include 'header.php';
?>
<div class="listinghub-settings mt-3">
	<div class="row">
		<div class="col-md-12">
			<h2 class="mb-3"><?php esc_html_e( 'Analytics – Agency engagement', 'listinghub' ); ?></h2>
			<?php
			$base_url = add_query_arg( array( 'page' => 'listinghub-analytics-agency' ), admin_url( 'admin.php' ) );
			?>
			<div class="nav-tab-wrapper mb-3">
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'claims', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'claims' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Claims', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'listings', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'listings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Listings per agency', 'listinghub' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'subtab', 'logins', $base_url ) ); ?>" class="nav-tab <?php echo $subtab === 'logins' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Agency logins/sessions', 'listinghub' ); ?></a>
			</div>

			<?php if ( $subtab === 'claims' ) : ?>
				<p class="description">
					<?php esc_html_e( 'Counts of claimed vs unclaimed agencies and how many agency claims have been requested.', 'listinghub' ); ?>
				</p>
			<?php elseif ( $subtab === 'listings' ) : ?>
				<p class="description">
					<?php esc_html_e( 'How many listings each agency currently has assigned.', 'listinghub' ); ?>
				</p>
			<?php elseif ( $subtab === 'logins' ) : ?>
				<p class="description">
					<?php esc_html_e( 'How often agency owners have logged in (sessions per agency).', 'listinghub' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $subtab === 'claims' ) : ?>
		<div class="row mb-3">
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total agencies', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) count( $agencies ) ); ?></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Claimed agencies', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) $claimed ); ?></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Unclaimed agencies', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) $unclaimed ); ?></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total claim requests', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_claims ); ?></p>
				</div>
			</div>
		</div>
	<?php elseif ( $subtab === 'listings' ) : ?>
		<div class="row mb-3">
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total agencies', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) count( $agencies ) ); ?></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total listings (all agencies)', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_listings_count ); ?></p>
				</div>
			</div>
		</div>
	<?php elseif ( $subtab === 'logins' ) : ?>
		<div class="row mb-3">
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total agencies', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) count( $agencies ) ); ?></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card p-3">
					<strong><?php esc_html_e( 'Total login sessions (all agencies)', 'listinghub' ); ?></strong>
					<p class="mt-1 mb-0"><?php echo esc_html( (string) $total_login_events ); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="row">
		<div class="col-md-12">
			<?php if ( empty( $agencies ) ) : ?>
				<p><?php esc_html_e( 'No agencies found.', 'listinghub' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Agency', 'listinghub' ); ?></th>
							<th scope="col">
								<?php
								if ( $subtab === 'claims' ) {
									esc_html_e( 'Status', 'listinghub' );
								} elseif ( $subtab === 'listings' ) {
									esc_html_e( 'Listings (published)', 'listinghub' );
								} elseif ( $subtab === 'logins' ) {
									esc_html_e( 'Login sessions', 'listinghub' );
								}
								?>
							</th>
							<?php if ( $subtab === 'claims' ) : ?>
								<th scope="col"><?php esc_html_e( 'Agency owner', 'listinghub' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Claim requests', 'listinghub' ); ?></th>
							<?php elseif ( $subtab === 'listings' ) : ?>
								<th scope="col"><?php esc_html_e( 'Claim status', 'listinghub' ); ?></th>
							<?php elseif ( $subtab === 'logins' ) : ?>
								<th scope="col"><?php esc_html_e( 'Agency owner', 'listinghub' ); ?></th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $agencies as $agency ) : ?>
							<?php
							$agency_id   = (int) $agency['agency_id'];
							$status      = $agency['status'] === 'claimed' ? __( 'Claimed', 'listinghub' ) : __( 'Unclaimed', 'listinghub' );
							$owner_id    = (int) $agency['owner_id'];
							$claims      = (int) $agency['claims'];
							$listings    = isset( $agency['listings'] ) ? (int) $agency['listings'] : 0;
							$logins      = isset( $agency['logins'] ) ? (int) $agency['logins'] : 0;
							$title       = get_the_title( $agency_id );
							$link        = get_edit_post_link( $agency_id, 'raw' );
							$owner_label = '';
							if ( $owner_id > 0 ) {
								$user = get_user_by( 'ID', $owner_id );
								if ( $user ) {
									$owner_label = $user->display_name . ' (#' . (int) $owner_id . ')';
								} else {
									$owner_label = '#' . (int) $owner_id;
								}
							} else {
								$owner_label = '—';
							}
							?>
							<tr>
								<td>
									<?php
									if ( $link && $title ) {
										echo '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . ' (#' . (int) $agency_id . ')</a>';
									} elseif ( $title ) {
										echo esc_html( $title ) . ' (#' . (int) $agency_id . ')';
									} else {
										echo '#' . (int) $agency_id;
									}
									?>
								</td>
								<?php if ( $subtab === 'claims' ) : ?>
									<td><?php echo esc_html( $status ); ?></td>
									<td><?php echo esc_html( $owner_label ); ?></td>
									<td><?php echo esc_html( (string) $claims ); ?></td>
								<?php elseif ( $subtab === 'listings' ) : ?>
									<td><?php echo esc_html( (string) $listings ); ?></td>
									<td><?php echo esc_html( $status ); ?></td>
								<?php elseif ( $subtab === 'logins' ) : ?>
									<td><?php echo esc_html( (string) $logins ); ?></td>
									<td><?php echo esc_html( $owner_label ); ?></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

